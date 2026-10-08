<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeValidator(array $data)
    {
        return Validator::make($data, (new ExportContactRequest)->rules());
    }

    /** @test */
    public function 正しいフィルタ条件を受け付ける(): void
    {
        $category = Category::factory()->create();

        $validator = $this->makeValidator([
            'keyword' => '田中',
            'gender' => 2,
            'category_id' => $category->id,
            'date' => '2026-10-01',
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 不正な性別は拒否される(): void
    {
        $this->assertTrue($this->makeValidator(['gender' => 4])->fails());
    }

    /** @test */
    public function 存在しないカテゴリ_i_dは拒否される(): void
    {
        $this->assertTrue($this->makeValidator(['category_id' => 999])->fails());
    }
}
