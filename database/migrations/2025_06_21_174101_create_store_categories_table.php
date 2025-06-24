<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

         $categories = [
            ['name' => 'Groceries', 'description' => 'Food and household items'],
            ['name' => 'Electronics', 'description' => 'Phones, laptops, and accessories'],
            ['name' => 'Clothing', 'description' => 'Men, Women and Kids fashion'],
            ['name' => 'Books', 'description' => 'Educational and leisure reading'],
            ['name' => 'Home & Furniture', 'description' => 'Furniture and home essentials'],
        ];

        foreach ($categories as $category) {
            DB::table('store_categories')->insert([
                'id' => Str::uuid(),
                'name' => $category['name'],
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_categories');
    }
};
