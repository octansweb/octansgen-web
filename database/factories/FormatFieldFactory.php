<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Format;
use App\Models\FormatField;

class FormatFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormatField::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'format_id' => Format::factory(),
            'name' => $this->faker->name(),
            'type' => $this->faker->word(),
            'required' => $this->faker->boolean(),
            'default_value' => $this->faker->word(),
        ];
    }
}
