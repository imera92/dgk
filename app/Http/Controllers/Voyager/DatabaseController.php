<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use TCG\Voyager\Facades\Voyager;
use App\System;
use App\Database;
use App\Table;
use App\Column;

class DatabaseController extends Controller
{
    private $drivers;

    public function __construct()
    {
        $this->drivers = [
            'mysql' =>  'MySQL',
            'pgsql' => 'PostgreSQL',
            'sqlsrv' => 'SQL Server'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Voyager::canOrFail('browse_db');

        $all_databases = Database::all();

        return view('vendor.voyager.database.index', [
            'all_databases' => $all_databases,
            'drivers' => $this->drivers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Voyager::canOrFail('add_db');

        $systems = System::all();

        return view('vendor.voyager.database.edit-add', [
            'systems' => $systems,
            'drivers' => $this->drivers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'system_id' => 'required',
            'name' => 'required',
            'driver' => 'required',
            'host' => 'required',
            'port' => 'required',
            'user' => 'required',
            'charset' => 'required',
            'collation' => 'required'
        ]);
        // Create new database
        $database = new Database();
        $database->name = $request->input('name');
        $database->driver = $request->input('driver');
        $database->host = $request->input('host');
        $database->port = $request->input('port');
        $database->user = $request->input('user');
        $database->password = (is_null($request->input('password')))?'':$request->input('password');
        $database->charset = $request->input('charset');
        $database->collation = $request->input('collation');
        // Find requested system and create relationship
        $system = System::find($request->input('system_id'));
        $database->system()->associate($system);
        // Save new database
        $database->save();

        // Query the stored database tables and columns
        // Create config array
        $db_settings = [
            "driver" => $database->driver,
            "host" => $database->host,
            "port" => $database->port,
            "database" => $database->name,
            "username" => $database->user,
            "password" => $database->password,
            "unix_socket" => "",
            "charset" => $database->charset,
            "collation" => $database->collation,
            "prefix" => "",
            "strict" => true,
            "engine" => null
        ];
        // Set config
        Config::set('database.connections.' . $database->name, $db_settings);

        // Query database tables
        $result = DB::connection($database->name)->select('SHOW TABLES');
        // DB facade returns stdObject, so a simpler array should be built
        $table_names = [];
        foreach ($result as $row) {
            array_push($table_names, $row->{'Tables_in_' . $database->name});
        }

        // Store the new table and its columns
        $columns = [];
        foreach ($table_names as $name) {
            // Store new table
            $new_table = new Table();
            $new_table->name = $name;
            $new_table->database()->associate($database);
            $new_table->save();
            // Query the columns
            $result = DB::connection($database->name)->select('DESCRIBE ' . $name);
            foreach ($result as $column) {
                $new_column = new Column();
                $new_column->name = $column->Field;
                $new_column->data_type = $column->Type;
                $new_column->table()->associate($new_table);
                $new_column->save();
            }
        }

        return redirect('admin/db');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        Voyager::canOrFail('read_db');

        $database = Database::find($id);
        $driver = $this->drivers[$database->driver];

        return view('vendor.voyager.database.read', [
            'database' => $database,
            'driver' => $driver
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Voyager::canOrFail('delete_db');

        // If null id is sent, then user wants to delete bulk
        if($id == 0){
            // The ids are sent as a string via the 'ids' hidden field
            $ids_str = $request->input('ids');
            // Retrieve the ids
            $ids = explode(',', $ids_str);
            // Delete the data
            foreach($ids as $id){
                $database = Database::find($id);
                $tables = [];
                $columns = [];

                foreach ($database->tables as $table) {
                    array_push($tables, $table->id);

                    foreach ($table->columns as $column) {
                        array_push($columns, $column->id);
                    }
                }

                Column::destroy($columns);
                Table::destroy($tables);
            }

            Database::destroy($ids);
        } else {
            $database = Database::find($id);
            $tables = [];
            $columns = [];

            foreach ($database->tables as $table) {
                array_push($tables, $table->id);

                foreach ($table->columns as $column) {
                    array_push($columns, $column->id);
                }
            }

            Column::destroy($columns);
            Table::destroy($tables);
            Database::destroy($id);
        }

        return redirect('admin/db');
    }
}