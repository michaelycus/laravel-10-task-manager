<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'due_date',
        'priority',
        'completed_at',
    ];

    protected $casts = [
        'due_date'     => 'date:Y-m-d',
        'completed_at' => 'datetime',
        'priority'     => 'string', // Keep as string, validation will handle allowed values
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    // --- Query Scopes ---

    /**
     * Scope a query to only include tasks matching the given name.
     */
    public function scopeFilterByName(Builder $query, ?string $name): Builder
    {
        return $name ? $query->where('name', 'like', '%' . $name . '%') : $query;
    }

    /**
     * Scope a query to only include tasks with a specific priority.
     */
    public function scopeFilterByPriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }

    /**
     * Scope a query to only include tasks based on completion status.
     * 'completed', 'pending'
     */
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if ($status === 'completed') {
            return $query->whereNotNull('completed_at');
        }
        if ($status === 'pending') {
            return $query->whereNull('completed_at');
        }
        return $query;
    }

    /**
     * Scope a query to sort tasks.
     * sortBy: name, due_date, priority, created_at
     * direction: asc, desc
     */
    public function scopeSortBy(Builder $query, ?string $sortBy, string $direction = 'asc'): Builder
    {
        $direction    = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'due_date', 'priority', 'created_at'];

        if ($sortBy && in_array($sortBy, $allowedSorts)) {
            // Special handling for priority sorting if it's not directly sortable
            if ($sortBy === 'priority') {
                // Example: Order by specific priority sequence
                // This requires a more complex CASE WHEN or mapping if priorities aren't alphabetically sortable
                // For 'low', 'medium', 'high', alphabetical sort works if stored as 1, 2, 3 or if the strings sort correctly
                // For now, direct sort. If it needs custom order, adjust this.
                return $query->orderBy('priority', $direction);
            }
            return $query->orderBy($sortBy, $direction);
        }
        return $query->orderBy('created_at', 'desc'); // Default sort
    }
}
