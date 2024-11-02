dataTableOptions.serverSide = true
dataTableOptions.processing = true
dataTableOptions.ajax = $('#deposit_history_route').val();
dataTableOptions.ajax.data = function (d) {
    d.f_date = $('.date_range_input').val();
};
dataTableOptions.columns = [
    {data: 'DT_RowIndex', name: 'id'},
    {data: 'created_at', name: 'created_at'},
    {data: 'amount', name: 'amount'},
    {data: 'method', name: 'method'},
]
let table = $('#lms_table').DataTable(dataTableOptions);
(function ($) {
    "use strict";
    $(document).ready(function () {


        $(document).on('click', '.reset_btn', function (event) {
            event.preventDefault();
            $('.date_range_input').val('');
            resetAfterChange();
        });


        function resetAfterChange() {
            let table = $('#lms_table').DataTable();
            table.ajax.reload();
        }


    });

})(jQuery);
