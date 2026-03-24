<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SwiftType;
use App\Models\Category;

class SwiftTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $swiftTypes = [
            ['type' => 'MT100', 'category_id' => Category::where('name', 'Category 1')->first()->id],
            ['type' => 'MT200', 'category_id' => Category::where('name', 'Category 2')->first()->id],
            ['type' => 'MT300', 'category_id' => Category::where('name', 'Category 3')->first()->id],
            ['type' => 'MT400', 'category_id' => Category::where('name', 'Category 4')->first()->id],
            ['type' => 'MT500', 'category_id' => Category::where('name', 'Category 5')->first()->id],
            ['type' => 'MT600', 'category_id' => Category::where('name', 'Category 6')->first()->id],
            ['type' => 'PACS', 'category_id' => Category::where('name', 'PACS')->first()->id],
            ['type' => 'GAMT', 'category_id' => Category::where('name', 'GAMT')->first()->id],
        ];

        foreach ($swiftTypes as $swiftType) {
            SwiftType::create($swiftType);
        }
    }
}
