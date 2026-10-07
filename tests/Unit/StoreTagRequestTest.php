<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeValidator(array $data)
    {
        return Validator::make($data, (new StoreTagRequest)->rules());
    }

    /** @test */
    public function 有効なタグ名を受け付ける(): void
    {
        $this->assertTrue($this->makeValidator(['name' => '新機能の要望'])->passes());
    }

    /** @test */
    public function タグ名が空だと拒否される(): void
    {
        $this->assertTrue($this->makeValidator(['name' => ''])->fails());
    }

    /** @test */
    public function タグ名が51文字以上だと拒否される(): void
    {
        $this->assertTrue($this->makeValidator(['name' => str_repeat('あ', 50)])->passes());
        $this->assertTrue($this->makeValidator(['name' => str_repeat('あ', 51)])->fails());
    }

    /** @test */
    public function 重複したタグ名は拒否される(): void
    {
        Tag::factory()->create(['name' => '質問']);

        $this->assertTrue($this->makeValidator(['name' => '質問'])->fails());
    }
}
