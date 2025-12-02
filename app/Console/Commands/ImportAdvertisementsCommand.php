<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAvailable;
use App\Models\ProductCharacteristics;
use App\Models\ProductImages;
use App\Models\ProductLocation;
use App\Models\ProductManager;
use App\Models\ProductPrice;
use App\Models\ProductState;
use App\Models\ProductStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class ImportAdvertisementsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisements:import {--url=https://panel.uniqset.com/storage/exports/advertisements.xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импортирует объявления из XML файла, создает продукты и скачивает изображения';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->option('url');
        
        $this->info("Начинаю импорт объявлений из: {$url}");
        
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
        
        // Синхронизируем категории
        $this->info("Синхронизирую категории...");
        $this->syncCategories($xml);
        
        // Синхронизируем статусы
        $this->info("Синхронизирую статусы...");
        $this->syncStatuses($xml);
        
        // Синхронизируем локации
        $this->info("Синхронизирую локации...");
        $this->syncLocations($xml);
        
        // Импортируем объявления
        $this->info("Импортирую объявления...");
        $advertisements = $xml->advertisements->advertisement ?? [];
        $total = count($advertisements);
        $this->info("Найдено объявлений: {$total}");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $created = 0;
        $updated = 0;
        $errors = 0;
        
        foreach ($advertisements as $advertisement) {
            try {
                DB::beginTransaction();
                
                $result = $this->importAdvertisement($advertisement);
                
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->newLine();
                $this->error("Ошибка при импорте объявления ID " . (string)$advertisement['id'] . ": " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Импорт завершен!");
        $this->info("Создано: {$created}");
        $this->info("Обновлено: {$updated}");
        $this->info("Ошибок: {$errors}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Синхронизация категорий
     */
    private function syncCategories(SimpleXMLElement $xml)
    {
        $categories = $xml->categories->category ?? [];
        
        foreach ($categories as $categoryXml) {
            $categoryId = (int)$categoryXml['id'];
            $name = (string)$categoryXml->name;
            $parentId = (string)$categoryXml->parent_id;
            $parentId = $parentId === '' ? 0 : (int)$parentId;
            
            $category = Category::where('category_panel_id', $categoryId)->first();
            
            if ($category) {
                $category->update([
                    'name' => $name,
                    'parent_id' => $parentId,
                ]);
            } else {
                Category::create([
                    'name' => $name,
                    'parent_id' => $parentId,
                    'category_panel_id' => $categoryId,
                    'active' => true,
                ]);
            }
        }
    }
    
    /**
     * Синхронизация статусов
     */
    private function syncStatuses(SimpleXMLElement $xml)
    {
        // Синхронизация статусов объявлений (ProductStatus)
        if (isset($xml->advertisement_statuses->status)) {
            foreach ($xml->advertisement_statuses->status as $statusXml) {
                $statusId = (int)$statusXml['id'];
                $name = (string)$statusXml->name;
                
                ProductStatus::firstOrCreate(
                    ['name' => $name],
                    [
                        'name' => $name,
                        'status_color' => '#17A2B8', // Значение по умолчанию
                        'show' => true,
                    ]
                );
            }
        }
        
        // Синхронизация состояний (ProductState) - Б.У, Новое и т.д.
        // В XML есть product_state, но нет отдельной секции, поэтому создаем по мере необходимости
        
        // Синхронизация доступности (ProductAvailable) - В наличии, Резерв и т.д.
        // В XML есть product_available, но нет отдельной секции, поэтому создаем по мере необходимости
    }
    
    /**
     * Синхронизация локаций
     */
    private function syncLocations(SimpleXMLElement $xml)
    {
        $advertisements = $xml->advertisements->advertisement ?? [];
        
        foreach ($advertisements as $advertisement) {
            if (isset($advertisement->location->regions->region)) {
                foreach ($advertisement->location->regions->region as $region) {
                    $regionId = (int)$region['id'];
                    $regionName = (string)$region->name;
                    
                    ProductLocation::firstOrCreate(
                        ['panel_location_id' => $regionId],
                        [
                            'name' => $regionName,
                            'active' => true,
                            'panel_location_id' => $regionId,
                        ]
                    );
                }
            }
        }
    }
    
    /**
     * Импорт одного объявления
     */
    private function importAdvertisement(SimpleXMLElement $advertisementXml)
    {
        $panelAdvId = (int)$advertisementXml['id'];
        $title = (string)$advertisementXml->title;
        $productId = (string)$advertisementXml->product_id;
        
        // Получаем категорию
        $categoryId = (int)$advertisementXml->category['id'];
        $categoryName = (string)$advertisementXml->category;
        $category = Category::where('category_panel_id', $categoryId)->first();
        
        if (!$category) {
            // Создаем категорию, если её нет
            $category = Category::create([
                'name' => $categoryName,
                'parent_id' => 0,
                'category_panel_id' => $categoryId,
                'active' => true,
            ]);
            $this->warn("Создана новая категория: {$categoryName} (ID: {$categoryId})");
        }
        
        // Получаем статус
        $statusName = (string)$advertisementXml->status;
        $status = ProductStatus::firstOrCreate(
            ['name' => $statusName],
            [
                'name' => $statusName,
                'status_color' => '#17A2B8', // Значение по умолчанию
                'show' => true,
            ]
        );
        
        // Получаем состояние
        $stateName = (string)$advertisementXml->product_state;
        $state = ProductState::firstOrCreate(['name' => $stateName], ['name' => $stateName]);
        
        // Получаем доступность
        $availableName = (string)$advertisementXml->product_available;
        $available = ProductAvailable::firstOrCreate(['name' => $availableName], ['name' => $availableName]);
        
        // Получаем локацию из региона
        $regionId = null;
        $regionName = null;
        
        if (isset($advertisementXml->location->regions->region)) {
            $region = $advertisementXml->location->regions->region;
            $regionId = (int)$region['id'];
            $regionName = (string)$region->name;
        }
        
        $location = null;
        if ($regionId) {
            $location = ProductLocation::where('panel_location_id', $regionId)->first();
            if (!$location) {
                $location = ProductLocation::create([
                    'name' => $regionName ?: "Регион {$regionId}",
                    'active' => true,
                    'panel_location_id' => $regionId,
                ]);
            }
        }
        
        // Если локация не найдена, создаем дефолтную
        if (!$location) {
            $location = ProductLocation::firstOrCreate(
                ['panel_location_id' => 0],
                [
                    'name' => 'Не указано',
                    'active' => true,
                    'panel_location_id' => 0,
                ]
            );
            $this->warn("Использована дефолтная локация для объявления ID {$panelAdvId}");
        }
        
        // Получаем SKU из XML, если есть, иначе генерируем
        $sku = null;
        if (isset($advertisementXml->sku) && !empty((string)$advertisementXml->sku)) {
            $sku = (string)$advertisementXml->sku;
        } else {
            // Генерируем SKU, если его нет в XML
            $sku = !empty($productId) ? "PROD-{$productId}" : "ADV-{$panelAdvId}";
        }
        
        // Проверяем, существует ли продукт
        $product = Product::where('panel_adv_id', $panelAdvId)->first();
        
        $productData = [
            'name' => $title,
            'sku' => $sku,
            'category_id' => $category->id,
            'product_status_id' => $status->id,
            'product_state_id' => $state->id,
            'product_availability_id' => $available->id,
            'product_location_id' => $location->id,
            'last_system_update' => isset($advertisementXml->dates->updated_at) 
                ? (string)$advertisementXml->dates->updated_at 
                : now(),
            'panel_adv_id' => $panelAdvId,
        ];
        
        if ($product) {
            $product->update($productData);
            $result = 'updated';
        } else {
            $product = Product::create($productData);
            $result = 'created';
        }
        
        // Создаем/обновляем менеджера из product_owner
        if (isset($advertisementXml->manager->product_owner)) {
            $managerXml = $advertisementXml->manager->product_owner;
            $managerName = (string)$managerXml->name;
            $managerPhone = (string)$managerXml->phone;
            $managerEmail = isset($managerXml->email) ? (string)$managerXml->email : null;
            $managerRole = isset($managerXml->role) ? (string)$managerXml->role : null;
            $hasWhatsapp = isset($managerXml->has_whatsapp) ? (bool)(int)$managerXml->has_whatsapp : false;
            $hasTelegram = isset($managerXml->has_telegram) ? (bool)(int)$managerXml->has_telegram : false;
            
            // Формируем строку менеджера с дополнительной информацией
            $managerInfo = $managerName;
            if ($managerEmail) {
                $managerInfo .= " ({$managerEmail})";
            }
            if ($hasWhatsapp || $hasTelegram) {
                $contacts = [];
                if ($hasWhatsapp) $contacts[] = 'WhatsApp';
                if ($hasTelegram) $contacts[] = 'Telegram';
                $managerInfo .= " [" . implode(', ', $contacts) . "]";
            }
            
            ProductManager::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'manager' => $managerInfo,
                    'phone' => $managerPhone,
                ]
            );
        }
        
        // Создаем/обновляем характеристики
        $checkData = null;
        if (isset($advertisementXml->check->status)) {
            $checkData = (string)$advertisementXml->check->status;
            if (isset($advertisementXml->check->comment) && !empty((string)$advertisementXml->check->comment)) {
                $checkData .= ': ' . (string)$advertisementXml->check->comment;
            }
        }
        
        $disassemblingData = null;
        if (isset($advertisementXml->removal->status)) {
            $disassemblingData = (string)$advertisementXml->removal->status;
            if (isset($advertisementXml->removal->comment) && !empty((string)$advertisementXml->removal->comment)) {
                $disassemblingData .= ': ' . (string)$advertisementXml->removal->comment;
            }
        }
        
        $loadingData = null;
        if (isset($advertisementXml->loading->status)) {
            $loadingData = (string)$advertisementXml->loading->status;
            if (isset($advertisementXml->loading->comment) && !empty((string)$advertisementXml->loading->comment)) {
                $loadingData .= ': ' . (string)$advertisementXml->loading->comment;
            }
        }
        
        $characteristicsData = [
            'product_id' => $product->id,
            'main_characteristic' => isset($advertisementXml->main_characteristics) 
                ? (string)$advertisementXml->main_characteristics 
                : null,
            'main_information' => isset($advertisementXml->main_info) 
                ? (string)$advertisementXml->main_info 
                : null,
            'equipment' => isset($advertisementXml->complectation) 
                ? (string)$advertisementXml->complectation 
                : null,
            'technical_specifications' => isset($advertisementXml->technical_characteristics) 
                ? (string)$advertisementXml->technical_characteristics 
                : null,
            'check_data' => $checkData,
            'disassembling_data' => $disassemblingData,
            'loading_data' => $loadingData,
            'additional_information' => isset($advertisementXml->additional_info) 
                ? (string)$advertisementXml->additional_info 
                : null,
        ];
        
        ProductCharacteristics::updateOrCreate(
            ['product_id' => $product->id],
            $characteristicsData
        );
        
        // Создаем/обновляем цену
        if (isset($advertisementXml->price)) {
            $price = (float)$advertisementXml->price->adv_price;
            $priceComment = isset($advertisementXml->price->adv_price_comment) 
                ? (string)$advertisementXml->price->adv_price_comment 
                : null;
            $showPrice = isset($advertisementXml->price->show_price) 
                ? (bool)(int)$advertisementXml->price->show_price 
                : true;
            
            ProductPrice::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'price' => $price,
                    'comment' => $priceComment,
                    'show' => $showPrice,
                ]
            );
        }
        
        // Скачиваем и сохраняем изображения
        if (isset($advertisementXml->media->media_item)) {
            $this->downloadImages($product, $advertisementXml->media->media_item);
        }
        
        return $result;
    }
    
    /**
     * Скачивание и сохранение изображений
     */
    private function downloadImages(Product $product, $mediaItems)
    {
        // Получаем старые изображения для удаления файлов
        $oldImages = ProductImages::where('product_id', $product->id)->get();
        
        // Удаляем файлы из хранилища
        foreach ($oldImages as $oldImage) {
            if (Storage::disk('public')->exists($oldImage->image)) {
                Storage::disk('public')->delete($oldImage->image);
            }
        }
        
        // Удаляем записи из базы данных
        ProductImages::where('product_id', $product->id)->delete();
        
        foreach ($mediaItems as $mediaItem) {
            $type = (string)$mediaItem['type'];
            
            // Скачиваем только изображения
            if ($type !== 'image') {
                continue;
            }
            
            $fileUrl = (string)$mediaItem->file_url;
            $fileName = (string)$mediaItem->file_name;
            $isMainImage = isset($mediaItem->is_main_image) 
                ? (bool)(int)$mediaItem->is_main_image 
                : false;
            
            try {
                // Скачиваем изображение
                $response = Http::withoutVerifying()->timeout(300)->get($fileUrl);
                
                if (!$response->successful()) {
                    $this->warn("Не удалось скачать изображение: {$fileUrl}");
                    continue;
                }
                
                // Сохраняем в storage/app/public/products/{product_id}/
                $storagePath = "products/{$product->id}";
                
                // Используем оригинальное имя файла из XML для отслеживания изменений
                $fullPath = $storagePath . '/' . $fileName;
                
                // Создаем директорию, если её нет
                Storage::disk('public')->makeDirectory($storagePath);
                
                Storage::disk('public')->put($fullPath, $response->body());
                
                // Сохраняем запись в базе данных
                ProductImages::create([
                    'product_id' => $product->id,
                    'image' => $fullPath,
                    'main_image' => $isMainImage,
                ]);
                
            } catch (\Exception $e) {
                $this->warn("Ошибка при скачивании изображения {$fileUrl}: " . $e->getMessage());
                continue;
            }
        }
    }
}

