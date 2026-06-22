<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    
    protected $guarded = [];

    protected $casts = [
        'params' => 'array',
    ];
}
