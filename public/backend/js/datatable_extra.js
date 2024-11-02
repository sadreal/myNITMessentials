let dataTableTitle = $("#logo_title").val();
let dataTableLang = {
    "emptyTable": window.jsLang('data_no_data_available_in_table'),
    "info": window.jsLang('data_showing') + " _START_ " + window.jsLang('data_to') + " _END_ " + window.jsLang('data_of') + " _TOTAL_ " + window.jsLang('data_entries'),
    "infoEmpty": window.jsLang('data_info_empty'),
    "infoFiltered": "(" + window.jsLang('data_info_filtered') + " _MAX_ total " + window.jsLang('data_entries') + ")",
    "infoThousands": ",",
    "lengthMenu": window.jsLang('data_show') + " _MENU_ " + window.jsLang('data_entries'),
    "loadingRecords": window.jsLang('data_loading'),
    "processing": window.jsLang('data_processing'),
    "search": '<i class=\'ti-search\'></i>',
    "searchPlaceholder": window.jsLang('data_quick_search'),
    "zeroRecords": window.jsLang('data_no_match'),
    "thousands": ",",
    "paginate": {
        "first": window.jsLang('data_first'),
        "last": window.jsLang('data_last'),
        "next": "<i class='ti-arrow-right'></i>",
        "previous": "<i class='ti-arrow-left'></i>"
    },


    "buttons": {
        "collection": window.jsLang('data_collection') + " <span class='ui-button-icon-primary ui-icon ui-icon-triangle-1-s'/>",
        "colvis": window.jsLang('data_col_visibility'),
        "colvisRestore": window.jsLang('data_col_restore'),
        "copy": window.jsLang('data_copy'),
        "copyKeys": window.jsLang('data_copy_keys') + "<br><br>" + window.jsLang('data_copy_cancel'),
        "copySuccess": {
            "1": window.jsLang('data_copy_success'),
            "_": window.jsLang('data_copied') + " %d " + window.jsLang('data_rows_to_clipboard')
        },
        "copyTitle": window.jsLang('data_copy_to_clipboard'),
        "csv": window.jsLang('data_csv'),
        "excel": window.jsLang('data_excel'),
        "pageLength": {
            "-1": window.jsLang('data_show_all_rows'),
            "_": window.jsLang('data_show') + " %d " + window.jsLang('data_rows')
        },

        "pdf": window.jsLang('data_pdf'),
        "print": window.jsLang('data_print'),
        "updateState": window.jsLang('data_update'),
        "stateRestore": window.jsLang('data_restore'),
        "savedStates": window.jsLang('data_states') + " %d",
        "renameState": window.jsLang('data_rename'),
        "removeState": window.jsLang('data_remove'),
        "removeAllStates": window.jsLang('data_remove_all'),
        "createState": window.jsLang('data_create'),

    },


    "datetime": {
        "previous": window.jsLang('data_previous'),
        "next": window.jsLang('data_next'),
        "hours": window.jsLang('data_hours'),
        "minutes": window.jsLang('data_minutes'),
        "seconds": window.jsLang('data_seconds'),
        "unknown": "-",
        "amPm": [window.jsLang('data_am'), window.jsLang('data_pm')],
        "weekdays": [window.jsLang('data_Sun'), window.jsLang('data_Mon'), window.jsLang('data_Tue'), window.jsLang('data_Wed'), window.jsLang('data_Thu'), window.jsLang('data_Fri'), window.jsLang('data_Sat')],
        "months": [window.jsLang('data_January'), window.jsLang('data_February'), window.jsLang('data_March'), window.jsLang('data_April'), window.jsLang('data_May'), window.jsLang('data_June'), window.jsLang('data_July'), window.jsLang('data_August'), window.jsLang('data_September'), window.jsLang('data_October'), window.jsLang('data_November'), window.jsLang('data_December')]
    },
}


var dataTableOptions = {
    bLengthChange: true,
    lengthChange: true,
    lengthMenu: [[10, 25, 50, 100, 10000000], [window.jsLang('data_10'), window.jsLang('data_25'), window.jsLang('data_50'), window.jsLang('data_100'), window.jsLang('data_all'), window.jsLang('data_all')]],
    bDestroy: true,
    stateSave: true,
    language: dataTableLang,
    dom: 'Blfrtip',
    buttons: [{
        extend: 'copyHtml5',
        text: '<i class="far fa-copy"></i>',
        title: dataTableTitle,
        titleAttr: window.jsLang('data_copy'),
        exportOptions: {
            columns: ':not(:last-child)',
        }
    }, {
        extend: 'excelHtml5',
        text: '<i class="far fa-file-excel"></i>',
        titleAttr: window.jsLang('data_excel'),
        title: dataTableTitle,
        margin: [10, 10, 10, 0],
        exportOptions: {
            columns: ':not(:last-child)',
        },

    }, {
        extend: 'csvHtml5',
        text: '<i class="far fa-file-alt"></i>',
        titleAttr: window.jsLang('data_csv'),
        exportOptions: {
            columns: ':not(:last-child)',
        }
    }, {
        extend: 'pdfHtml5',
        text: '<i class="far fa-file-pdf"></i>',
        title: dataTableTitle,
        titleAttr: window.jsLang('data_pdf'),
        exportOptions: {
            columns: ':not(:last-child)',
        },
        orientation: 'landscape',
        pageSize: 'A4',
        margin: [0, 0, 0, 0],
        alignment: 'left',
        header: true,
        customize: function (doc) {
            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
        }

    }, {
        extend: 'print',
        text: '<i class="fa fa-print"></i>',
        titleAttr: window.jsLang('data_print'),
        title: dataTableTitle,
        exportOptions: {
            columns: ':not(:last-child)',
        }
    }, {
        extend: 'colvis', text: '<i class="fa fa-columns"></i>', postfixButtons: ['colvisRestore']
    }],

    responsive: true,
    columnDefs: [

        {
            targets: '_all', // Or specific column index for DT_RowIndex
            render: function (data, type, row) {
                if (type === 'display') {
                    return window.translatedNumber(data)
                }
                return data;
            }
        }]

}


function updateColumnExportOption(dataTableOptions, columnList) {
    dataTableOptions.buttons[0].exportOptions.columns = columnList; //copy
    dataTableOptions.buttons[1].exportOptions.columns = columnList; //excel
    dataTableOptions.buttons[2].exportOptions.columns = columnList;  //csv
    dataTableOptions.buttons[3].exportOptions.columns = columnList; //pdf
    dataTableOptions.buttons[4].exportOptions.columns = columnList; //print
    return dataTableOptions;
}
