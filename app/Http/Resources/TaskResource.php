<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'description'  => $this->description,
            'due_date'     => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'priority'     => $this->priority,
            'is_completed' => ! is_null($this->completed_at),
            'completed_at' => $this->completed_at ? $this->completed_at->toIso8601String() : null,
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
            'categories'   => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
