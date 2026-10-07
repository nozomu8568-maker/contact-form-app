<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function カテゴリから紐づく複数のお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->contacts);
        $this->assertInstanceOf(Contact::class, $category->contacts->first());
    }
}
