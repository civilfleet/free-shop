<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->catchPhrase,
            'category' => ucfirst($this->faker->word),
            'description' => $this->faker->optional(0.9)->text,
            'stock' => $this->faker->numberBetween(0, 100),
            'customer_limit' => $this->faker->optional(0.2)->numberBetween(0, 10),
        ];
    }
}
