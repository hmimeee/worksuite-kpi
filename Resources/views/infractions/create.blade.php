<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/switchery/dist/switchery.min.css') }}">

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Add Infraction</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <form id="addInfractionForm" method="post">
            @csrf
            <div class="form-group">
                <label class="control-label">Employee</label>
                <select class="select2 form-control" name="user_id" id="user_id">
                    @foreach($employees as $employee)
                    <option value="{{$employee->id}}">{{$employee->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-xs-10" id="infractionTypeTab">
                    <div class="form-group">
                        <label class="control-label">Infraction Type</label>
                        <select class="select2 form-control" name="infraction_type_id" id="infraction_type_id">
                            @foreach($types as $type)
                            <option value="{{$type->id}}">{{$type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-xs-10" id="manualInfractionTab" style="display: none;">
                    <div class="col-xs-8">
                        <div class="form-group">
                            <label class="control-label">Infraction Name</label>
                            <input class="form-control" type="text" name="infraction_type" placeholder="Write infraction type here">
                        </div>
                    </div>

                    <div class="col-xs-4">
                        <div class="form-group">
                            <label>Reduction Points</label>
                            <input class="form-control" type="number" name="reduction_points" placeholder="Type reduction points here" step="any">
                        </div>
                    </div>
                </div>

                <div class="col-xs-2">
                    <div class="form-group">
                        <label class="control-label">From List</label>
                        <br>
                        <input type="checkbox" class="js-switch" name="from_list" id="from_list" value="1" checked />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">Notes</label>
                <textarea class="form-control" name="details" placeholder="Type details here"></textarea>
            </div>

            <div class="form-group">
                <button class="btn btn-sm btn-success">Save</button>
                <button type="button" class="btn default btn-sm" data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/switchery/dist/switchery.min.js') }}"></script>
<script type="text/javascript">
    $(".select2").select2();

    //Checkbox Style
    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
    elems.forEach(function(html) {
        var switchery = new Switchery(html);
    });

    $('#from_list').change(function(){
        if ($(this).prop('checked')) {
            $('#manualInfractionTab').toggle('show');
            $('#infractionTypeTab').toggle('hide');
        } else {
            $('#manualInfractionTab').toggle('show');
            $('#infractionTypeTab').toggle('hide');
        }
    });

    $('#addInfractionForm').submit(function(e){
        e.preventDefault();
        @if($user->hasRole('admin'))
        url = '{{route('admin.kpi.infractions.store')}}';
        @else
        url = '{{route('member.kpi.infractions.store')}}';
        @endif

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $(this).serialize(),
            success: function(res){
                if (res.status == "success") {
                    window.LaravelDataTables["infractions-table"].draw();
                    $('#infractionModal').modal('toggle');
                }
            }
        })
    })
</script>