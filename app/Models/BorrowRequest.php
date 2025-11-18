<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id', 'user_id', 'item_id', 'status',
        'borrow_date', 'return_date', 'reason',
        'processed_by', 'processed_at'
    ];

    // Relasi
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); } 
}
