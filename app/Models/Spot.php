<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Review; 


class Spot extends Model
{
    protected $fillable = ['name', 'description','congestion', 'lat', 'lng'];

    
    public function reviews()
{
    //return $this->hasMany(Review::class);
    return $this->hasMany(\App\Models\Review::class);


}

}
