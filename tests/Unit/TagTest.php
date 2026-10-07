<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグが中間テーブルを介して複数のお問い合わせに紐づく(): void
    {
        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(2)->create();

        foreach ($contacts as $contact) {
            $contact->tags()->attach($tag->id);
        }

        $this->assertCount(2, $tag->contacts);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contacts->first()->id,
            'tag_id' => $tag->id,
        ]);
    }
}
