<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory ,HasApiTokens, HasRoles;

    // трейт для "мягкого" удаления
    use SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'image_id',
        'role'
    ];

    protected $hidden = [
        'image_id',
        'password'
    ];

    public function customer():HasMany {
        return $this->hasMany(Customer::class);
    }

    public function coach():HasMany {
        return $this->hasMany(Coach::class);
    }

    public function image(): BelongsTo {
        return $this->belongsTo(Image::class);
    }

}
