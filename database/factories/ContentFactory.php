<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Content;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
     /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(rand(3, 8)), // A random sentence for a title
            'profileImg' => $this->faker->imageUrl(640, 480, 'people', true), // Fake image URL
            'coverImg' => $this->faker->imageUrl(1280, 720, 'abstract', true), // Fake image URL
            'links' => $this->faker->randomElements([ // Array of fake links
                $this->faker->url(),
                $this->faker->url(),
                $this->faker->url(),
            ], rand(1, 3)), // 1 to 3 random links
            'content' => $this->faker->paragraphs(rand(3, 10), true), // 3 to 10 paragraphs of text
            'tags' => $this->faker->randomElements([ // Array of fake tags
                'Laravel', 'API', 'VueJS', 'ReactJS', 'PHP', 'Backend', 'Frontend', 'Database', 'Auth', 'Security',
            ], rand(1, 4)), // 1 to 4 random tags
            'isvip' => $this->faker->boolean(30), // 30% chance of being VIP content
        ];
    }

    /**
     * Indicate that the content is VIP.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function vip(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'isvip' => true,
            ];
        });
    }

    /**
     * Indicate that the content is standard (non-VIP).
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function standard(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'isvip' => false,
            ];
        });
    }
}
