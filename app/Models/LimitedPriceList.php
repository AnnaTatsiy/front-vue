<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LimitedPriceList extends Model
{
    use HasFactory;
    // трейт для "мягкого" удаления
    use SoftDeletes;

    protected $fillable = [

        'coach_id',
        'amount_workout',
        'price'
    ];

    // сторона "много" отношение "1:М", отношение "принадлежит"
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class)->withTrashed();
    }
}
