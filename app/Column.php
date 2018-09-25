<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
	use \Conner\Tagging\Taggable;

    public function table()
    {
    	return $this->belongsTo('App\Table');
    }

    public function metadata()
    {
    	return $this->hasOne('App\ColumnMetadata');
    }

    public function tagsToString()
    {
        $str = '';
        foreach ($this->tags as $tag) {
            if (!is_null($tag)) {
                $str .= $tag->slug . ',';
            }
        }
        return rtrim($str, ',');
    }
}
