@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Registrierte Spieler</div>
                <div id="dataTablesButtons" style="display: inline-block;"></div>
                <div class="col search-wrapper px-2">
                    <a id="newlink" style="float: right;" href="{{ route('begegnungen.create') }}"
                       class="btn btn-secondary fuse-ripple-ready">Neuer Spieler
                    </a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                        @include('layouts.datatables')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
    <script type="text/javascript">
        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var DataTable = $.fn.dataTable;
            $.extend( true, DataTable.Buttons.defaults, {
                dom: {
                    container: {
                        className: 'dt-buttons'
                    },
                    button: {
                        className: 'btn btn-secondary fuse-ripple-ready'
                    },
                },
                collection: {
                    tag: "div",
                    className: "dt-button-collection dropdown-menu dropdown-toggle",
                    button: {
                        tag: "a",
                        className: "dt-button dropdown-item",
                        active: "active",
                        disabled: "disabled"
                    }
                }
            } );

            var customConfig = {};

            var dtView = new DTView(jQuery.extend({}, customConfig, JSON.parse(atob('{{ $DT->dtViewJSConfig() }}'))));
            dtView.enableStateSave();
            dtView.createView();

            dtView.dataTable.on('init', function() {
                jQuery("#" + dtView.getElementId() + "Conditions").remove();
                jQuery("<div id='" + dtView.getElementId() + "Conditions'></div>").appendTo($('.page-conditions-header'));
                dtView.setupConditionFilter();
            });


            var buttons = new $.fn.dataTable.Buttons(dtView.dataTable, {
                buttons: [
                    { extend: 'csv', text: 'CSV Export', className: '', exportOptions: { columns: ':visible', modifier: { search: 'applied' } } },
                    { extend: 'colvis', text: 'Tabellen-Spalten', dropup: false, className: '', collectionLayout: 'fixed two-column', columns: [ {{ join(',', $DT->GetShownInColumnVisbilityColumns() ) }} ] },


                    { text: 'Tabellen-Filter', className: 'dt-evo-add', action: function ( e, dt, node, config ) {
                            $('.structFilter').show();

                        }
                    }
                ]
            }).container().appendTo($('#dataTablesButtons'));


        });
    </script>
@stop
