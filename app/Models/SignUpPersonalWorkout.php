<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignUpPersonalWorkout extends Model
{
    use HasFactory;

    protected $fillable = [

        'date_begin',
        'customer_id',
        'schedule_id'

    ];

    // сторона "много" отношение "1:М", отношение "принадлежит"
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function schedule(): BelongsTo {
        return $this->belongsTo(PersonalSchedule::class)->withTrashed();
    }
}
