<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CosplanImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'cosplan_id',
        'path',
        'type',
        'album_id',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }
        return Storage::disk('s3')->url($this->path);
    }

    public function cosplan(): BelongsTo
    {
        return $this->belongsTo(Cosplan::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(CosplanAlbum::class, 'album_id');
    }
}
