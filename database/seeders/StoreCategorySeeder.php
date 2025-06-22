<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StoreCategory;
use Illuminate\Support\Str;

class StoreCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $categories = [
            ['name' => 'Groceries', 'description' => 'Food and household items'],
            ['name' => 'Electronics', 'description' => 'Phones, laptops, and accessories'],
            ['name' => 'Clothing', 'description' => 'Men, Women and Kids fashion'],
            ['name' => 'Books', 'description' => 'Educational and leisure reading'],
            ['name' => 'Home & Furniture', 'description' => 'Furniture and home essentials'],
        ];

        foreach ($categories as $category) {
            StoreCategory::create([
                    'name' => $category['name'],
                    'description' => $category['description'],
            ]);
        }
    }
}
