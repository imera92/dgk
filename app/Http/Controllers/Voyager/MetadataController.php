<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use TCG\Voyager\Facades\Voyager;
use App\System;
use App\Database;
use App\Table;
use App\Column;
use App\TableMetadata;
use App\ColumnMetadata;
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

        $all_metadata = TableMetadata::all();

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
        /*$validator = $request->validate([
            'db_id' => 'required',
            'table' => ['required', new MetadataUnique($request->input('db_id'))],
            'retention_policy' => 'required',
            'debug_policy' => 'required',
            'dependencies' => 'required',
            'manager' => 'required',
            'relevance' => 'required',
            'access' => 'required'
        ]);*/

        // dd($request->input());

        // Retrieve the columns and table metadata from request
        $metadata_table = $request->input('table');
        $metadata_columns = $request->input('columns');
        // Find the requested table
        $table = Table::find($metadata_table['table_id']);
        // Create and save the table metadata
        $table_meta = new TableMetadata();
        $table_meta->retention_policy = $metadata_table['retention_policy'];
        $table_meta->debug_policy = $metadata_table['debug_policy'];
        $table_meta->dependencies = $metadata_table['dependencies'];
        $table_meta->manager = $metadata_table['manager'];
        $table_meta->relevance = $metadata_table['relevance'];
        $table_meta->access = $metadata_table['access'];
        //$table_meta->table()->associate($table);
        $table->metadata()->save($table_meta);
        $table_meta->save();
        // Save table metadata tags
        //$tags = explode(',', $request->input('tags'));
        if (!empty($metadata_table['tags'])) {
            $table->tag($metadata_table['tags']);
            foreach($table->tags as $tag) {
                $tag->setGroup('TableTags');
            }
        }

        // Find the requested columns
        foreach ($metadata_columns as $column_id => $metadata) {
            $column = Column::find($column_id);
            // Create and save the column metadata
            $column_meta = new ColumnMetadata();
            $column_meta->rules = (is_null($metadata['rules']))?'':$metadata['rules'];
            $column_meta->validity = (is_null($metadata['validity']))?'':$metadata['validity'];
            // $column_meta->column()->associate($column);
            $column->metadata()->save($column_meta);
            $column_meta->save();
            // Save column metadata tags
            if (!empty($metadata['tags'])) {
                $column->tag($metadata['tags']);
                foreach($column->tags as $tag) {
                    $tag->setGroup('ColumnTags');
                }
            }
        }

        // return redirect('admin/metadata');
        return response()->json([
            'error' => 0
        ]);
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

        $metadata = TableMetadata::find($id);
        $table = $metadata->table;
        /*$columns = [];
        foreach ($table->columns as $column) {
            if (!is_null($column->metadata)) {
                array_push($columns, $column);
            }
        }*/
        $columns = $table->columns()->whereHas('metadata')->get();
        $relevance = $this->relevance_options[$metadata->relevance];
        $access = $this->access_options[$metadata->access];

        return view('vendor.voyager.metadata.read', [
            'system_name' => $table->database->system->name,
            'database_name' => $table->database->name,
            'table_name' => $table->name,
            'table_tags' => $table->tags,
            'metadata' => $metadata,
            'columns' => $columns,
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

        $metadata = TableMetadata::find($id);
        $meta_table = $metadata->table;
        $meta_columns = $metadata->table->columns;
        $table_tags = $metadata->table->tagsToString();
        $systems = System::all();
        $tables = $metadata->table->database->tables;

        return view('vendor.voyager.metadata.edit-add', [
            'metadata' => $metadata,
            'meta_table' => $meta_table,
            'meta_columns' => $meta_columns,
            'table_tags' => $table_tags,
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
        // Retrieve the columns and table metadata from request
        $metadata_table = $request->input('table');
        $metadata_columns = $request->input('columns');

        // Create and save the table metadata
        $table_meta = TableMetadata::find($id);
        $table_meta->retention_policy = $metadata_table['retention_policy'];
        $table_meta->debug_policy = $metadata_table['debug_policy'];
        $table_meta->dependencies = $metadata_table['dependencies'];
        $table_meta->manager = $metadata_table['manager'];
        $table_meta->relevance = $metadata_table['relevance'];
        $table_meta->access = $metadata_table['access'];
        $table_meta->save();
        // Save table metadata tags
        $table_meta->table->retag($metadata_table['tags']);
        foreach($table_meta->table->tags as $tag) {
            $tag->setGroup('TableTags');
        }

        // Find the requested columns
        foreach ($metadata_columns as $column_id => $metadata) {
            $column = Column::find($column_id);
            if (is_null($column->metadata)) {
                $column_meta = new ColumnMetadata();
            } else {
                $column_meta = $column->metadata;
            }
            $column_meta->rules = (is_null($metadata['rules']))?'':$metadata['rules'];
            $column_meta->validity = (is_null($metadata['validity']))?'':$metadata['validity'];
            if (is_null($column->metadata)) {
                $column->metadata()->save($column_meta);
            }
            $column_meta->save();
            // Save column metadata tags
            if (!empty($metadata['tags'])) {
                $column->retag($metadata['tags']);
                foreach($column->tags as $tag) {
                    $tag->setGroup('ColumnTags');
                }
            } else {
                $column->untag();
            }
        }

        // return redirect('admin/metadata');
        return response()->json([
            'error' => 0
        ]);
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
                $table_metadata = TableMetadata::find($id);
                $table_metadata->table->untag();
                foreach ($table_metadata->table->columns as $column) {
                    $column->untag();
                    if (!is_null($column->metadata)) {
                        $column->metadata->delete();
                    }
                }
                TableMetadata::destroy($id);
            }
        } else {
            $table_metadata = TableMetadata::find($id);
            $table_metadata->table->untag();
            foreach ($table_metadata->table->columns as $column) {
                $column->untag();
                if (!is_null($column->metadata)) {
                    $column->metadata->delete();
                }
            }
            TableMetadata::destroy($id);
        }

        return redirect('admin/metadata');
    }

    /**
     * Retrieve all the tables corresponding to an 'App\Database' instance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function tables(Request $request)
    {
        if($request->has(['db_id'])){
            // Get database id from request
            $db_id = $request->input('db_id');
            // Get requested database instance
            $database = Database::find($db_id);
            // Get an array of tables
            $tables = $database->tables;
            // Return array of tables
            return response()->json([
                'ans' => $tables
            ]);
        }
    }

    /**
     * Retrieve all the columns corresponding to a table from an 'App\Table' instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function columns(Request $request)
    {
        if($request->has(['table_id'])){
            // Get table id from request
            $table_id = $request->input('table_id');
            // Get requested table instance
            $table = Table::find($table_id);
            // Get an array of tables
            $columns = $table->columns;
            // Return array of tables
            return response()->json([
                'ans' => $columns
            ]);
        }
    }
}
