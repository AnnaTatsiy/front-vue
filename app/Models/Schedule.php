<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory;

    // трейт для "мягкого" удаления
    use SoftDeletes;

    protected $fillable = [

        'day_id',
        'gym_id',
        'time_begin',
        'time_end',
        'coach_id',
        'workout_type_id'

    ];

    // сторона "один" отношения "1:М" - отношение "имеет"
    public function group_workout():HasMany {
        return $this->hasMany(GroupWorkout::class);
    }

    // сторона "много" отношение "1:М", отношение "принадлежит"
    public function day(): BelongsTo {
        return $this->belongsTo(Day::class);
    }

    public function gym(): BelongsTo {
        return $this->belongsTo(Gym::class);
    }

    public function coach(): BelongsTo {
        return $this->belongsTo(Coach::class)->withTrashed();
    }

    public function workout_type(): BelongsTo {
        return $this->belongsTo(WorkoutType::class);
    }
}
