@extends('voyager::master')

@section('css')
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
                    <form role="form" class="form-edit-add" action="/admin/metadata{{ isset($metadata)?'/'.$metadata->id:'' }}" method="POST" enctype="multipart/form-data">
                        @if(isset($metadata))
                            @method('PUT')
                        @endif
                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}
                        <div class="panel-body">
                            <div class="form-group col-md-12">
                                <label>Base de Datos</label>
                                <select class="form-control select2" name="db_id" required>
                                    <option selected disabled hidden>Seleccione una base de datos</option>
                                    @foreach($systems as $system)
                                        @foreach($system->databases as $database)
                                            <option {{ ( isset($metadata) && ($metadata->database->name == $database->name) )?'selected':'' }} value="{{ $database->id }}">{{ $system->name . ' - ' . $database->host . ' - ' . $database->name}}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Tabla</label>
                                <select class="form-control select2" name="table" required>
                                    <option class="no-delete" selected disabled hidden>Seleccione una tabla</option>
                                    @if(isset($tables))
                                        @foreach($tables as $table)
                                            <option {{ ( isset($metadata) && ($metadata->table_name == $table) )?'selected':'' }} value="{{ $table }}">{{ $table }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Políticas de Retención</label>
                                <textarea class="form-control custom_rich_textbox" name="retention_policy" rows="5">{!! (isset($metadata))?$metadata->retention_policy:'' !!}</textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Políticas de Depuración</label>
                                <textarea class="form-control custom_rich_textbox" name="debug_policy" rows="5">{!! (isset($metadata))?$metadata->debug_policy:'' !!}</textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Relaciones y Dependencias</label>
                                <textarea class="form-control custom_rich_textbox" name="dependencies" rows="5">{!! (isset($metadata))?$metadata->dependencies:'' !!}</textarea>
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
                        </div><!-- panel-body -->
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">Guardar</button>
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
            tinymce.init({
              menubar: false,
              selector:'textarea.custom_rich_textbox',
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
                    },
                    success: function(data){
                        enable_form();
                        $.each(data.ans, function(index, table){
                            var $option = $('<option>', {
                                value: table,
                                text: table
                            });
                            $('select[name~=table]').append($option);
                        });
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
    </script>
@stop