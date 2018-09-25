<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ColumnMetadata extends Model
{
    protected $table = 'metadata_columns';

    public function column()
    {
    	return $this->belongsTo('App\Column');
    }
}
