<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeEditPersonalSchedule extends Model
{
    // трейт для "мягкого" удаления
    use SoftDeletes;

    protected $fillable = [

        'date_edit',
        'coach_id',
    ];

    public function coach(): BelongsTo {
        return $this->belongsTo(Coach::class);
    }
}
