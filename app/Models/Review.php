<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'spot_id',
        'content',
        // 'rating', // rating を使用する場合
    ];

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}