<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AnimalSpecies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'species',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'species' => AnimalSpecies::class,
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
