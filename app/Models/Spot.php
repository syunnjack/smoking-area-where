<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'lat',
        'lng',
        'area',
        'congestion',
        'congestion_reports',
        'average_congestion',
        'views',
        'likes_count',
    ];

    protected $casts = [
        'congestion_reports' => 'array',
        'average_congestion' => 'float',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}