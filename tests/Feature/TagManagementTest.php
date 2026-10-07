<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function タグ編集画面が表示される(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($this->user)->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.tags.edit');
        $response->assertSee('質問');
    }

    /** @test */
    public function タグを作成できる(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/tags', ['name' => '新機能の要望']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['name' => '新機能の要望']);
    }

    /** @test */
    public function タグを更新できる(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($this->user)->put("/admin/tags/{$tag->id}", ['name' => 'ご質問']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'ご質問']);
    }

    /** @test */
    public function タグを削除できる(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    /** @test */
    public function タグ更新は自分の現在の名前なら受け付ける(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($this->user)->put("/admin/tags/{$tag->id}", ['name' => '質問']);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/admin');
    }

    /** @test */
    public function タグ更新は他で使われている名前を拒否する(): void
    {
        Tag::factory()->create(['name' => '要望']);
        $tag = Tag::factory()->create(['name' => '質問']);

        $response = $this->actingAs($this->user)->put("/admin/tags/{$tag->id}", ['name' => '要望']);

        $response->assertSessionHasErrors(['name' => 'そのタグ名は既に使用されています']);
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '質問']);
    }

    /** @test */
    public function 未認証ユーザーはタグ操作ができない(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $this->get("/admin/tags/{$tag->id}/edit")->assertRedirect('/login');
        $this->post('/admin/tags', ['name' => '新タグ'])->assertRedirect('/login');
        $this->put("/admin/tags/{$tag->id}", ['name' => '変更'])->assertRedirect('/login');
        $this->delete("/admin/tags/{$tag->id}")->assertRedirect('/login');

        $this->assertDatabaseHas('tags', ['name' => '質問']);
        $this->assertDatabaseMissing('tags', ['name' => '新タグ']);
    }
}
