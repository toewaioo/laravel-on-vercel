<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Content extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'title',
        'profileImg',
        'coverImg',
        'links',
        'content',
        'tags',
        'isvip'
    ];

    protected $casts = [
        'tags' => 'array',
        'links' => 'array'
    ];
}
