<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせは特定のカテゴリに属する(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $contact->category);
        $this->assertEquals($category->id, $contact->category->id);
    }

    /** @test */
    public function お問い合わせに複数のタグを同期できる(): void
    {
        $contact = Contact::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $contact->tags()->sync($tags->pluck('id')->all());
        $this->assertCount(3, $contact->fresh()->tags);

        $contact->tags()->sync([$tags->first()->id]);
        $this->assertCount(1, $contact->fresh()->tags);
    }
}
