<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Hotel extends Authenticatable implements JWTSubject
{
    use HasApiTokens, Notifiable;

    protected $table = 'hotels';

    protected $fillable = [
        'hotel_name', 'hotel_address',  'state', 'num_roomavail',
        'room_type', 'room_price', 'description', 'user_id'
    ];

    // Define the relationship with the User model (belongs to a User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function hotelticket()
    {
        return $this->hasMany(Hotel::class);
    }
}


