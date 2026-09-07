<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookLoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'is_overdue' => $this->isOverdue(),
            'book' => [
                'id' => $this->book->id,
                'title' => $this->book->title,
                'author' => $this->book->author,
                'category' => $this->book->category,
                'cover_url' => $this->book->cover_url,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'borrowed_at' => $this->borrowed_at ? $this->borrowed_at->toIso8601String() : null,
            'due_at' => $this->due_at ? $this->due_at->toIso8601String() : null,
            'returned_at' => $this->returned_at ? $this->returned_at->toIso8601String() : null,
        ];
    }
}
