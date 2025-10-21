<?php

namespace App\Service\Dictionary;

use App\Models\ProductLocation;

class SyncProductLocations
{
    public function processing($locations): array
    {
        $verbose = [];
        foreach ($locations as $location) {
            $locationData = ProductLocation::where('panel_location_id', $location['id'])->first();
            if($locationData) {
                if($locationData->name != $location['name']) {
                    $locationData->update(['name' => $location['name']]);
                }
                if($locationData->active != $location['active']) {
                    $locationData->update(['active' => $location['active']]);
                }
                $verbose['message'][] = 'Location ' . $location['name'] . ' updated';
            } else {
                $createArr = [
                    'id' => $location['id'],
                    'name' => $location['name'],
                    'active' => $location['active'],
                    'panel_location_id' => $location['id']
                ];
                ProductLocation::create($createArr);
                $verbose['message'][] = 'Location ' . $location['name'] . ' created';
            }
        }
        return $verbose;
    }
}
