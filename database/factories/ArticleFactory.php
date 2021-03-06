<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // fakerを使ってデータを生成
            'title' => $this->faker->word(),
            'body' => $this->faker->paragraph(),
            'user_id' => \App\Models\User::factory()->create(),
        ];
    }
}
