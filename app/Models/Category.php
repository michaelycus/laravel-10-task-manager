<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // For slug generation
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }

    /**
     * Boot method to automatically generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // --- Query Scopes ---
    public function scopeFilterByName(Builder $query, ?string $name): Builder
    {
        return $name ? $query->where('name', 'like', '%' . $name . '%') : $query;
    }

    public function scopeSortBy(Builder $query, ?string $sortBy, string $direction = 'asc'): Builder
    {
        $direction    = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'created_at'];

        if ($sortBy && in_array($sortBy, $allowedSorts)) {
            return $query->orderBy($sortBy, $direction);
        }
        return $query->orderBy('name', 'asc'); // Default sort for categories
    }
}
