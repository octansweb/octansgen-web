<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Brand;
use App\Models\BrandFormat;
use App\Models\Format;
use App\Models\Video;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'format_id' => Format::factory(),
            'file_path' => $this->faker->word(),
            'metadata' => '{}',
            'brand_format_id' => BrandFormat::factory(),
        ];
    }
}
