var TableDataClasses = function() {
    "use strict";
    //function to initiate DataTable
    //DataTable is a highly flexible tool, based upon the foundations of progressive enhancement,
    //which will add advanced interaction controls to any HTML table
    //For more information, please visit https://datatables.net/
    var runDataTable_AddClasses = function() {
        var newRow = false;
        var actualEditingRow = null;

        function restoreRow(oTable, nRow) {
            var aData = oTable.fnGetData(nRow);
            var jqTds = $('>td', nRow);

            for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
                oTable.fnUpdate(aData[i], nRow, i, false);
            }

            oTable.fnDraw();
        }

        function editRow(oTable, nRow) {
            var aData = oTable.fnGetData(nRow);
            var jqTds = $('>td', nRow);
            jqTds[0].innerHTML = '<input type="text" class="form-control" id="new-input" value="' + aData[0] + '">';
            jqTds[1].innerHTML = '<select class="streams-drop-down form-control"><option>Choose an Stream...</option></select>';
            jqTds[2].innerHTML = '<a class="save-row-classes" href="">Save</a>';
            jqTds[3].innerHTML = '<a class="cancel-row-classes" href="">Cancel</a>';
            $.ajax({
                url: 'http://localhost/projects/schools/public/administrator/admin/time/table/get/streams',
                dataType: 'json',
                method: 'POST',
                success: function(data, response) {
                    var DropdownClass = oTable.find(".streams-drop-down");
                    var DropdownId = oTable.find(".streams-drop-down").parent().attr('id');
                    var i;
                    for(i=0; i< data.streams.length; i++){    
                        if(DropdownId == data.streams[i].id){
                            DropdownClass.append('<option value="'+ data.streams[i].id +'" selected>'+ data.streams[i].stream_name +'</option>');
                        }else{                            
                            DropdownClass.append('<option value="'+ data.streams[i].id +'">'+ data.streams[i].stream_name +'</option>');
                        }
                    } 
                }
            });

        }

        function saveRow(oTable, nRow, dataId, streamId, streamName) {
            var jqInputs = $('input', nRow); 
            $('select', nRow).parent().attr('id', streamId);
            var isExistsId = nRow.getAttribute('id');
            if (isExistsId === null) {
                nRow = nRow.setAttribute('id', dataId);
            }
            oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
            oTable.fnUpdate(streamName, nRow, 1, false);
            oTable.fnUpdate('<a class="edit-row-classes" href="">Edit</a>', nRow, 2, false);
            oTable.fnUpdate('<a class="delete-row-classes" href="">Delete</a>', nRow, 3, false);
            oTable.fnDraw();
            newRow = false;
            actualEditingRow = null;
        }

        $('body').on('click', '.add-row-classes', function(e) {
            e.preventDefault();
            if (newRow == false) {
                if (actualEditingRow) {
                    restoreRow(oTable, actualEditingRow);
                }
                newRow = true;
                var aiNew = oTable.fnAddData(['', '', '', '']);
                var nRow = oTable.fnGetNodes(aiNew[0]);
                editRow(oTable, nRow);
                actualEditingRow = nRow;
            }
        });
        $('#table-add-classes').on('click', '.cancel-row-classes', function(e) {
            e.preventDefault();
            if (newRow) {
                newRow = false;
                actualEditingRow = null;
                var nRow = $(this).parents('tr')[0];
                oTable.fnDeleteRow(nRow);

            } else {
                restoreRow(oTable, actualEditingRow);
                actualEditingRow = null;
            }
            oTable.parentsUntil(".panel").find(".errorHandler").addClass("no-display");
        });
        $('#table-add-classes').on('click', '.delete-row-classes', function(e) {
            e.preventDefault();
            if (newRow && actualEditingRow) {
                oTable.fnDeleteRow(actualEditingRow);
                newRow = false;

            }
            var nRow = $(this).parents('tr')[0];
            var class_name = $(this).parents('tr').find('td').first().text();
            var class_id = $(this).parents('tr').attr('id');
            var stream_id = $(this).parents('tr').find(".sorting_1").attr('id');
            var data = {
                class_name: class_name,
                class_id  : class_id,
                stream_id: stream_id
            };
            
            console.log(data);

            bootbox.confirm("Are you sure to delete this row?", function(result) {
                if (result) {
                    $.blockUI({
                        message: '<i class="fa fa-spinner fa-spin"></i> Do some ajax to sync with backend...'
                    });
                    $.ajax({
                        url: 'http://localhost/projects/schools/public/administrator/admin/time/table/delete/classes',
                        dataType: 'json',
                        method: 'POST',
                        data: data,
                        success: function(data, response) {
                            $.unblockUI();
                            oTable.fnDeleteRow(nRow);
                        }
                    });

                }
            });



        });
        $('#table-add-classes').on('click', '.save-row-classes', function(e) {
            e.preventDefault();

            var nRow = $(this).parents('tr')[0];
            var class_name = $(this).parents('tr').find('#new-input').val();
            var class_id = $(this).parents('tr').attr('id');
            var stream_id = $(this).parents('tr').find("select").val();
            var data = {
                class_name: class_name,
                class_id  : class_id,
                stream_id: stream_id
            };
            $.blockUI({
                message: '<i class="fa fa-spinner fa-spin"></i> Do some ajax to sync with backend...'
            });
            $.ajax({
                url: 'http://localhost/projects/schools/public/administrator/admin/time/table/add/classes',
                dataType: 'json',
                method: 'POST',
                data: data,
                success: function(data, response) {
                    $.unblockUI();
                    if (data.status == "success") {
                        saveRow(oTable, nRow, data.data_send.id, data.data_send.streams_id, data.data_send.streams_name);
                    } else if (data.status == "failed") {
                        oTable.parentsUntil(".panel").find(".errorHandler").removeClass("no-display").html('<p class="help-block alert-danger">' + data.error_messages.class_name + '</p>');
                    }
                }
            });
        });
        $('#table-add-classes').on('click', '.edit-row-classes', function(e) {
            e.preventDefault();
            if (actualEditingRow) {
                if (newRow) {
                    oTable.fnDeleteRow(actualEditingRow);
                    newRow = false;
                } else {
                    restoreRow(oTable, actualEditingRow);

                }
            }
            var nRow = $(this).parents('tr')[0];

            editRow(oTable, nRow);
            actualEditingRow = nRow;

        });
        var oTable = $('#table-add-classes').dataTable({
            "aoColumnDefs": [{
                    "aTargets": [0]
                }],
            "oLanguage": {
                "sLengthMenu": "Show _MENU_ Rows",
                "sSearch": "",
                "oPaginate": {
                    "sPrevious": "",
                    "sNext": ""
                }
            },
            "aaSorting": [[1, 'asc']],
            "aLengthMenu": [[5, 10, 15, 20, -1], [5, 10, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 10,
        });
        $('#table-add-classes_wrapper .dataTables_filter input').addClass("form-control input-sm").attr("placeholder", "Search");
        // modify table search input
        $('#table-add-classes_wrapper .dataTables_length select').addClass("m-wrap small");
        // modify table per page dropdown
        $('#table-add-classes_wrapper .dataTables_length select').select2();
        // initialzie select2 dropdown
        $('#table-add-classes_column_toggler input[type="checkbox"]').change(function() {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    };
    return {
        //main function to initiate template pages
        init: function() {
            runDataTable_AddClasses();
        }
    };
}();
