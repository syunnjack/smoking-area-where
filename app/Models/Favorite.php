<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = [
        'line_user_id',
        'spot_id',
    ];

    public function lineUser(): BelongsTo
    {
        return $this->belongsTo(LineUser::class);
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }
}
