<?php

use App\Models\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status_color');
            $table->boolean('show');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'В продаже',
                'status_color' => '#28a745',
                'show' => true,
            ],
            [
                'name' => 'Резерв',
                'status_color' => '#17A2B8',
                'show' => true,
            ],
            [
                'name' => 'Продано',
                'status_color' => '#17A2B8',
                'show' => false,
            ],
            [
                'name' => 'Архив',
                'status_color' => '#17A2B8',
                'show' => false,
            ],
        ];

        foreach ($dataItems as $dataItem) {
            ProductStatus::create($dataItem);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_statuses');
    }
};
