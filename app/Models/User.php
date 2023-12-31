<?php

namespace App\Models;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Queue;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements JWTSubject, LaratrustUser, MustVerifyEmail
{
    use HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function role()
    {
        return $this->roles->first();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function save(array $options = [])
    {
        if ($this->isDirty('email'))
            $this->email_verified_at = null;

        parent::save($options);

        if ($this->wasChanged('email') || !$this->hasVerifiedEmail())
            event(new Registered($this));
    }

    public function delete()
    {
        if (!parent::delete()) return false;
        $this->syncRoles([]);
        return true;
    }

    public function getAllPermissionsNames(): array
    {
        $userPermissions = $this->allPermissions();
        $permissionNames = [];
        foreach ($userPermissions as $permission) {
            array_push($permissionNames, $permission->name);
        }
        return $permissionNames;
    }
}
