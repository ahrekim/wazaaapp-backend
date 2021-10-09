<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Happening extends Model
{
    use SoftDeletes;

    protected $appends = [
        'happening_ends_org'
    ];



    /**
     * Has-many invites
     */
    public function invites(){
        return $this->hasMany(Invite::class);
    }
    
    /**
     * Many-to-many relation between tags and happenings
     */
    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

    public function getHappeningStartsAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d H:i');
    }



    /** Mutators and accessors */
    public function getHappeningEndsAttribute($value)
    {
        if(Carbon::parse($this->attributes["happening_starts"])->format('Y-m-d') == Carbon::parse($value)->format('Y-m-d'))
        {
            return Carbon::parse($value)->format('H:i');
        } else {
            return Carbon::parse($value)->format('Y-m-d H:i');
        }
    }
    
    public function getHappeningEndsOrgAttribute()
    {
        return Carbon::parse($this->attributes["happening_ends"])->format('Y-m-d H:i');
    }

    public function setHappeningEndsAttribute($value)
    {
        if(!empty($value)){
            $this->attributes["happening_ends"] = Carbon::createFromFormat("Y-m-d H:i", $value);
        }
    }

    public function setHappeningStartsAttribute($value)
    {
        if(!empty($value)){
            $this->attributes["happening_starts"] = Carbon::createFromFormat("Y-m-d H:i", $value);
        }
    }
}
