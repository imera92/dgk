@extends('voyager::master')

@section('page_title', 'Bases de Datos')

@section('page_header')
    <h1 class="page-title"><i class="voyager-data"></i>Bases de Datos</h1>
    @can('add_db', \App\Database::class)
        <a href="{{ url('admin/db/create') }}" class="btn btn-success"><i class="voyager-plus"></i> Registrar una Base de Datos</a>
    @endcan
    @can('delete_db', \App\Database::class)
        <a class="btn btn-danger" id="bulk_delete_btn"><i class="voyager-trash"></i> <span>Borrar por lotes</span></a>
    @endcan
@stop

@section('content')
	<div class="page-content container-fluid">
		<div class="row">
		    <div class="col-md-12">
		        <div class="panel panel-bordered">
		            <div class="panel-body">
                        <div class="table-responsive">
    		                <table id="dataTable" class="table table-hover">
    		                	<thead>
    		                		<tr>
                                        <th><input type="checkbox" class="select_all"></th>
    		                			<th>Sistema</th>
    		                			<th>Nombre</th>
    		                			<th>Host</th>
                                        <th>Motor</th>
    		                			<th class="actions text-right">Acciones</th>
    		                		</tr>
    		                	</thead>
    		                	<tbody>
                                    @foreach($all_databases as $database)
                                        <tr>
                                            <td><input type="checkbox" name="row_id" id="checkbox_{{ $database->id }}" value="{{ $database->id }}"></td>
                                            <td>{{ $database->system->name }}</td>
                                            <td>{{ $database->name }}</td>
                                            <td>{{ $database->host }}</td>
                                            <td>{{ $drivers[$database->driver] }}</td>
                                            <td class="no-sort no-click" id="bread-actions">
                                                @can('delete_db', $database)
                                                    <a class="btn btn-sm btn-danger pull-right delete" href="#" title="Borrar" data-id="{{ $database->id }}" id="delete-{{ $database->id }}">
                                                        <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                                    </a>
                                                @endcan
                                                @can('edit_db', $database)
                                                    <a class="btn btn-sm btn-primary pull-right edit" href="{{ url('admin/db/' . $database->id) . '/edit' }}" title="Editar">
                                                        <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                                                    </a>
                                                @endcan
                                                @can('read_db', $database)
                                                    <a class="btn btn-sm btn-warning pull-right view" href="{{ url('admin/db/' . $database->id) }}" title="Ver">
                                                        <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
    	                	</table>
                        </div>
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
                    <form action="#" id="delete_form" method="POST">
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
    {{-- Bulk delete modal --}}
    <div class="modal modal-danger fade" tabindex="-1" id="bulk_delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <i class="voyager-trash"></i> Se borrarán todas las tablas relacionadas a estas <span id="bulk_delete_count_display"></span> bases de datos ¿Desea continuar?
                    </h4>
                </div>
                <div class="modal-body" id="bulk_delete_modal_body">
                </div>
                <div class="modal-footer">
                    <form action="/admin/db/0" id="bulk_delete_form" method="POST">
                        @method('DELETE')
                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}
                        <input type="hidden" name="ids" id="bulk_delete_input" value="">
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="¡Sí! ¡Bórralos!">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop

@section('javascript')
    <!-- DataTables -->
    <script>
        $(document).ready(function () {
            $('#dataTable').DataTable({
                "order": [],
                "language": { url: '{{ asset('js/datatables_lang/spanish.json') }}' },
                "columnDefs": [{"targets": -1, "searchable":  false, "orderable": false}]
            });
            $('td').on('click', '.delete', function (e) {
                $('#delete_form')[0].action = '/admin/db/' + $(this).data('id');
                $('#delete_modal').modal('show');
            });
            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked'));
            });
        });
        $(window).on('load', function(){
            // Bulk delete selectors
            var $bulkDeleteBtn = $('#bulk_delete_btn');
            var $bulkDeleteModal = $('#bulk_delete_modal');
            var $bulkDeleteCountDisplay = $('#bulk_delete_count_display');
            var $bulkDeleteInput = $('#bulk_delete_input');
            // Reposition modal to prevent z-index issues
            $bulkDeleteModal.appendTo('body');
            // Bulk delete listener
            $bulkDeleteBtn.click(function () {
                var ids = [];
                var $checkedBoxes = $('#dataTable input[type=checkbox]:checked').not('.select_all');
                var count = $checkedBoxes.length;
                if (count) {
                    // Reset input value
                    $bulkDeleteInput.val('');
                    // Deletion info
                    var displayName = count > 1 ? 'estos ' + count + ' registros' : 'este registro';
                    $bulkDeleteCountDisplay.html(displayName);
                    // Gather IDs
                    $.each($checkedBoxes, function () {
                        var value = $(this).val();
                        ids.push(value);
                    })
                    // Set input value
                    $bulkDeleteInput.val(ids);
                    // Show modal
                    $bulkDeleteModal.modal('show');
                } else {
                    // No row selected
                    toastr.warning('No ha seleccionado nada para borrar');
                }
            });
        });
    </script>
@stop
