<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $activeLoans = $this->active_loans_count ?? $this->activeLoans()->count();
        $availableCopies = max(0, (int) $this->total_copies - (int) $activeLoans);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'category' => $this->category,
            'language' => $this->language,
            'language_label' => $this->languageLabel(),
            'published_year' => $this->published_year,
            'description' => $this->description,
            'cover_url' => $this->cover_url,
            'total_copies' => (int) $this->total_copies,
            'active_loans' => $activeLoans,
            'available_copies' => $availableCopies,
            'is_available' => $availableCopies > 0,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
