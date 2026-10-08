<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'building',
        'detail',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function scopeSearch(Builder $query, array $filters): Builder
    {
        if (isset($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if (isset($filters['gender']) && (int) $filters['gender'] !== 0) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query;
    }
}
