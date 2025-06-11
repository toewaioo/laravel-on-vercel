<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'device_id',
        'api_token',
        'is_vip',
        'subscription_key',
        'vip_expires_at'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_key', 'key');
    }
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('expires_at', '>', now())
                    ->orWhere('type', 'lifetime');
            });
    }

    public function isVip()
    {
        return $this->is_vip && (
            $this->vip_expires_at === null ||
            $this->vip_expires_at > now()
        );
    }
}
