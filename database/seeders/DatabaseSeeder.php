<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. EL USUARIO ADMIN FIJO
        User::create([
            'name'     => 'Admin Dorki',
            'email'    => 'admin@tienda3d.com',
            'password' => bcrypt('password123'),
            'rol'      => 'admin'
        ]);

        $categoriasPrincipales = ['Figuras 3D', 'Camisetas'];

        foreach ($categoriasPrincipales as $nombreCat) {
            
            $categoria = Category::factory()->create([
                'name' => $nombreCat,
                'slug' => Str::slug($nombreCat)
            ]);

            Product::factory(25)->create([
                'category_id' => $categoria->id
            ]);
        }
    }
}
