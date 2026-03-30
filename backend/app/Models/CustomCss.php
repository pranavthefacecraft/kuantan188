<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomCss extends Model
{
    use HasFactory;

    protected $table = 'custom_css';

    protected $fillable = [
        'name',
        'css_content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
