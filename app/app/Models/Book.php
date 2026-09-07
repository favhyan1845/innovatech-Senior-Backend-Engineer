<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'category',
        'language',
        'published_year',
        'total_copies',
        'description',
        'cover_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_year' => 'integer',
        'total_copies' => 'integer',
    ];

    /**
     * Every loan record ever created for this book.
     */
    public function loans()
    {
        return $this->hasMany(BookLoan::class);
    }

    /**
     * Loans that are currently active (not returned yet).
     */
    public function activeLoans()
    {
        return $this->loans()->whereNull('returned_at');
    }

    /**
     * How many physical copies are currently available to borrow.
     */
    public function availableCopies(): int
    {
        return max(0, $this->total_copies - $this->activeLoans()->count());
    }

    public function isAvailable(): bool
    {
        return $this->availableCopies() > 0;
    }

    /**
     * Attribute convenience for the Blade views.
     */
    public function getAvailableCopiesAttribute(): int
    {
        return $this->availableCopies();
    }

    /**
     * Attribute convenience for the Blade views.
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->isAvailable();
    }

    /**
     * Free-text search across the main bibliographic fields.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = 'like';

        return $query->where(function (Builder $q) use ($term, $like) {
            $q->where('title', $like, "%{$term}%")
                ->orWhere('author', $like, "%{$term}%")
                ->orWhere('publisher', $like, "%{$term}%")
                ->orWhere('isbn', $like, "%{$term}%")
                ->orWhere('category', $like, "%{$term}%")
                ->orWhere('description', $like, "%{$term}%");
        });
    }

    /**
     * Only books with at least one available copy.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereRaw(
            '(select count(*) from book_loans where book_loans.book_id = books.id and book_loans.returned_at is null) < books.total_copies'
        );
    }
}
