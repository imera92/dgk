<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    public function databases()
    {
    	return $this->hasMany('App\Database');
    }
}
