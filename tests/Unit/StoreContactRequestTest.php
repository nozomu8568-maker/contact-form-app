<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeValidator(array $data)
    {
        return Validator::make($data, (new StoreContactRequest)->rules());
    }

    private function validData(): array
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        return [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => null,
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ];
    }

    /** @test */
    public function 全ての必須項目とタグを受け付ける(): void
    {
        $this->assertTrue($this->makeValidator($this->validData())->passes());
    }

    /** @test */
    public function 必須項目が空だと拒否される(): void
    {
        $this->assertTrue($this->makeValidator([])->fails());
    }

    /** @test */
    public function 不正な電話番号は拒否される(): void
    {
        $data = $this->validData();

        $data['tel'] = '090-1234-5678';
        $this->assertTrue($this->makeValidator($data)->fails());

        $data['tel'] = '090123456';
        $this->assertTrue($this->makeValidator($data)->fails());

        $data['tel'] = '090123456789';
        $this->assertTrue($this->makeValidator($data)->fails());
    }

    /** @test */
    public function お問い合わせ内容が121文字以上だと拒否される(): void
    {
        $data = $this->validData();

        $data['detail'] = str_repeat('あ', 120);
        $this->assertTrue($this->makeValidator($data)->passes());

        $data['detail'] = str_repeat('あ', 121);
        $this->assertTrue($this->makeValidator($data)->fails());
    }
}
