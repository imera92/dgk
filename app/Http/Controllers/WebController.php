<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use App\System;
use App\Database;

class WebController extends Controller
{
    public function test()
    {
    	/* if (!Schema::hasTable('test_table')) {
	    	Schema::create('test_table', function(Blueprint $table){
	    		$table->increments('id');
	    	});
	    	return 'table created';
    	} else {
    		return 'table not created';
    	} */
        $new_database = [
            "driver" => "mysql",
            "host" => "127.0.0.1",
            "port" => "3306",
            "database" => "cocina_patricia",
            "username" => "root",
            "password" => "",
            "unix_socket" => "",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
            "prefix" => "",
            "strict" => true,
            "engine" => null
        ];
        Config::set('database.connections.cocina_patricia', $new_database);
        $tables = DB::connection('cocina_patricia')->select('show tables');
        dd($tables[0]->{'Tables_in_cocina_patricia'});
    }
}
