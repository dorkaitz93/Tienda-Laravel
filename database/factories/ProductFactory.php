<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Str;


/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isShirt = $this->faker->boolean();

        $name = $isShirt 
            ? $this->faker->randomElement(['Camiseta', 'Sudadera', 'Top']) . ' ' . ucfirst($this->faker->word())
            : $this->faker->randomElement(['Figura', 'Busto', 'Estatua']) . ' ' . ucfirst($this->faker->word());
        return [
            'category_id' => Category::factory(), // Si no le pasamos categoría, creará una nueva
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 10000), 
            'description' => $this->faker->sentence(),
            'price'       => $this->faker->randomFloat(2, 9, 90),
            'stock'       => $this->faker->numberBetween(0, 100),
            'size'        => $isShirt ? $this->faker->randomElement(['S', 'M', 'L', 'XL']) : null,
            'material'    => $isShirt ? 'Algodón' : $this->faker->randomElement(['PLA', 'Resina']),
            'dimensions'  => $isShirt ? null : $this->faker->numberBetween(10, 30) . "x" . $this->faker->numberBetween(10, 20) . " cm",
        ];
    }
}
