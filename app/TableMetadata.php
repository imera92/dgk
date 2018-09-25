<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TableMetadata extends Model
{
    protected $table = 'metadata_tables';

    public function table()
    {
    	return $this->belongsTo('App\Table');
    }
}
