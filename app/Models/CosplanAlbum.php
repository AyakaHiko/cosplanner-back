<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CosplanAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'cosplan_id',
        'title',
    ];

    public function cosplan(): BelongsTo
    {
        return $this->belongsTo(Cosplan::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CosplanImage::class, 'album_id');
    }
}
