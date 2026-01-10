<?php

namespace Database\Seeders;

use App\Models\CropType;
use Illuminate\Database\Seeder;

class CropTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds common Benguet highland vegetables with harvest days and average yield
     */
    public function run(): void
    {
        $crops = [
            // Leafy Vegetables
            [
                'name' => 'Cabbage',
                'category' => 'Leafy Vegetables',
                'description' => 'Cool weather crop, popular highland vegetable',
                'days_to_harvest' => 90,
                'average_yield_per_hectare' => 25.00,
                'is_active' => true,
            ],
            [
                'name' => 'Chinese Cabbage',
                'category' => 'Leafy Vegetables',
                'description' => 'Also known as Pechay Baguio or Wombok',
                'days_to_harvest' => 60,
                'average_yield_per_hectare' => 20.00,
                'is_active' => true,
            ],
            [
                'name' => 'Lettuce',
                'category' => 'Leafy Vegetables',
                'description' => 'Popular salad green, quick growing',
                'days_to_harvest' => 45,
                'average_yield_per_hectare' => 15.00,
                'is_active' => true,
            ],
            [
                'name' => 'Celery',
                'category' => 'Leafy Vegetables',
                'description' => 'Aromatic vegetable used as flavoring',
                'days_to_harvest' => 100,
                'average_yield_per_hectare' => 20.00,
                'is_active' => true,
            ],
            
            // Root Vegetables
            [
                'name' => 'Carrots',
                'category' => 'Root Vegetables',
                'description' => 'Popular root vegetable rich in beta-carotene',
                'days_to_harvest' => 75,
                'average_yield_per_hectare' => 18.00,
                'is_active' => true,
            ],
            [
                'name' => 'Potatoes',
                'category' => 'Root Vegetables',
                'description' => 'Major tuber crop in highlands',
                'days_to_harvest' => 100,
                'average_yield_per_hectare' => 15.00,
                'is_active' => true,
            ],
            [
                'name' => 'Radish',
                'category' => 'Root Vegetables',
                'description' => 'Fast-growing root vegetable',
                'days_to_harvest' => 30,
                'average_yield_per_hectare' => 12.00,
                'is_active' => true,
            ],
            
            // Cruciferous Vegetables
            [
                'name' => 'Broccoli',
                'category' => 'Cruciferous',
                'description' => 'High-value cool weather crop',
                'days_to_harvest' => 80,
                'average_yield_per_hectare' => 12.00,
                'is_active' => true,
            ],
            [
                'name' => 'Cauliflower',
                'category' => 'Cruciferous',
                'description' => 'White headed vegetable, needs cool climate',
                'days_to_harvest' => 85,
                'average_yield_per_hectare' => 12.00,
                'is_active' => true,
            ],
            
            // Legumes
            [
                'name' => 'Snap Beans',
                'category' => 'Legumes',
                'description' => 'Also known as Baguio beans',
                'days_to_harvest' => 55,
                'average_yield_per_hectare' => 10.00,
                'is_active' => true,
            ],
            [
                'name' => 'String Beans',
                'category' => 'Legumes',
                'description' => 'Long pod variety beans',
                'days_to_harvest' => 55,
                'average_yield_per_hectare' => 10.00,
                'is_active' => true,
            ],
            [
                'name' => 'Sweet Peas',
                'category' => 'Legumes',
                'description' => 'High-value pea variety',
                'days_to_harvest' => 70,
                'average_yield_per_hectare' => 8.00,
                'is_active' => true,
            ],
            [
                'name' => 'Garden Peas',
                'category' => 'Legumes',
                'description' => 'Also known as Sitsaro',
                'days_to_harvest' => 65,
                'average_yield_per_hectare' => 6.00,
                'is_active' => true,
            ],
            
            // Fruit Vegetables
            [
                'name' => 'Tomatoes',
                'category' => 'Fruit Vegetables',
                'description' => 'Versatile fruit vegetable',
                'days_to_harvest' => 75,
                'average_yield_per_hectare' => 30.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bell Pepper',
                'category' => 'Fruit Vegetables',
                'description' => 'Sweet pepper variety',
                'days_to_harvest' => 70,
                'average_yield_per_hectare' => 15.00,
                'is_active' => true,
            ],
            [
                'name' => 'Sayote',
                'category' => 'Fruit Vegetables',
                'description' => 'Also known as Chayote, perennial vine',
                'days_to_harvest' => 90,
                'average_yield_per_hectare' => 35.00,
                'is_active' => true,
            ],
            
            // Bulb Vegetables
            [
                'name' => 'Onion',
                'category' => 'Bulb Vegetables',
                'description' => 'Common cooking ingredient',
                'days_to_harvest' => 90,
                'average_yield_per_hectare' => 15.00,
                'is_active' => true,
            ],
            [
                'name' => 'Garlic',
                'category' => 'Bulb Vegetables',
                'description' => 'Aromatic bulb vegetable',
                'days_to_harvest' => 120,
                'average_yield_per_hectare' => 8.00,
                'is_active' => true,
            ],
            
            // Fruits
            [
                'name' => 'Strawberry',
                'category' => 'Fruits',
                'description' => 'Famous Benguet strawberry',
                'days_to_harvest' => 90,
                'average_yield_per_hectare' => 15.00,
                'is_active' => true,
            ],
        ];

        foreach ($crops as $crop) {
            CropType::updateOrCreate(
                ['name' => $crop['name']],
                $crop
            );
        }

        $this->command->info('Crop types seeded with harvest days and average yields!');
    }
}
