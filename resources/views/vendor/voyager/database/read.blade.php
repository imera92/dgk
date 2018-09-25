@extends('voyager::master')

@section('page_title', 'Visualizando una Base de datos')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-data"></i>Visualizando una Base de Datos &nbsp;
        @can('edit_db', $database)
        	<a href="{{ url('admin/db/' . $database->id . '/edit') }}" class="btn btn-info"><span class="glyphicon glyphicon-pencil"></span>&nbsp;Editar</a>
        @endcan
        @can('delete_db', $database)
        	<a href="#" class="btn btn-danger delete" data-id="{{ $database->id }}" id="delete-{{ $database->id }}"><i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span></a>
        @endcan
        <a href="{{ url('admin/db') }}" class="btn btn-warning"><span class="glyphicon glyphicon-list"></span>&nbsp;Regresar a la lista</a>
    </h1>
@stop

@section('content')
	<div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
            	<div class="panel panel-bordered" style="padding-bottom:5px;">
            		<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Sistema</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $database->system->name }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Nombre</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $database->name }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Motor de Base de Datos</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $driver }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Host y puerto</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $database->host . ':' . $database->port }}</p>
        			</div>
                    <!-- panel-body -->
                    <hr style="margin:0;">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Usuario para conexión</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p>{{ $database->user }}</p>
                    </div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Charset</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $database->charset }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Colación</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $database->collation }}</p>
        			</div>
                    <!-- panel-body -->
                    <hr style="margin:0;">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Fecha de Creación</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p>{{ $database->created_at }}</p>
                    </div><!-- panel-body -->
                    <hr style="margin:0;">
                    <div class="panel-heading" style="border-bottom:0;">
                        <h3 class="panel-title">Última Actualización</h3>
                    </div>
                    <div class="panel-body" style="padding-top:0;">
                        <p>{{ $database->updated_at }}</p>
                    </div>
        		</div>
            </div>
        </div>
    </div>
    {{-- Single delete modal --}}
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> Se borrarán todas las tablas relacionadas a esta base de datos ¿Desea continuar?</h4>
                </div>
                <div class="modal-footer">
                    <form action="/admin/metadata/{{ $database->id }}" id="delete_form" method="POST">
                        @method('DELETE')
                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="¡Sí! ¡Bórralo!">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('javascript')
	<script>
		var deleteFormAction;
		$('.delete').on('click', function (e) {
		    var form = $('#delete_form')[0];

		    if (!deleteFormAction) { // Save form action initial value
		        deleteFormAction = form.action;
		    }
		    // Check if the the action URL is correct. If not, rewrite it
		    form.action = deleteFormAction.match(/\/[0-9]+$/)
		        ? deleteFormAction.replace(/([0-9]+$)/, $(this).data('id'))	
		        : deleteFormAction + '/' + $(this).data('id');

		    $('#delete_modal').modal('show');
		});
	</script>
@stop