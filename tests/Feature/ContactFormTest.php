<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function validData(Category $category, array $tagIds = []): array
    {
        return [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区千駄ヶ谷1-2-3',
            'building' => '千駄ヶ谷マンション305',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => $tagIds,
        ];
    }

    /** @test */
    public function 確認ページに入力内容が表示される(): void
    {
        $category = Category::factory()->create(['content' => '商品トラブル']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->post('/contacts/confirm', $this->validData($category, [$tag->id]));

        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');
        $response->assertSee('山田');
        $response->assertSee('yamada@example.com');
        $response->assertSee('商品トラブル');
        $response->assertSee('質問');
    }

    /** @test */
    public function 確認ページはバリデーションエラー時にリダイレクトされる(): void
    {
        $response = $this->post('/contacts/confirm', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'first_name' => '姓を入力してください',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    /** @test */
    public function お問い合わせを送信するとレコードとタグが保存されサンクスへ移動する(): void
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->post('/contacts', $this->validData($category, $tags->pluck('id')->all()));

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'email' => 'yamada@example.com',
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('contact_tag', ['tag_id' => $tags->first()->id]);
        $this->assertDatabaseHas('contact_tag', ['tag_id' => $tags->last()->id]);
    }

    /** @test */
    public function 送信はバリデーションエラー時に保存されない(): void
    {
        $category = Category::factory()->create();
        $data = $this->validData($category);
        $data['tel'] = '090-1234-5678';

        $response = $this->post('/contacts', $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('tel');
        $this->assertDatabaseCount('contacts', 0);
    }
}
