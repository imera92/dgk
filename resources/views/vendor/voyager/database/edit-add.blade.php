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

@section('page_title', (!isset($database))?'Añadir':'Editar' . ' Base de Datos')

@section('page_header')
    <h1 class="page-title"><i class="voyager-file-text"></i>{{ (!isset($database))?'Añadir':'Editar' }} Base de Datos</h1>
@stop

@section('content')
	<div class="page-content container-fluid">
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-bordered">
		            <!-- form start -->
                    <form role="form" class="form-edit-add" action="/admin/db{{ isset($database)?'/'.$database->id:'' }}" method="POST">
                        @if(isset($database))
                            @method('PUT')
                        @endif
                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}
                        <div class="panel-body">
                            <div class="form-group col-md-12">
                                <label>Sistema</label>
                                <select class="form-control select2" name="system_id" required>
                                    <option selected disabled hidden>Seleccione un sistema</option>
                                    @foreach($systems as $system)
                                        <option {{ ( isset($database) && ($database->system->name == $system->name) )?'selected':'' }} value="{{ $system->id }}">{{ $system->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Nombre</label>
                                <input class="form-control" name="name" required type="text" value="{{ (isset($database))?$database->name:'' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Motor de Base de Datos</label>
                                <select class="form-control select2" name="driver" required>
                                    @foreach($drivers as $key => $driver)
                                        <option {{ ( isset($database) && ($database->driver == $key) )?'selected':'' }} value="{{ $key }}">{{ $driver }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Host</label>
                                <input class="form-control" name="host" required type="text" value="{{ (isset($database))?$database->host:'' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Puerto</label>
                                <input class="form-control" name="port" required type="number" value="{{ (isset($database))?$database->port:'' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Usuario para conexión</label>
                                <input class="form-control" name="user" required type="text" value="{{ (isset($database))?$database->user:'' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Contraseña</label>
                                @if(isset($database))
                                    <small>Dejar en blanco si desea conservar la misma contraseña</small>
                                @endif
                                <input class="form-control" name="password" type="password" value="">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Charset</label>
                                <input class="form-control" name="charset" required type="text" value="{{ (isset($database))?$database->charset:'' }}">
                            </div>
                            <div class="form-group col-md-12">
                                <label>Colación</label>
                                <input class="form-control" name="collation" required type="text" value="{{ (isset($database))?$database->collation:'' }}">
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
            @if ($errors->any())
                var validation_error = '<span>Se han detectado los siguientes errores</span><ul>';
                @foreach ($errors->all() as $error)
                    validation_error += '<li>' + '{{ $error }}' + '</li>';
                @endforeach
                validation_error += '</ul>';
                toastr.warning(validation_error);
            @endif
            // There's an event bound to the form's submit handler. If not removed,
            // the form will be submited multiple times
            $('form.form-edit-add').off();
        });
    </script>
@stop