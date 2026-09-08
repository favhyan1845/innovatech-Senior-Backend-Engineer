<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLoan extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_RETURNED = 'returned';

    public const DEFAULT_LOAN_DAYS = 14;
    public const MAX_LOAN_DAYS = 30;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'user_id',
        'handled_by',
        'borrowed_at',
        'due_at',
        'returned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    /**
     * Appended derived attributes.
     *
     * @var array<int, string>
     */
    protected $appends = ['status'];

    /**
     * The borrowed book.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * The member who borrowed the copy.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The staff member who processed the loan.
     */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isReturned() && $this->due_at !== null && $this->due_at->isPast();
    }

    public function getStatusAttribute(): string
    {
        if ($this->isReturned()) {
            return self::STATUS_RETURNED;
        }

        return $this->isOverdue() ? self::STATUS_OVERDUE : self::STATUS_ACTIVE;
    }

    /**
     * Active (not returned yet) loans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('returned_at');
    }

    /**
     * Returned loans.
     */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->whereNotNull('returned_at');
    }

    /**
     * Active loans whose due date has already passed.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->active()->where('due_at', '<', now());
    }
}
