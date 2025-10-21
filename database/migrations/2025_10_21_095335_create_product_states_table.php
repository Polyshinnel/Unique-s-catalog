<?php

use App\Models\ProductState;
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
        Schema::create('product_states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $dataItems = [
            [
                'name' => 'Новые'
            ],
            [
                'name' => 'Б.У'
            ],
            [
                'name' => 'Восстановленные'
            ],
        ];

        foreach ($dataItems as $item) {
            ProductState::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_states');
    }
};
