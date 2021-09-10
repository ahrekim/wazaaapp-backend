<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invite extends Model
{
    use SoftDeletes;

    /**
     * 
     * BelongsTo happening
     */
    public function happening(){
        return $this->belongsTo(Happening::class);
    }
}
