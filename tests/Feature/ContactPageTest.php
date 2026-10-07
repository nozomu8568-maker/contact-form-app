<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 入力ページが表示されカテゴリとタグが渡される(): void
    {
        // Arrange
        Category::factory()->create(['content' => '商品トラブル']);
        Tag::factory()->create(['name' => '質問']);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee('商品トラブル');
        $response->assertSee('質問');
    }

    /** @test */
    public function サンクスページが表示される(): void
    {
        $response = $this->get('/thanks');

        $response->assertStatus(200);
    }
}
