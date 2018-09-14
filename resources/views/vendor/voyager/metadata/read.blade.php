@extends('voyager::master')

@section('page_title', 'Visualzando Metadata')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-file-text"></i>Visualizando Metadata &nbsp;
        @can('edit_metadata', $metadata)
        	<a href="{{ url('admin/metadata/' . $metadata->id . '/edit') }}" class="btn btn-info"><span class="glyphicon glyphicon-pencil"></span>&nbsp;Editar</a>
        @endcan
        @can('delete_metadata', $metadata)
        	<a href="#" class="btn btn-danger delete" data-id="{{ $metadata->id }}" id="delete-{{ $metadata->id }}"><i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span></a>
        @endcan
        <a href="{{ url('admin/metadata') }}" class="btn btn-warning"><span class="glyphicon glyphicon-list"></span>&nbsp;Regresar a la lista</a>
    </h1>
@stop

@section('content')
	<div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
            	<div class="panel panel-bordered" style="padding-bottom:5px;">
            		<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Tabla</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $metadata->database->system->name . ' - ' . $metadata->database->name . ':' . $metadata->database->host . ':' . $metadata->database->port . ' - ' . $metadata->table_name }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Políticas de Retención</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			{!! $metadata->retention_policy !!}
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Políticas de Depuración</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			{!! $metadata->debug_policy !!}
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Relaciones y Dependencias</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			{!! $metadata->dependencies !!}
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Responsable</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $metadata->manager }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Relevancia</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $relevance }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
        			<div class="panel-heading" style="border-bottom:0;">
        		    	<h3 class="panel-title">Acceso</h3>
            		</div>
            		<div class="panel-body" style="padding-top:0;">
            			<p>{{ $access }}</p>
        			</div>
        			<!-- panel-body -->
        			<hr style="margin:0;">
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
                    <h4 class="modal-title"><i class="voyager-trash"></i> ¿Está seguro de querer borrar esta ficha?</h4>
                </div>
                <div class="modal-footer">
                    <form action="/admin/metadata/{{ $metadata->id }}" id="delete_form" method="POST">
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