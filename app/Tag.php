<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{

    use SoftDeletes;
    //

    /**
     * Many-to-many relation between tags and happenings
     */
    public function happenings(){
        return $this->belongsToMany(Happening::class);
    }
}
