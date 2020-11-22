<?php

namespace Modules\KPI\DataTables;

use App\Task;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\KPI\Entities\Infraction;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RatingsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
        ->eloquent($query)
        ->addColumn('action', function($row){
            $action = '<div class="btn-group">';
            $action .= '<a href="javascript:;" class="btn btn-sm btn-info" onclick="editInfraction('.$row->id.')"><i class="fa fa-pencil"></i></a>';
            $action .= '<a href="javascript:;" class="btn btn-sm btn-danger" onclick="deleteInfraction('.$row->id.')"><i class="fa fa-trash"></i></a>';
             $action .= '</div>';
             
            return $action;
        })

        ->editColumn('user_name', function($row){
            $name = '<a href="javascript:;" onclick="viewInfraction('.$row->id.')">'.$row->user->name.'</a>';

            return $name;
        })

        ->editColumn('type_name', function($row){
            if ($row->type) {
                $type = '<label class="label label-info">'.$row->type->name.'</label>';
            } else {
                $type = '<label class="label label-inverse">'.$row->infraction_type.'</label>';
            }

            return $type;
        })

        ->editColumn('created_at', function($row){
            return $row->created_at->format('d M Y');
        })

        ->rawColumns(['action', 'user_name', 'type_name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Modules\KPI\Entities\Infraction $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Task $model)
    {
        $model = $model->with(['users'])->whereHas('users', function($q){
            return $q->where()
        })
        ->groupBy('kpi_infractions.id');

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
        ->setTableId('infractions-table')
        ->columns($this->getColumns())
        ->minifiedAjax()
        ->dom("<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>")
        ->orderBy(0)
        ->destroy(true)
        ->responsive(true)
        ->serverSide(true)
        ->stateSave(true)
        ->processing(true)
        ->language(__("app.datatable"))
        ->buttons(
            Button::make(['extend'=> 'export','buttons' => ['excel', 'csv']])
        )
        ->parameters([
            'initComplete' => 'function () {
                window.LaravelDataTables["infractions-table"].buttons().container()
               .appendTo( ".bg-title .text-right")
           }',
           'fnDrawCallback' => 'function( oSettings ) {
            $("body").tooltip({
              selector: \'[data-toggle="tooltip"]\'
              })
          }',
      ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
        '#' => ['data' => 'id', 'name' => 'id', 'visible' => true],
        'user' => ['data' => 'user_name', 'name' => 'users.name'],
        'type' => ['data' => 'type_name', 'name' => 'kpi_infraction_types.name'],
        'reduction' => ['data' => 'reduction_points', 'name' => 'reduction_points'],
        'date' => ['data' => 'created_at'],
        'infraction_type' => ['visible' => false],
        Column::computed('action')
        ->exportable(false)
        ->printable(false)
        ->orderable(false)
        ->searchable(false)
        ->width(150)
        ->addClass('text-center')
      ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Infractions_' . date('YmdHis');
    }


    public function pdf()
    {
      set_time_limit(0);
      if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
        return $this->snappyPdf();
      }

      $pdf = app('dompdf.wrapper');
      $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

      return $pdf->download($this->getFilename() . '.pdf');
    }
}
