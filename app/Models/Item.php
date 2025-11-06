<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'team_id',
        'category_id',
        'location_id',
        'name',
        'item_code',
        'description',
        'quantity',
        'status',
        'price',
        'purchase_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    protected $with = ['category', 'location'];

    /**
     * Tim (tenant) yang memiliki item ini.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Kategori dari item ini.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault([
            'name' => 'Uncategorized'
        ]);
    }

    /**
     * Lokasi dari item ini.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class)->withDefault([
            'name' => 'No Location'
        ]);
    }

    /**
     * Semua log aktivitas untuk item ini.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class)->latest(); // Selalu urutkan dari yang terbaru
    }
}
