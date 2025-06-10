<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
class Subscription extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type',
        'expires_at',
        'verification_key',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public static function generateVerificationKey()
    {
        return Str::uuid()->toString();
    }

    public static function calculateExpiration($type)
    {
        switch ($type) {
            case '1month':
                return now()->addMonth();
            case '3months':
                return now()->addMonths(3);
            case 'lifetime':
                return null;
            default:
                return now()->addMonth();
        }
    }
}
