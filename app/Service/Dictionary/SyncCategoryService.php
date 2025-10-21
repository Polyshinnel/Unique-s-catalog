<?php

namespace App\Service\Dictionary;

use App\Models\Category;

class SyncCategoryService
{
    public function processing($categories):array
    {
        $verbose = [];
        foreach ($categories as $categoryItem) {
            $category = Category::where('category_panel_id', $category['id'])->first();
            if ($category) {
                if ($category->name != $categoryItem['name']) {
                    $category->update(['name' => $categoryItem['name']]);
                }

                if($category->parent_id != $categoryItem['parent_id']){
                    $category->update(['parent_id' => $categoryItem['parent_id']]);
                }

                if($category->active != $categoryItem['is_active']){
                    $category->update(['active' => $categoryItem['is_active']]);
                }
                $verbose['message'] = "Category {$categoryItem['name']} updated";
            } else {
                $createArr = [
                    'id' => $categoryItem['id'],
                    'name' => $categoryItem['name'],
                    'parent_id' => $categoryItem['parent_id'],
                    'active' => $categoryItem['is_active'],
                    'category_panel_id' => $categoryItem['id']
                ];

                Category::create($createArr);

                $verbose['message'] = "Category {$categoryItem['name']} created";
            }
        }
        return $verbose;
    }
}
