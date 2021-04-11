<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["name", "email", "password"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "password",
        "remember_token",
        "two_factor_recovery_codes",
        "two_factor_secret",
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ["profile_photo_url"];

    /**
     * Lay down relation that a user can have multiple pages which the created.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function created_pages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Page::class, "created_by_id");
    }

    /**
     * Lay down relation that a user can have multiple pages which the user updated.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updated_pages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Page::class, "updated_by_id");
    }

    /**
     * Lay down relation that returns all related Pages, created and updated.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->created_pages()->union(
            $this->updated_pages()->getBaseQuery(),
        );
    }
}
