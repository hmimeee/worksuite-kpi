<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Infraction Types</h4>
</div>
<div class="modal-body">
    <div class="portlet-body" id="addInfractionTypeTab" style="display: none;">
        <form id="addInfractionTypeForm">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input class="form-control" type="text" name="name" placeholder="Type name here">
            </div>

            <div class="form-group">
                <label>Reduction Points</label>
                <input class="form-control" type="number" name="reduction_points" placeholder="Type reduction points here" step="any">
            </div>

            <div class="form-group">
                <label>Details</label>
                <textarea class="form-control" name="details" placeholder="Type details here"></textarea>
            </div>

            <div class="form-group">
                <button class="btn btn-sm btn-success">Save</button>
                <button class="btn btn-sm btn-inverse" id="addInfractionTypeCancel">Cancel</button>
            </div>
        </form>
    </div>

    <div class="portlet-body" id="editInfractionTypeTab" style="display: none;">
        <form id="editInfractionTypeForm" data-id="">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label>Name</label>
                <input class="form-control" type="text" name="name" placeholder="Type name here">
            </div>

            <div class="form-group">
                <label>Reduction Points</label>
                <input class="form-control" type="number" name="reduction_points" placeholder="Type reduction points here" step="any">
            </div>

            <div class="form-group">
                <label>Details</label>
                <textarea class="form-control" name="details" placeholder="Type details here"></textarea>
            </div>

            <div class="form-group">
                <button class="btn btn-sm btn-success">Update</button>
                <button type="button" class="btn btn-sm btn-inverse" id="editInfractionTypeCancel">Cancel</button>
            </div>
        </form>
    </div>

    <div class="portlet-body">
        <table class="table table-bordered table-hover" width="100%">
            <thead>
                <tr>
                    <th width="20%">Name</th>
                    <th>Reduction Points</th>
                    <th>Details</th>
                    <th width="15%">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($types as $type)
                <tr>
                    <td>{{$type->name}}</td>
                    <td>{{number_format($type->reduction_points, 2)}}</td>
                    <td>{{$type->details}}</td>
                    <td>
                        <div class="btn-group">
                            <a href="javascript:;" id="editInfractionType" data-id="{{$type->id}}" class="btn btn-xs btn-info">Edit</a>
                            <a href="javascript:;" id="deleteInfractionType" data-id="{{$type->id}}" class="btn btn-xs btn-danger">Delete</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-success btn-sm" id="addInfractionType">Add Type <i class="fa fa-plus" aria-hidden="true"></i></button>
    <button type="button" class="btn default btn-sm" data-dismiss="modal">Close</button>
</div>

<script type="text/javascript">
    $('#addInfractionType').click(function(e){
        e.preventDefault();
        $('#addInfractionTypeTab').toggle('show');
        $(this).toggle('hide');
    });

    $('#addInfractionTypeCancel').click(function(e){
        e.preventDefault();
        $('#addInfractionTypeTab').toggle('hide');
        $('#addInfractionType').toggle('show');
    });

    $('#editInfractionTypeCancel').click(function(e){
        e.preventDefault();
        $('#editInfractionTypeTab').toggle('hide');
        $('tr.active').removeClass('active');
    });

    $('#addInfractionTypeForm').submit(function(e){
        e.preventDefault();
        data = $(this).serialize();
        url = '{{route('admin.kpi.infraction-types.store')}}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: data,
            success: function(res){
                if (res.status == "success") {
                    reloadTypes();
                }
            }
        })
    })

    $('#editInfractionTypeForm').submit(function(e){
        e.preventDefault();
        data = $(this).serialize();
        url = '{{ route('admin.kpi.infraction-types.update', ':id')}}';
        url = url.replace(':id', $(this).data('id'));

        $.easyAjax({
            type: 'POST',
            url: url,
            data: data,
            success: function(res){
                if (res.status == "success") {
                    reloadTypes();
                }
            }
        })
    })

    $('#infractionModal #editInfractionType').click(function(){
        url = '{{ route('admin.kpi.infraction-types.edit', ':id')}}';
        url = url.replace(':id', $(this).data('id'));
        $('tr.active').removeClass('active');
        $(this).parent().parent().parent().addClass('active');

        $.ajax({
            method: 'GET',
            url: url,
            success: function(res){
                if ($('#editInfractionTypeTab').is(":hidden")) {
                    $('#editInfractionTypeTab').toggle('show');
                }

                $('#editInfractionTypeForm').find('input[name=name]').val(res.name);
                $('#editInfractionTypeForm').find('input[name=reduction_points]').val(res.reduction_points);
                $('#editInfractionTypeForm').find('textarea[name=details]').val(res.details);
                $('#editInfractionTypeForm').data('id', res.id);
            }
        });
    })

    $(function () {
        $('body').on('click', '#deleteInfractionType', function () {
            var id = $(this).data('id');

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover the deleted type!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel please!",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function (isConfirm) {
                if (isConfirm) {

                    var url = "{{ route('admin.kpi.infraction-types.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        success: function (response) {
                            if (response.status == "success") {
                                reloadTypes();
                            }
                        }
                    });
                }
            });
        });

    });


    function reloadTypes(){
        url = '{{ route('admin.kpi.infraction-types.index')}}';
        $.ajax({
            method: 'GET',
            url: url,
            success: function(res){
                $('#infractionModal .modal-content').html(res);
            }
        });
    }
</script>