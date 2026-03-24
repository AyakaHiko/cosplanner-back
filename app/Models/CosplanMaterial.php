<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CosplanMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'cosplan_id',
        'type',
        'content',
    ];

    public function cosplan(): BelongsTo
    {
        return $this->belongsTo(Cosplan::class);
    }
}
