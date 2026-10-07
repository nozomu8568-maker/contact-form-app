<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeValidator(array $data)
    {
        return Validator::make($data, (new IndexContactRequest)->rules());
    }

    /** @test */
    public function 有効なフィルタ条件を受け付ける(): void
    {
        $category = Category::factory()->create();

        $validator = $this->makeValidator([
            'keyword' => '田中',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-10-01',
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 条件が空でも受け付ける(): void
    {
        $this->assertTrue($this->makeValidator([])->passes());
    }

    /** @test */
    public function 不正な性別値は拒否される(): void
    {
        $this->assertTrue($this->makeValidator(['gender' => 4])->fails());
        $this->assertTrue($this->makeValidator(['gender' => 'abc'])->fails());
    }

    /** @test */
    public function 存在しないカテゴリ_i_dは拒否される(): void
    {
        $this->assertTrue($this->makeValidator(['category_id' => 999])->fails());
    }
}
