<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Supermercado', 'icon' => '🛒', 'user_id' => null],
            ['name' => 'Renta/Casa', 'icon' => '🏠', 'user_id' => null],
            ['name' => 'Comida/Restaurante', 'icon' => '🍔', 'user_id' => null],
            ['name' => 'Transporte/Gas', 'icon' => '🚗', 'user_id' => null],
            ['name' => 'Perros/Mascotas', 'icon' => '🐕', 'user_id' => null],
            ['name' => 'Entretenimiento', 'icon' => '🎬', 'user_id' => null],
            ['name' => 'Servicios (Luz/Agua)', 'icon' => '💡', 'user_id' => null],
        ];

        foreach ($defaults as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
