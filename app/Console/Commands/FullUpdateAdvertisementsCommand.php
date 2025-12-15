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
use App\Models\ProductTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class FullUpdateAdvertisementsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'advertisements:full-update {--url=https://panel.uniqset.com/storage/exports/advertisements.xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Полное обновление товаров из XML файла с синхронизацией изображений и тегов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->option('url');
        
        $this->info("Начинаю полное обновление товаров из: {$url}");
        
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
        
        // Обновляем товары
        $this->info("Обновляю товары...");
        $advertisements = $xml->advertisements->advertisement ?? [];
        $total = count($advertisements);
        $this->info("Найдено объявлений: {$total}");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $imagesUpdated = 0;
        $tagsUpdated = 0;
        
        foreach ($advertisements as $advertisement) {
            try {
                DB::beginTransaction();
                
                $result = $this->fullUpdateAdvertisement($advertisement);
                
                if ($result['updated']) {
                    $updated++;
                }
                if ($result['skipped']) {
                    $skipped++;
                }
                if ($result['images_updated']) {
                    $imagesUpdated++;
                }
                if ($result['tags_updated']) {
                    $tagsUpdated++;
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
        
        $this->info("Полное обновление завершено!");
        $this->info("Товаров обновлено: {$updated}");
        $this->info("Изображений синхронизировано: {$imagesUpdated}");
        $this->info("Тегов синхронизировано: {$tagsUpdated}");
        $this->info("Пропущено (нет в базе): {$skipped}");
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
                // Обновляем только если изменилось
                if ($category->name !== $name || $category->parent_id !== $parentId) {
                    $category->update([
                        'name' => $name,
                        'parent_id' => $parentId,
                    ]);
                }
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
        if (isset($xml->advertisement_statuses->status)) {
            foreach ($xml->advertisement_statuses->status as $statusXml) {
                $name = (string)$statusXml->name;
                
                ProductStatus::firstOrCreate(
                    ['name' => $name],
                    [
                        'name' => $name,
                        'status_color' => '#17A2B8',
                        'show' => true,
                    ]
                );
            }
        }
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
     * Полное обновление одного объявления
     */
    private function fullUpdateAdvertisement(SimpleXMLElement $advertisementXml): array
    {
        $result = [
            'updated' => false,
            'skipped' => false,
            'images_updated' => false,
            'tags_updated' => false,
        ];
        
        $panelAdvId = (int)$advertisementXml['id'];
        
        // Проверяем, существует ли продукт
        $product = Product::where('panel_adv_id', $panelAdvId)->first();
        
        if (!$product) {
            $result['skipped'] = true;
            return $result;
        }
        
        $hasChanges = false;
        
        // Получаем данные для обновления
        $title = (string)$advertisementXml->title;
        
        // Получаем категорию
        $categoryId = (int)$advertisementXml->category['id'];
        $categoryName = (string)$advertisementXml->category;
        $category = Category::where('category_panel_id', $categoryId)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => $categoryName,
                'parent_id' => 0,
                'category_panel_id' => $categoryId,
                'active' => true,
            ]);
        }
        
        // Получаем статус
        $statusName = (string)$advertisementXml->status;
        $status = ProductStatus::firstOrCreate(
            ['name' => $statusName],
            [
                'name' => $statusName,
                'status_color' => '#17A2B8',
                'show' => true,
            ]
        );
        
        // Получаем состояние
        $stateName = (string)$advertisementXml->product_state;
        $state = ProductState::firstOrCreate(['name' => $stateName], ['name' => $stateName]);
        
        // Получаем доступность
        $availableName = (string)$advertisementXml->product_available;
        $available = ProductAvailable::firstOrCreate(['name' => $availableName], ['name' => $availableName]);
        
        // Получаем локацию
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
        
        if (!$location) {
            $location = ProductLocation::firstOrCreate(
                ['panel_location_id' => 0],
                [
                    'name' => 'Не указано',
                    'active' => true,
                    'panel_location_id' => 0,
                ]
            );
        }
        
        // Получаем SKU
        $sku = null;
        $productId = (string)$advertisementXml->product_id;
        if (isset($advertisementXml->sku) && !empty((string)$advertisementXml->sku)) {
            $sku = (string)$advertisementXml->sku;
        } else {
            $sku = !empty($productId) ? "PROD-{$productId}" : "ADV-{$panelAdvId}";
        }
        
        $lastSystemUpdate = isset($advertisementXml->dates->updated_at) 
            ? (string)$advertisementXml->dates->updated_at 
            : now();
        
        // Проверяем изменения в основных данных продукта
        $productData = [];
        
        if ($product->name !== $title) {
            $productData['name'] = $title;
            $hasChanges = true;
        }
        
        if ($product->sku !== $sku) {
            $productData['sku'] = $sku;
            $hasChanges = true;
        }
        
        if ($product->category_id !== $category->id) {
            $productData['category_id'] = $category->id;
            $hasChanges = true;
        }
        
        if ($product->product_status_id !== $status->id) {
            $productData['product_status_id'] = $status->id;
            $hasChanges = true;
        }
        
        if ($product->product_state_id !== $state->id) {
            $productData['product_state_id'] = $state->id;
            $hasChanges = true;
        }
        
        if ($product->product_availability_id !== $available->id) {
            $productData['product_availability_id'] = $available->id;
            $hasChanges = true;
        }
        
        if ($product->product_location_id !== $location->id) {
            $productData['product_location_id'] = $location->id;
            $hasChanges = true;
        }
        
        // Всегда обновляем время последнего системного обновления
        $productData['last_system_update'] = $lastSystemUpdate;
        
        if (!empty($productData)) {
            $product->update($productData);
        }
        
        // Обновляем менеджера
        if (isset($advertisementXml->manager->product_owner)) {
            $managerUpdated = $this->updateManager($product, $advertisementXml->manager->product_owner);
            if ($managerUpdated) {
                $hasChanges = true;
            }
        }
        
        // Обновляем характеристики
        $characteristicsUpdated = $this->updateCharacteristics($product, $advertisementXml);
        if ($characteristicsUpdated) {
            $hasChanges = true;
        }
        
        // Обновляем цену
        if (isset($advertisementXml->price)) {
            $priceUpdated = $this->updatePrice($product, $advertisementXml->price);
            if ($priceUpdated) {
                $hasChanges = true;
            }
        }
        
        // Синхронизируем изображения
        if (isset($advertisementXml->media->media_item)) {
            $imagesUpdated = $this->syncImages($product, $advertisementXml->media->media_item);
            if ($imagesUpdated) {
                $result['images_updated'] = true;
                $hasChanges = true;
            }
        }
        
        // Синхронизируем теги
        if (isset($advertisementXml->tags->tag)) {
            $tagsUpdated = $this->syncTags($product, $advertisementXml->tags->tag);
            if ($tagsUpdated) {
                $result['tags_updated'] = true;
                $hasChanges = true;
            }
        }
        
        if ($hasChanges) {
            $result['updated'] = true;
        }
        
        return $result;
    }
    
    /**
     * Обновление менеджера
     */
    private function updateManager(Product $product, SimpleXMLElement $managerXml): bool
    {
        $managerName = (string)$managerXml->name;
        $managerPhone = (string)$managerXml->phone;
        $managerEmail = isset($managerXml->email) ? (string)$managerXml->email : null;
        $hasWhatsapp = isset($managerXml->has_whatsapp) ? (bool)(int)$managerXml->has_whatsapp : false;
        $hasTelegram = isset($managerXml->has_telegram) ? (bool)(int)$managerXml->has_telegram : false;
        
        // Формируем строку менеджера
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
        
        $existingManager = ProductManager::where('product_id', $product->id)->first();
        
        // Проверяем, изменились ли данные менеджера
        if (!$existingManager || 
            $existingManager->manager !== $managerInfo || 
            $existingManager->phone !== $managerPhone) {
            
            ProductManager::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'manager' => $managerInfo,
                    'phone' => $managerPhone,
                ]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Обновление характеристик
     */
    private function updateCharacteristics(Product $product, SimpleXMLElement $advertisementXml): bool
    {
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
        
        $newCharacteristics = [
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
        
        $existingCharacteristics = ProductCharacteristics::where('product_id', $product->id)->first();
        
        // Проверяем, изменились ли характеристики
        $hasChanges = false;
        if (!$existingCharacteristics) {
            $hasChanges = true;
        } else {
            foreach ($newCharacteristics as $key => $value) {
                if ($existingCharacteristics->$key !== $value) {
                    $hasChanges = true;
                    break;
                }
            }
        }
        
        if ($hasChanges) {
            ProductCharacteristics::updateOrCreate(
                ['product_id' => $product->id],
                $newCharacteristics
            );
        }
        
        return $hasChanges;
    }
    
    /**
     * Обновление цены
     */
    private function updatePrice(Product $product, SimpleXMLElement $priceXml): bool
    {
        $newPrice = (float)$priceXml->adv_price;
        $newPriceComment = isset($priceXml->adv_price_comment) 
            ? (string)$priceXml->adv_price_comment 
            : null;
        $newShowPrice = isset($priceXml->show_price) 
            ? (bool)(int)$priceXml->show_price 
            : true;
        
        $existingPrice = ProductPrice::where('product_id', $product->id)->first();
        
        // Проверяем, изменилась ли цена
        if (!$existingPrice || 
            $existingPrice->price != $newPrice || 
            $existingPrice->comment != $newPriceComment || 
            $existingPrice->show != $newShowPrice) {
            
            ProductPrice::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'price' => $newPrice,
                    'comment' => $newPriceComment,
                    'show' => $newShowPrice,
                ]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Синхронизация изображений
     */
    private function syncImages(Product $product, $mediaItems): bool
    {
        $hasChanges = false;
        
        // Получаем список изображений из фида
        $feedImages = [];
        foreach ($mediaItems as $mediaItem) {
            $type = (string)$mediaItem['type'];
            
            if ($type === 'image') {
                $fileName = (string)$mediaItem->file_name;
                $fileUrl = (string)$mediaItem->file_url;
                $isMainImage = isset($mediaItem->is_main_image) 
                    ? (bool)(int)$mediaItem->is_main_image 
                    : false;
                
                $feedImages[$fileName] = [
                    'url' => $fileUrl,
                    'is_main' => $isMainImage,
                ];
            }
        }
        
        // Получаем текущие изображения из базы
        $currentImages = ProductImages::where('product_id', $product->id)->get();
        $currentImageNames = [];
        
        foreach ($currentImages as $currentImage) {
            $fileName = basename($currentImage->image);
            $currentImageNames[] = $fileName;
            
            // Если изображения нет в фиде - удаляем
            if (!isset($feedImages[$fileName])) {
                // Удаляем файл
                if (Storage::disk('public')->exists($currentImage->image)) {
                    Storage::disk('public')->delete($currentImage->image);
                }
                
                // Удаляем запись из базы
                $currentImage->delete();
                $hasChanges = true;
            } else {
                // Проверяем, изменился ли флаг main_image
                if ($currentImage->main_image !== $feedImages[$fileName]['is_main']) {
                    $currentImage->update(['main_image' => $feedImages[$fileName]['is_main']]);
                    $hasChanges = true;
                }
            }
        }
        
        // Добавляем новые изображения из фида
        foreach ($feedImages as $fileName => $imageData) {
            if (!in_array($fileName, $currentImageNames)) {
                try {
                    // Скачиваем изображение
                    $response = Http::withoutVerifying()->timeout(300)->get($imageData['url']);
                    
                    if (!$response->successful()) {
                        continue;
                    }
                    
                    // Сохраняем в storage
                    $storagePath = "products/{$product->id}";
                    $fullPath = $storagePath . '/' . $fileName;
                    
                    Storage::disk('public')->makeDirectory($storagePath);
                    Storage::disk('public')->put($fullPath, $response->body());
                    
                    // Сохраняем запись в базе данных
                    ProductImages::create([
                        'product_id' => $product->id,
                        'image' => $fullPath,
                        'main_image' => $imageData['is_main'],
                    ]);
                    
                    $hasChanges = true;
                    
                } catch (\Exception $e) {
                    // Пропускаем изображение при ошибке
                    continue;
                }
            }
        }
        
        return $hasChanges;
    }
    
    /**
     * Синхронизация тегов
     */
    private function syncTags(Product $product, $tagsXml): bool
    {
        $hasChanges = false;
        
        // Получаем список тегов из фида
        $feedTags = [];
        foreach ($tagsXml as $tag) {
            $tagValue = trim((string)$tag);
            if (!empty($tagValue)) {
                $feedTags[] = $tagValue;
            }
        }
        
        // Получаем текущие теги из базы
        $currentTags = ProductTag::where('product_id', $product->id)->get();
        $currentTagValues = $currentTags->pluck('tag')->toArray();
        
        // Находим теги для удаления (есть в базе, но нет в фиде)
        $tagsToDelete = array_diff($currentTagValues, $feedTags);
        if (!empty($tagsToDelete)) {
            ProductTag::where('product_id', $product->id)
                ->whereIn('tag', $tagsToDelete)
                ->delete();
            $hasChanges = true;
        }
        
        // Находим теги для добавления (есть в фиде, но нет в базе)
        $tagsToAdd = array_diff($feedTags, $currentTagValues);
        if (!empty($tagsToAdd)) {
            foreach ($tagsToAdd as $tagValue) {
                ProductTag::create([
                    'product_id' => $product->id,
                    'tag' => $tagValue,
                ]);
            }
            $hasChanges = true;
        }
        
        return $hasChanges;
    }
}

