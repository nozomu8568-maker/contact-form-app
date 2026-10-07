<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function search(array $query)
    {
        return $this->actingAs($this->user)->get('/admin?'.http_build_query($query));
    }

    /** @test */
    public function 一覧は7件ごとにページネーションされる(): void
    {
        Contact::factory()->count(10)->create();

        $first = $this->actingAs($this->user)->get('/admin');
        $second = $this->actingAs($this->user)->get('/admin?page=2');

        $first->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 7 && $contacts->total() === 10;
        });
        $second->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 3;
        });
    }

    /** @test */
    public function キーワードで名前とメールアドレスを検索できる(): void
    {
        Contact::factory()->create(['first_name' => '田中', 'email' => 'a@example.com']);
        Contact::factory()->create(['first_name' => '佐藤', 'email' => 'b@example.com']);

        $byName = $this->search(['keyword' => '田中']);
        $byEmail = $this->search(['keyword' => 'b@example']);

        $byName->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1 && $contacts->first()->first_name === '田中';
        });
        $byEmail->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1 && $contacts->first()->first_name === '佐藤';
        });
    }

    /** @test */
    public function 性別で絞り込める(): void
    {
        Contact::factory()->create(['gender' => 1]);
        Contact::factory()->create(['gender' => 2]);

        $male = $this->search(['gender' => 1]);
        $all = $this->search(['gender' => 0]);

        $male->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1 && $contacts->first()->gender === 1;
        });
        $all->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 2;
        });
    }

    /** @test */
    public function カテゴリで絞り込める(): void
    {
        $target = Category::factory()->create();
        Contact::factory()->create(['category_id' => $target->id]);
        Contact::factory()->create();

        $response = $this->search(['category_id' => $target->id]);

        $response->assertViewHas('contacts', function ($contacts) use ($target) {
            return $contacts->count() === 1 && $contacts->first()->category_id === $target->id;
        });
    }

    /** @test */
    public function 日付で絞り込める(): void
    {
        Contact::factory()->create(['created_at' => '2026-01-01 10:00:00']);
        Contact::factory()->create(['created_at' => '2026-02-01 10:00:00']);

        $response = $this->search(['date' => '2026-01-01']);

        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->count() === 1;
        });
    }

    /** @test */
    public function 詳細ページにカテゴリとタグが表示される(): void
    {
        $category = Category::factory()->create(['content' => '商品の交換について']);
        $tag = Tag::factory()->create(['name' => '要望']);
        $contact = Contact::factory()->create(['category_id' => $category->id]);
        $contact->tags()->attach($tag->id);

        $response = $this->actingAs($this->user)->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        $response->assertViewHas('contact');
        $response->assertSee('商品の交換について');
        $response->assertSee('要望');
    }

    /** @test */
    public function お問い合わせを削除すると一覧にリダイレクトされる(): void
    {
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->actingAs($this->user)->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
        $this->assertDatabaseMissing('contact_tag', ['contact_id' => $contact->id]);
    }
}
