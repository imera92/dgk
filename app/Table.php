<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use \Conner\Tagging\Taggable;

    public function database()
    {
    	return $this->belongsTo('App\Database');
    }

    public function columns()
    {
        return $this->hasMany('App\Column');
    }

    public function metadata()
    {
    	return $this->hasOne('App\TableMetadata');
    }

    public function tagsToString()
    {
        $str = '';
        foreach ($this->tags as $tag) {
            $str .= $tag->slug . ',';
        }
        return rtrim($str, ',');
    }
}
