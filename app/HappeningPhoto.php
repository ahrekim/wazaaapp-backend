<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HappeningPhoto extends Model
{
    /**
     * 
     * BelongsTo happenings
     */
    public function happening(){
        return $this->belongsTo(Happening::class);
    }
}
