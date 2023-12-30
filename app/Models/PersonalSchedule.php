<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalSchedule extends Model
{
    use HasFactory;

    // трейт для "мягкого" удаления
    use SoftDeletes;

    protected $fillable = [

        'day_id',
        'time_begin',
        'coach_id'
    ];

    // сторона "много" отношение "1:М", отношение "принадлежит"
    public function day(): BelongsTo {
        return $this->belongsTo(Day::class);
    }

    public function coach(): BelongsTo {
        return $this->belongsTo(Coach::class)->withTrashed();
    }
}
