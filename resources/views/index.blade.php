<!DOCTYPE html>
<html>
<head>
    <title>Datatables AJAX pagination with Search and Sort in Laravel 10</title>

    <!-- Meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">

    <!-- Datatable CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css"/>

    <!-- jQuery Library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Datatable JS -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
</head>
<body>
    <div class="container">
        <div class="row">
            {{-- <div class="col-md-6">
                <!-- Date Range Filter -->
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" class="form-control" id="start_date">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" class="form-control" id="end_date">
                </div>
            </div> --}}
            <div class="col-md-6">
                <!-- Status Filter -->
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select class="form-control" id="status">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <button id="resetDataTable" style="margin-bottom: 10px; background-color: lightcoral;">Reset Filters</button>

        <button id="loadDataTable" style="margin-bottom: 10px; background-color:lightblue;">Load DataTable</button>
        <input type="text" id="datefilter" name="datefilter" value="" />

        <button id="deleteSelected" class="btn btn-danger">Delete Selected</button>

        <table id='empTable' width='100%' border="1" style='border-collapse: collapse;'>
            <thead>
                <tr>
                    <th>S.no</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Action</th>
                    <th class="no-sort"><input type="checkbox" id="selectAll">Select</th>
                </tr>
            </thead>
        </table>

        <!-- Edit Modal -->
        <div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editEmployeeForm">
                            <input type="hidden" id="editEmployeeId" name="id">
                            <div class="form-group">
                                <label for="editUsername">Username:</label>
                                <input type="text" class="form-control" id="editUsername" name="username">
                            </div>
                            <div class="form-group">
                                <label for="editName">Name:</label>
                                <input type="text" class="form-control" id="editName" name="name">
                            </div>
                            <div class="form-group">
                                <label for="editEmail">Email:</label>
                                <input type="email" class="form-control" id="editEmail" name="email">
                            </div>
                            <div class="form-group">
                                <label for="editDate">Date:</label>
                                <input type="text" class="form-control" id="editDate" name="date">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="updateEmployee">Update</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Modal -->
        <div class="modal fade" id="showEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="showEmployeeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="showEmployeeModalLabel">Employee Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="employeeDetails">
                            <p><strong>ID:</strong> <span id="showEmployeeId"></span></p>
                            <p><strong>Username:</strong> <span id="showUsername"></span></p>
                            <p><strong>Name:</strong> <span id="showName"></span></p>
                            <p><strong>Email:</strong> <span id="showEmail"></span></p>
                            <p><strong>Date:</strong> <span id="showDate"></span></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script -->
        <script type="text/javascript">
            $(document).ready(function () {
                var table;

                // Handle the "Select All" checkbox click event
                $('#selectAll').on('click', function () {
                $('.selected-row').prop('checked', this.checked);
                });

                // Handle individual row checkbox click event
                $('#empTable').on('click', '.selected-row', function () {
                    // Check if all checkboxes are checked, and update the "Select All" checkbox accordingly
                    if ($('.selected-row:checked').length === $('.selected-row').length) {
                        $('#selectAll').prop('checked', true);
                    } else {
                        $('#selectAll').prop('checked', false);
                    }
                });

                // Function to initialize the DataTable with all data
                function initDataTable() {
                    table = $('#empTable').DataTable({
                        processing: true,
                        serverSide: true,
                        // ajax: "{{ route('getEmployees') }}",
                        ajax: {
                            url: "{{ route('getEmployees') }}",
                            data: function (d) {
                                d.status = $('#status').val();
                            },
                        },
                        columns: [
                            { data: 'id' },
                            { data: 'username' },
                            { data: 'name' },
                            { data: 'email' },
                            { data: 'date' },
                            {
                                data: null,
                                render: function (data, type, row) {
                                    return `
                                        <button class="btn btn-info btn-sm edit-record" data-id="${row.id}">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-record" data-id="${row.id}">Delete</button>
                                        <button class="btn btn-primary btn-sm show-record" data-id="${row.id}">Show</button>
                                    `;
                                },
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: null,
                                render: function (data, type, row) {
                                    return `<input type="checkbox" class="selected-row" value="${row.id}">`;
                                },
                                orderable: false,
                                searchable: false
                            }
                        ],
                        columnDefs: [
                            { targets: 'no-sort', orderable: false }
                        ],
                        lengthMenu: [5, 10, 25, 50, 100], // Define the pagination length options
                        pageLength: 10, // Default page length when the table is initialized
                    });
                }

                // Attach click event handler to the "Load DataTable" button
                $('#loadDataTable').on('click', function () {
                    // Initialize the DataTable with all data
                    initDataTable();
                });

                $('#resetDataTable').on('click', function () {
                    // Clear the status filter
                    $('#status').val('');

                    // Clear the date range filter
                    $('input[name="datefilter"]').val('');

                    // Destroy the DataTable and reinitialize it with all data
                    if ($.fn.DataTable.isDataTable('#empTable')) {
                    table.destroy();
                    }
                    initDataTable();
                });

                // Initialize the Date Range Picker
                $('input[name="datefilter"]').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                    }
                });

                // Apply date range filter on user selection
                $('input[name="datefilter"]').on('apply.daterangepicker', function (ev, picker) {
                    var startDate = picker.startDate.format('YYYY-MM-DD');
                    var endDate = picker.endDate.format('YYYY-MM-DD');

                    // Format and display the selected date range in the input field as 'dd-mm-yyyy'
                    var formattedStartDate = picker.startDate.format('DD-MM-YYYY');
                    var formattedEndDate = picker.endDate.format('DD-MM-YYYY');
                    $(this).val(formattedStartDate + ' - ' + formattedEndDate);

                    // Reload DataTable with the filtered data
                    table.destroy();
                    initDataTableWithDateRange(startDate, endDate);
                });

                // Clear date range filter
                $('input[name="datefilter"]').on('cancel.daterangepicker', function (ev, picker) {
                    // Clear the input field
                    $(this).val('');

                    // Reload DataTable with all data
                    table.destroy();
                    initDataTable();
                });

                // Function to initialize the DataTable with date range filter
                function initDataTableWithDateRange(startDate, endDate) {
                    table = $('#empTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('getFilteredEmployees') }}",
                            data: function (d) {
                                d.startDate = startDate;
                                d.endDate = endDate;
                            },
                        },
                        columns: [
                            { data: 'id' },
                            { data: 'username' },
                            { data: 'name' },
                            { data: 'email' },
                            { data: 'date' },
                            {
                                data: null,
                                render: function (data, type, row) {
                                    return `
                                        <button class="btn btn-info btn-sm edit-record" data-id="${row.id}">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-record" data-id="${row.id}">Delete</button>
                                        <button class="btn btn-primary btn-sm show-record" data-id="${row.id}">Show</button>
                                    `;
                                },
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: null,
                                render: function (data, type, row) {
                                    return `<input type="checkbox" class="selected-row" value="${row.id}">`;
                                },
                                orderable: false,
                                searchable: false
                            }
                        ],
                        columnDefs: [
                            { targets: 'no-sort', orderable: false }
                        ],
                        lengthMenu: [5, 10, 25, 50, 100], // Define the pagination length options
                        pageLength: 10, // Default page length when the table is initialized
                    });
                }

                // Redraw the table based on custom input
                $('#status').on('change', function () {
                    if ($.fn.DataTable.isDataTable('#empTable')) {
                        table.draw();
                    }
                });

                // Handle edit button click
                $('#empTable').on('click', '.edit-record', function () {
                    var employeeId = $(this).data('id');

                    // Send an AJAX request to fetch the employee data for editing
                    $.get('/get-employee/' + employeeId, function (data) {
                        // Populate the edit modal with employee data
                        $('#editEmployeeId').val(data.id);
                        $('#editUsername').val(data.username);
                        $('#editName').val(data.name);
                        $('#editEmail').val(data.email);
                        $('#editDate').val(data.date);

                        // Open the edit modal
                        $('#editEmployeeModal').modal('show');
                    });
                });

                // Handle show button click
                $('#empTable').on('click', '.show-record', function () {
                    var employeeId = $(this).data('id');

                    // Send an AJAX request to fetch the employee data for showing
                    $.get('/get-employee/' + employeeId, function (data) {
                        // Populate the show modal with employee data
                        $('#showEmployeeId').text(data.id);
                        $('#showUsername').text(data.username);
                        $('#showName').text(data.name);
                        $('#showEmail').text(data.email);
                        $('#showDate').text(data.date);

                        // Open the show modal
                        $('#showEmployeeModal').modal('show');
                    });
                });

                // Handle delete button click
                $('#empTable').on('click', '.delete-record', function () {
                    if (confirm('Are you sure you want to delete this employee?')) {
                        var employeeId = $(this).data('id');

                        // Send an AJAX request to delete the employee
                        $.ajax({
                            url: '/delete-employee/' + employeeId,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                alert(response.message);
                                // Reload the DataTable to reflect the changes
                                table.ajax.reload();
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                alert('Error deleting employee: ' + xhr.responseText);
                            }
                        });
                    }
                });

                // Handle update button click
                $('#updateEmployee').on('click', function () {
                    var employeeId = $('#editEmployeeId').val();
                    var formData = $('#editEmployeeForm').serialize();

                    // Send an AJAX request to update the employee data
                    $.ajax({
                        url: '/edit-employee/' + employeeId,
                        type: 'PUT',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            alert(response.message);
                            // Close the edit modal
                            $('#editEmployeeModal').modal('hide');
                            // Reload the DataTable to reflect the changes
                            table.ajax.reload();
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            alert('Error updating employee: ' + xhr.responseText);
                        }
                    });
                });

                // Handle bulk delete button click
                $('#deleteSelected').on('click', function () {
                    var selectedIds = [];
                    $('.selected-row:checked').each(function () {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) {
                        alert('Please select at least one employee to delete.');
                        return;
                    }

                    if (confirm('Are you sure you want to delete the selected employees?')) {
                        // Send an AJAX request to delete the selected employees
                        $.ajax({
                            url: '/delete-selected-employees',
                            type: 'DELETE',
                            data: { ids: selectedIds },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                alert(response.message);
                                // Reload the DataTable to reflect the changes
                                table.ajax.reload();
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                alert('Error deleting selected employees: ' + xhr.responseText);
                            }
                        });
                    }
                });
            });
        </script>

    </div>
</body>
</html>