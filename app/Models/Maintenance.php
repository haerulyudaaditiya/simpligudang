<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'team_id', 'item_id', 'service_provider',
        'start_date', 'completion_date', 'cost',
        'issue_description', 'status', 'user_id'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'start_date' => 'date',
        'completion_date' => 'date',
    ];

    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
