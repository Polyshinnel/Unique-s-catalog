<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class QuickUpdateAdvertisementsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisements:quick-update {--url=https://panel.uniqset.com/storage/exports/advertisements.xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Быстрое обновление статусов и цен товаров из XML файла';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->option('url');
        
        $this->info("Начинаю быстрое обновление товаров из: {$url}");
        
        // Скачиваем XML файл
        $this->info("Скачиваю XML файл...");
        try {
            $response = Http::withoutVerifying()->timeout(300)->get($url);
            
            if (!$response->successful()) {
                $this->error("Не удалось скачать XML файл. Код ответа: " . $response->status());
                return Command::FAILURE;
            }
            
            $xmlContent = $response->body();
        } catch (\Exception $e) {
            $this->error("Ошибка при скачивании XML файла: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Парсим XML
        $this->info("Парсю XML файл...");
        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (\Exception $e) {
            $this->error("Ошибка при парсинге XML: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        // Обновляем товары
        $this->info("Обновляю товары...");
        $advertisements = $xml->advertisements->advertisement ?? [];
        $total = count($advertisements);
        $this->info("Найдено объявлений: {$total}");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $statusUpdated = 0;
        $priceUpdated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($advertisements as $advertisement) {
            try {
                DB::beginTransaction();
                
                $result = $this->updateAdvertisement($advertisement);
                
                if ($result['status_updated']) {
                    $statusUpdated++;
                }
                if ($result['price_updated']) {
                    $priceUpdated++;
                }
                if ($result['skipped']) {
                    $skipped++;
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->newLine();
                $this->error("Ошибка при обновлении объявления ID " . (string)$advertisement['id'] . ": " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Обновление завершено!");
        $this->info("Статусов обновлено: {$statusUpdated}");
        $this->info("Цен обновлено: {$priceUpdated}");
        $this->info("Пропущено (нет в базе): {$skipped}");
        $this->info("Ошибок: {$errors}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Обновление одного объявления
     */
    private function updateAdvertisement(SimpleXMLElement $advertisementXml): array
    {
        $result = [
            'status_updated' => false,
            'price_updated' => false,
            'skipped' => false,
        ];
        
        $panelAdvId = (int)$advertisementXml['id'];
        
        // Проверяем, существует ли продукт
        $product = Product::where('panel_adv_id', $panelAdvId)->first();
        
        if (!$product) {
            $result['skipped'] = true;
            return $result;
        }
        
        // Обновляем статус, если изменился
        $statusName = (string)$advertisementXml->status;
        $status = ProductStatus::firstOrCreate(
            ['name' => $statusName],
            [
                'name' => $statusName,
                'status_color' => '#17A2B8',
                'show' => true,
            ]
        );
        
        if ($product->product_status_id !== $status->id) {
            $product->update(['product_status_id' => $status->id]);
            $result['status_updated'] = true;
        }
        
        // Обновляем цену, если изменилась
        if (isset($advertisementXml->price)) {
            $newPrice = (float)$advertisementXml->price->adv_price;
            $newPriceComment = isset($advertisementXml->price->adv_price_comment) 
                ? (string)$advertisementXml->price->adv_price_comment 
                : null;
            $newShowPrice = isset($advertisementXml->price->show_price) 
                ? (bool)(int)$advertisementXml->price->show_price 
                : true;
            
            $productPrice = ProductPrice::where('product_id', $product->id)->first();
            
            $priceChanged = false;
            
            if (!$productPrice) {
                // Создаем новую цену
                ProductPrice::create([
                    'product_id' => $product->id,
                    'price' => $newPrice,
                    'comment' => $newPriceComment,
                    'show' => $newShowPrice,
                ]);
                $priceChanged = true;
            } else {
                // Проверяем, изменилась ли цена или её параметры
                if ($productPrice->price != $newPrice || 
                    $productPrice->comment != $newPriceComment || 
                    $productPrice->show != $newShowPrice) {
                    
                    $productPrice->update([
                        'price' => $newPrice,
                        'comment' => $newPriceComment,
                        'show' => $newShowPrice,
                    ]);
                    $priceChanged = true;
                }
            }
            
            if ($priceChanged) {
                $result['price_updated'] = true;
            }
        }
        
        // Обновляем время последнего системного обновления
        $product->update([
            'last_system_update' => now(),
        ]);
        
        return $result;
    }
}

