<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosplanImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'cosplan_id',
        'path',
        'type',
    ];

    public function cosplan(): BelongsTo
    {
        return $this->belongsTo(Cosplan::class);
    }
}
