<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Category 1', 'description' => 'MT100'],
            ['name' => 'Category 2', 'description' => 'MT200'],
            ['name' => 'Category 3', 'description' => 'MT300'],
            ['name' => 'Category 4', 'description' => 'MT400'],
            ['name' => 'Category 5', 'description' => 'MT500'],
            ['name' => 'Category 6', 'description' => 'MT600'],
            ['name' => 'PACS', 'description' => 'PACS category'],
            ['name' => 'GAMT', 'description' => 'GAMT category'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
