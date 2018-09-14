<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $table = 'metadata';

    public function database()
    {
    	return $this->belongsTo('App\Database');
    }
}
