<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Builder;

class Database extends Model
{
    public function system()
    {
    	return $this->belongsTo('App\System');
    }

    public function metadata()
    {
        return $this->hasMany('App\Metadata');
    }

    /*protected function setKeysForSaveQuery(Builder $query)
    {
        $query
            ->where('system_id', '=', $this->getAttribute('system_id'))
            ->where('name', '=', $this->getAttribute('name'));
        return $query;
    }*/
}
