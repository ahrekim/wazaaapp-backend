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
     * 
     * Has-many inties
     */
    public function invites(){
        return $this->hasMany(Invite::class);
    }

    public function getHappeningStartsAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i');
    }



    /** Mutators and accessors */
    public function getHappeningEndsAttribute($value)
    {
        if(Carbon::parse($this->attributes["happening_starts"])->format('Y-m-d') == Carbon::parse($value)->format('Y-m-d'))
        {
            return Carbon::parse($value)->format('H:i');
        } else {
            return Carbon::parse($value)->format('d.m.Y H:i');
        }
    }
    
    public function getHappeningEndsOrgAttribute()
    {
        return Carbon::parse($this->attributes["happening_ends"])->format('d.m.Y H:i');
    }

    public function setHappeningEndsAttribute($value)
    {
        if(!empty($value)){
            $this->attributes["happening_ends"] = Carbon::createFromFormat("d.m.Y H:i", $value);
        }
    }

    public function setHappeningStartsAttribute($value)
    {
        if(!empty($value)){
            $this->attributes["happening_starts"] = Carbon::createFromFormat("d.m.Y H:i", $value);
        }
    }
}
