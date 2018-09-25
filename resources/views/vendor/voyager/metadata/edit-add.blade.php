@extends('voyager::master')

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/admin-master.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        var js_base_url = function( urlText ){
            var urlTmp = "{{ url('') }}" + urlText;
            return urlTmp;
        }
    </script>
@stop

@section('page_title', (!isset($metadata))?'Añadir':'Editar' . ' Metadata')

@section('page_header')
    <h1 class="page-title"><i class="voyager-file-text"></i>{{ (!isset($metadata))?'Añadir':'Editar' }} Metadata</h1>
@stop

@section('content')
	<div class="page-content container-fluid">
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-bordered">
		            <!-- form start -->
                    <form role="form" class="form-edit-add" action="{{-- /admin/metadata{{ isset($metadata)?'/'.$metadata->id:'' }} --}}" method="POST" enctype="multipart/form-data">
                        @if(isset($metadata))
                            @method('PUT')
                        @endif
                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}
                        <div class="panel-body">
                            <div class="form-group col-md-12">
                                <label>Base de Datos</label>
                                <select class="form-control select2" name="db_id" required {{ isset($metadata)?'disabled':'' }}>
                                    <option selected disabled hidden>Seleccione una base de datos</option>
                                    @foreach($systems as $system)
                                        @foreach($system->databases as $database)
                                            <option {{ ( isset($meta_table) && ($meta_table->database->id == $database->id) )?'selected':'' }} value="{{ $database->id }}">{{ $system->name . ' - ' . $database->host . ' - ' . $database->name}}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Tabla</label>
                                <select class="form-control select2" name="table_id" required {{ isset($metadata)?'disabled':'' }}>
                                    <option class="no-delete" selected disabled hidden>Seleccione una tabla</option>
                                    @if(isset($tables))
                                        @foreach($tables as $table)
                                            <option {{ ( isset($meta_table) && ($meta_table->id == $table->id) )?'selected':'' }} value="{{ $table->id }}">{{ $table->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Políticas de Retención</label>
                                <textarea id="retention_policy" class="form-control custom_rich_textbox" name="retention_policy" rows="5">{!! (isset($metadata))?$metadata->retention_policy:'' !!}</textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Políticas de Depuración</label>
                                <textarea id="debug_policy" class="form-control custom_rich_textbox" name="debug_policy" rows="5">{!! (isset($metadata))?$metadata->debug_policy:'' !!}</textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Relaciones y Dependencias</label>
                                <textarea id="dependencies" class="form-control custom_rich_textbox" name="dependencies" rows="5">{!! (isset($metadata))?$metadata->dependencies:'' !!}</textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Responsable</label>
                                <input class="form-control" name="manager" required type="text" value="{{ (isset($metadata))?$metadata->manager:'' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Relevancia</label>
                                <select class="form-control select2" name="relevance" required type="text">
                                    @foreach($relevance_options as $key => $value)
                                        <option {{ ( isset($metadata) && ($metadata->relevance == $key) )?'selected':'' }} value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Acceso</label>
                                <select class="form-control select2" name="access" required>
                                    @foreach($access_options as $key => $value)
                                        <option {{ ( isset($metadata) && ($metadata->access == $key) )?'selected':'' }} value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Tags</label>
                                <input class="form-control" name="tags" required type="text" value="{{ (isset($table_tags))?$table_tags:'' }}">
                            </div>
                            @if(isset($meta_columns))
                                <!-- column metadata -->
                                @foreach($meta_columns as $column)
                                    <div class="form-group col-md-12 column-meta" data-id="{{ $column->id }}">
                                        <h4>Columna: {{ $column->name }}</h4>
                                        <label>Validez</label>
                                        <input class="form-control validity" type="text" value="{{ $column->metadata->validity }}">
                                        <label>Reglas</label>
                                        <textarea class="form-control column_rich_textbox" id="rules-{{ $column->id }}" name="rules">{!! $column->metadata->rules !!}</textarea>
                                        <label>Tags</label>
                                        <input class="form-control tags" type="text" value="{{ $column->tagsToString() }}">
                                    </div>
                                @endforeach
                            @endif
                        </div><!-- panel-body -->
                        <div class="panel-footer">
                            <button class="btn btn-primary save">Guardar</button>
                        </div>
                    </form>
            	</div>
        	</div>
    	</div>
	</div>
@stop
@section('javascript')
    <script>
        $(function(){
            // There's an event bound to the form's submit handler. If not removed,
            // the form will be submited multiple times
            $('form.form-edit-add').off();
            tinymce.init({
              menubar: false,
              selector:'textarea.custom_rich_textbox,textarea.column_rich_textbox',
              skin: 'voyager',
              min_height: 200,
              resize: 'vertical',
              plugins: 'lists',
              extended_valid_elements : 'input[id|name|value|type|class|style|required|placeholder|autocomplete|onclick]',
              toolbar: 'bold italic underline | bullist numlist',
            });
            @if ($errors->any())
                var validation_error = '<span>Se han detectado los siguientes errores</span><ul>';
                @foreach ($errors->all() as $error)
                    validation_error += '<li>' + '{{ $error }}' + '</li>';
                @endforeach
                validation_error += '</ul>';
                toastr.warning(validation_error);
            @endif
            // Event handler for database selector
            $('select[name~=db_id]').on('change', function(){
                data = {
                    '_token': $('meta[name~=csrf-token]').attr('content'),
                    'db_id': $(this).val()
                }
                $.ajax({
                    url: js_base_url('/admin/metadata/tables'),
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    beforeSend: function(){
                        disable_form();
                        reset_tables_dropdown();
                        delete_column_groups();
                    },
                    success: function(data){
                        enable_form();
                        $.each(data.ans, function(index, table){
                            var $option = $('<option>', {
                                value: table.id,
                                text: table.name
                            });
                            $('select[name~=table_id]').append($option);
                        });
                    },
                    error: function(){
                        enable_form();
                        alert('Se generó un error durante la operación. Póngase en contacto con el administrador.');
                    }
                });
            });
            // Event handler for table selector
            $('select[name~=table_id]').on('change', function(){
                data = {
                    '_token': $('meta[name~=csrf-token]').attr('content'),
                    'table_id': $(this).val()
                }
                $.ajax({
                    url: js_base_url('/admin/metadata/columns'),
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    beforeSend: function(){
                        disable_form();
                        delete_column_groups();
                    },
                    success: function(data){
                        enable_form();
                        $.each(data.ans, function(index, column){
                            var $title = $('<h4>Columna: ' + column.name + '</h4>')
                            var $form_group = $('<div class="form-group col-md-12 column-meta" data-id="' + column.id + '"></div>');
                            var $label1 = $('<label>Validez</label>');
                            var $validity = $('<input class="form-control validity" type="text" value="">');
                            var $label2 = $('<label>Reglas</label>');
                            var $rules = $('<textarea class="form-control column_rich_textbox" id="rules-' + column.id + '" name="rules"></textarea>');
                            var $label3 = $('<label>Tags</label>');
                            var $tags = $('<input class="form-control tags" type="text" value="">');
                            $form_group.append($title);
                            $form_group.append($label1);
                            $form_group.append($validity);
                            $form_group.append($label2);
                            $form_group.append($rules);
                            $form_group.append($label3);
                            $form_group.append($tags);
                            $('form.form-edit-add>.panel-body').append($form_group);
                            tinymce.init({
                              menubar: false,
                              selector:'textarea.column_rich_textbox',
                              skin: 'voyager',
                              min_height: 200,
                              resize: 'vertical',
                              plugins: 'lists',
                              extended_valid_elements : 'input[id|name|value|type|class|style|required|placeholder|autocomplete|onclick]',
                              toolbar: 'bold italic underline | bullist numlist',
                            });
                        });
                    },
                    error: function(){
                        enable_form();
                        alert('Se generó un error durante la operación. Póngase en contacto con el administrador.');
                    }
                });
            });
            // Event handler for save button
            $('button.save').on('click', function(e){
                e.preventDefault();
                var data = {
                    'table': {
                        'table_id': $('select[name~="table_id"]').val(),
                        'retention_policy': tinymce.get('retention_policy').getContent(),
                        'debug_policy': tinymce.get('debug_policy').getContent(),
                        'dependencies': tinymce.get('dependencies').getContent(),
                        'manager': $('input[name~="manager"]').val(),
                        'relevance': $('select[name~="relevance"]').val(),
                        'access': $('select[name~="access"]').val(),
                        'tags':$('input[name~="tags"]').val()
                    },
                    'columns': {}
                };
                $('.column-meta').each(function(){
                    var column_id = $(this).data('id');
                    data.columns[column_id] = {
                        'rules': tinymce.get('rules-' + column_id).getContent(),
                        'validity': $(this).find('input.validity').val(),
                        'tags': $(this).find('input.tags').val()
                    };
                });
                $.ajax({
                    @if(isset($metadata))
                        url: js_base_url('/admin/metadata/{{ $metadata->id }}'),
                        type: 'PUT',
                    @else
                        url: js_base_url('/admin/metadata'),
                        type: 'POST',
                    @endif
                    data: data,
                    dataType: 'json',
                    beforeSend: function(){
                        disable_form();
                    },
                    success: function(data){
                        if(data.error == 0){
                            window.location.replace(js_base_url('/admin/metadata'));
                        }
                    },
                    error: function(){
                        enable_form();
                        alert('Se generó un error durante la operación. Póngase en contacto con el administrador.');
                    }
                });
            });
        });
        function disable_form(){
            $('form input, form input, form textarea, form select').attr('disabled', true);
            for(var i = 0; i < tinyMCE.editors.length; i++){
                tinyMCE.editors[i].setMode('readonly');
            }
        }
        function enable_form(){
            $('form input, form input, form textarea, form select').attr('disabled', false);
            for(var i = 0; i < tinyMCE.editors.length; i++){
                tinyMCE.editors[i].setMode('exact');
            }
        }
        function reset_tables_dropdown(){
            $('select[name~=table]>option').each(function(){
                if($(this).hasClass('no-delete') != true){
                    $(this).remove();
                }
            });
        }
        function delete_column_groups(){
            $('.column-meta').remove();
        }
    </script>
@stop