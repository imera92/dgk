<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use TCG\Voyager\Facades\Voyager;
use App\System;
use App\Database;
use App\Metadata;
use App\Rules\MetadataUnique;

class MetadataController extends Controller
{
    private $relevance_options;
    private $access_options;

    public function __construct()
    {
        $this->relevance_options = [
            'master' =>  'Maestro',
            'detail' => 'Detalle'
        ];

        $this->access_options = [
            'transaction' =>  'Transacción',
            'batch' => 'Batch',
            'dwh' => 'Data Warehouse'
        ];   
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Voyager::canOrFail('browse_metadata');

        $all_metadata = Metadata::all();

        return view('vendor.voyager.metadata.index', [
            'all_metadata' => $all_metadata
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Voyager::canOrFail('add_metadata');

        $systems = System::all();

        return view('vendor.voyager.metadata.edit-add', [
            'systems' => $systems,
            'relevance_options' => $this->relevance_options,
            'access_options' => $this->access_options
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
            'db_id' => 'required',
            'table' => ['required', new MetadataUnique($request->input('db_id'))],
            'retention_policy' => 'required',
            'debug_policy' => 'required',
            'dependencies' => 'required',
            'manager' => 'required',
            'relevance' => 'required',
            'access' => 'required'
        ]);

        $metadata = new Metadata();
        $metadata->table_name = $request->input('table');
        $metadata->retention_policy = $request->input('retention_policy');
        $metadata->debug_policy = $request->input('debug_policy');
        $metadata->dependencies = $request->input('dependencies');
        $metadata->manager = $request->input('manager');
        $metadata->relevance = $request->input('relevance');
        $metadata->access = $request->input('access');
        $metadata->tags = '';
        $metadata->status = '';
        $database = Database::find($request->input('db_id'));
        $metadata->database()->associate($database);
        $database->metadata()->save($metadata);
        $metadata->save();

        return redirect('admin/metadata');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        Voyager::canOrFail('read_metadata');

        $metadata = Metadata::find($id);
        $relevance = $this->relevance_options[$metadata->relevance];
        $access = $this->access_options[$metadata->access];

        return view('vendor.voyager.metadata.read', [
            'metadata' => $metadata,
            'relevance' => $relevance,
            'access' => $access
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
        Voyager::canOrFail('edit_metadata');

        $metadata = Metadata::find($id);
        $systems = System::all();
        // Build database configuration
        $db_settings = [
            "driver" => $metadata->database->driver,
            "host" => $metadata->database->host,
            "port" => $metadata->database->port,
            "database" => $metadata->database->name,
            "username" => $metadata->database->user,
            "password" => $metadata->database->password,
            "unix_socket" => "",
            "charset" => $metadata->database->charset,
            "collation" => $metadata->database->collation,
            "prefix" => "",
            "strict" => true,
            "engine" => null
        ];
        // Set database configuration
        Config::set('database.connections.' . $metadata->database->name, $db_settings);
        // Query database tables
        $result = DB::connection($metadata->database->name)->select('SHOW TABLES');
        // DB facade returns stdObject, so a simpler array should be built
        $tables = [];
        foreach ($result as $row) {
            array_push($tables, $row->{'Tables_in_' . $metadata->database->name});
        }

        return view('vendor.voyager.metadata.edit-add', [
            'metadata' => $metadata,
            'systems' => $systems,
            'tables' => $tables,
            'relevance_options' => $this->relevance_options,
            'access_options' => $this->access_options
        ]);
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
        $validator = $request->validate([
            'db_id' => 'required',
            'table' => ['required', new MetadataUnique($request->input('db_id'), $id)],
            'retention_policy' => 'required',
            'debug_policy' => 'required',
            'dependencies' => 'required',
            'manager' => 'required',
            'relevance' => 'required',
            'access' => 'required'
        ]);

        $metadata = Metadata::find($id);
        $metadata->table_name = $request->input('table');
        $metadata->retention_policy = $request->input('retention_policy');
        $metadata->debug_policy = $request->input('debug_policy');
        $metadata->dependencies = $request->input('dependencies');
        $metadata->manager = $request->input('manager');
        $metadata->relevance = $request->input('relevance');
        $metadata->access = $request->input('access');
        $metadata->tags = '';
        $metadata->status = '';
        $database = Database::find($request->input('db_id'));
        $metadata->database()->associate($database);
        $database->metadata()->save($metadata);
        $metadata->save();

        return redirect('admin/metadata');
    }

    /**
     * Remove the specified resource/s from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        Voyager::canOrFail('delete_metadata');

        // If null id is sent, then user wants to delete bulk
        if($id == 0){
            // The ids are sent as a string via the 'ids' hidden field
            $ids_str = $request->input('ids');
            // Retrieve de ids
            $ids = explode(',', $ids_str);
            // Delete the data
            foreach($ids as $id){
                Metadata::destroy($id);
            }
        } else {
            Metadata::destroy($id);
        }

        return redirect('admin/metadata');
    }

    /**
     * Retrieve all the tables corresponding to the 'App\Database' instances in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tables(Request $request)
    {
        if($request->has(['db_id'])){
            // Get database id from request
            $db_id = $request->input('db_id');
            // Get requested database configuration
            $database = Database::find($db_id);
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
            // Set database configuration
            Config::set('database.connections.' . $database->name, $db_settings);
            // Query database tables
            $result = DB::connection($database->name)->select('SHOW TABLES');
            // DB facade returns stdObject, so a simpler array should be built
            $tables = [];
            foreach ($result as $row) {
                array_push($tables, $row->{'Tables_in_' . $database->name});
            }
            // Return array of tables
            return response()->json([
                'ans' => $tables
            ]);
        }
    }
}
