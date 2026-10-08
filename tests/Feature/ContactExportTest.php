<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function ログイン済みユーザーは_bo_m付き_cs_vをダウンロードできる(): void
    {
        $category = Category::factory()->create(['content' => '商品トラブル']);
        Contact::factory()->create([
            'first_name' => '田中',
            'last_name' => '太郎',
            'gender' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->user)->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertDownload();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('ID,氏名,性別', $csv);
        $this->assertStringContainsString('田中 太郎', $csv);
        $this->assertStringContainsString('男性', $csv);
        $this->assertStringContainsString('商品トラブル', $csv);
    }

    /** @test */
    public function 検索条件に一致するデータだけが出力される(): void
    {
        Contact::factory()->create(['first_name' => '田中', 'gender' => 1]);
        Contact::factory()->create(['first_name' => '佐藤', 'gender' => 2]);

        $response = $this->actingAs($this->user)->get('/contacts/export?gender=1');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('田中', $csv);
        $this->assertStringNotContainsString('佐藤', $csv);
    }

    /** @test */
    public function 条件なしの場合は新着順で出力される(): void
    {
        Contact::factory()->create(['first_name' => '古い', 'created_at' => '2026-01-01 10:00:00']);
        Contact::factory()->create(['first_name' => '新しい', 'created_at' => '2026-02-01 10:00:00']);

        $response = $this->actingAs($this->user)->get('/contacts/export');

        $csv = $response->streamedContent();
        $this->assertTrue(strpos($csv, '新しい') < strpos($csv, '古い'));
    }

    /** @test */
    public function 未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 不正な性別を指定するとエラーになる(): void
    {
        $response = $this->actingAs($this->user)->get('/contacts/export?gender=9');

        $response->assertSessionHasErrors('gender');
    }
}
