var DTView = function(dataTableViewOptions) {
    "use strict";


    var elementId = dataTableViewOptions.elementId;


    var datatable_conditions = 0;

    this.dataTableElement = jQuery('#'+elementId);
    this.dataTable = null;

    this.cache = {
        columns_order: []
    };

    this.getElementId = function() {
        return this.dataTableOptions.elementId;
    }.bind(this);

    this.stateSaveCallback = function (settings, data) {
        data.start=0;

        $.ajax( {
            url: dataTableViewOptions.extra.stateRestInterfaceSave+"?tid="+elementId,
            data: {payload: JSON.stringify(data) },
            dataType: "json",
            type: "POST",
            async: true,
            success: function () {}
        });
    }.bind(this);

    this.stateLoadCallback = function (settings) {
        var state = JSON.parse(dataTableViewOptions.State);
        try {
            // TODO reload
            var datatable_conditions_tmp = JSON.parse(state.conditions);
        } catch(err) {

        }
        $('.structFilter').show();


        return state;
    }.bind(this);

    this.initComplete = function(settings, json) {
        var api = this.api(), searchBox = $('#table-search-input');

        // Bind an external input as a table wide search box
        if ( searchBox.length > 0 )  {
            searchBox.bindWithDelay('keyup', function (event) {
                api.search(event.target.value).draw();
            }, 200);
        }

    };

    this.footerCallback = function(row, data, start, end, display) {
        var api = this.api();
        var json_data = api.ajax.json();

        if(json_data.total) {
            for (var i = 0; i < json_data.total.length; i++) {
                $(api.column(parseInt(json_data.total[i].pos)).footer()).html(
                    json_data.total[i].value
                );
            }
        }
    };

    this.ajaxConfig = {
        url: dataTableViewOptions.extra.restInterface,
        beforeSend: function(jqXHR, settings) {
            var viscols = [];

            this.dataTableElement.DataTable().columns().every( function () {
                if(this.visible()){
                    viscols.push(this.index());
                }
            });

            settings.data += '&conditions='+JSON.stringify(datatable_conditions);
            settings.data += '&viscols='+viscols.join();


        /*    var drp = $('#reportrange').data('daterangepicker');
            if(drp.length !== 0) {
                if(drp) {
                    settings.data += '&date_start='+drp.startDate.format('YYYY-MM-DD');
                    settings.data += '&date_end='+drp.endDate.format('YYYY-MM-DD');
                }
            }*/

        }.bind(this),
        method: 'POST'
    };

    this.dataTableDefaultOptions = {
        colReorder: {
            realtime: false
        },
        fixedColumns: false,
        responsive: true,
        processing: true,
        serverSide: true,
        deferRender: true,
        fixedHeader: true,
        language: {
            sProcessing: '<svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle></svg><span class="sr-only">Lade...</span> ',
            decimal: ",",
            thousands: ".",
            sEmptyTable: "Keine Daten in der Tabelle vorhanden",
            sInfo: "_START_ bis _END_ von _TOTAL_ Einträgen",
            sInfoEmpty: "0 bis 0 von 0 Einträgen",
            sInfoFiltered: "(gefiltert von _MAX_ Einträgen)",
            sInfoPostFix: "",
            sInfoThousands: ".",
            sLengthMenu: "_MENU_ Einträge anzeigen",
            sLoadingRecords: "Wird geladen...",
            sSearch: "",
            sZeroRecords: "Keine Einträge vorhanden.",
            oPaginate: {
                sFirst: "Erste",
                sPrevious: "Zurück",
                sNext: "Nächste",
                sLast: "Letzte"
            },
            oAria: {
                sSortAscending: ": aktivieren, um Spalte aufsteigend zu sortieren",
                sSortDescending: ": aktivieren, um Spalte absteigend zu sortieren"
            }
        },
        pageLength: 10,
        paging: true,
        ajax: this.ajaxConfig,
        initComplete: this.initComplete,
        footerCallback: this.footerCallback,
        extra: {
            columnsForPdfExport: [], // {{ DT.GetColumnsForPdfExport|join(',') }}
            piwikEnabled: 0
        }
    };

    this.dataTableOptions = jQuery.extend({}, this.dataTableDefaultOptions, dataTableViewOptions);

    this.enableStateSave = function() {
        this.dataTableOptions.stateSave = true;
        this.dataTableOptions.stateSaveCallback = this.stateSaveCallback;
        this.dataTableOptions.stateLoadCallback = this.stateLoadCallback;
    };

    this.pdfColResolver = function(idx, data, node) {
        if(this.cache.columns_order.length < 1) {
            var columns_ordered = [];

            var columns_db = this.dataTableOptions.extra.columnsForPdfExport;
            var columns_current = this.dataTableElement.DataTable().colReorder.order();
            for(var i = 0; i < columns_db.length; i++) {
                columns_ordered.push(columns_current.indexOf(columns_db[i]));
            }
            this.cache.columns_order = columns_ordered;
        }

        if(this.cache.columns_order.indexOf(idx) === -1) {
            return false;
        }
        return true;
    };



    this.createView = function() {
        this.configureExportPdfColResolver();

        this.dataTable = this.dataTableElement.DataTable(this.dataTableOptions);

        this.dataTable.on('page.dt', function() {

        });

        this.dataTable.on( 'column-visibility.dt', function ( e, settings, column, state ) {
            $(this).DataTable().ajax.reload();
            $(window).trigger('resize');
        });


        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            jQuery("#" + elementId).DataTable().ajax.reload();
        }.bind(this));

        //this.setupConditionFilter();

    }.bind(this);

    this.configureExportPdfColResolver = function() {
        $.each(this.dataTableOptions.buttons, function(index, obj) {
            if(obj && obj.extend && obj.extend === "pdfHtml5") {
                obj.exportOptions = {
                    columns: this.pdfColResolver.bind(this)
                };

                this.dataTableOptions.buttons[index] = obj;
            }
        }.bind(this));
    };

    this.addResetOrderButton = function() {
        $("div.resetOrder").html('<a id="#reset" class="btn btn-default buttons-collection buttons-colvis btn-sm"><span>Spaltensortierung zurücksetzen</span></a>');
        $("div.resetOrder").click( function (e) {
            var r = confirm("Sortierung wirklich zurücksetzen?");
            if (r === true) {
                e.preventDefault();
                this.dataTable.colReorder.reset();
            }
        }.bind(this));
    };

    this.setupConditionFilter = function() {
        var dtfilter = jQuery("#" + elementId + "Conditions");

        dtfilter.structFilter(this.dataTableOptions.extra.conditionFilterOptions).on("change.search", function(event){
                datatable_conditions = jQuery("#" + elementId + "Conditions").structFilter("val");
                $('.structFilter').show();
        }).on("change.search", function(event){
            datatable_conditions = jQuery("#" + elementId + "Conditions").structFilter("val");
            $('.structFilter').show();
            this.dataTable.ajax.reload();
        }.bind(this));
    };

};
