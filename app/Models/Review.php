<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Spot;


class Review extends Model
{
    protected $fillable = ['spot_id','rating','comment'];

public function spot()
{
    return $this->belongsTo(Spot::class);
}


}



