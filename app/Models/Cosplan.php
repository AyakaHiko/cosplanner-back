<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Cosplan extends Model
{
    use HasFactory;

    protected $table = 'cosplan';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'deadline',
        'status',
        'main_image_path',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    protected $appends = ['main_image_url'];

    public function getMainImageUrlAttribute(): ?string
    {
        if (!$this->main_image_path) {
            return null;
        }
        if (filter_var($this->main_image_path, FILTER_VALIDATE_URL)) {
            return $this->main_image_path;
        }
        return Storage::disk('s3')->url($this->main_image_path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CosplanImage::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(CosplanAlbum::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CosplanMaterial::class);
    }
}
