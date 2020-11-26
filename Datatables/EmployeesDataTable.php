<?php

namespace Modules\KPI\DataTables;

use App\Task;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\KPI\Entities\Employee;
use Yajra\DataTables\Services\DataTable;
use Modules\KPI\Entities\TaskPerformance;

class EmployeesDataTable extends DataTable
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

            ->editColumn('name', function ($row) {
                $name = '<a href="javascript::" id="userTasks" data-id="'.$row->user->id.'">'.$row->user->name.'</a>';

                return $name;
            })

            ->editColumn('rating', function ($row) {
            $rating = $row->rating ?? 0;
            $html = '';
            foreach (range(1, 5) as $i) {
                $html .= '<span class="fa-stack" style="width:1em"><i class="fa fa-star fa-stack-1x"></i>';
                if ($rating > 0) {
                    if ($rating > 0.5) {
                        $html .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
                    } else {
                        $html .= '<i class="fa fa-star-half fa-stack-1x text-warning" style="margin-left: -3px;"></i>';
                    }
                }
                $rating--;
                $html .= '</span>';
            }
            $html .= ' (' . number_format($rating, 1) . ')';

            return $html;
            })

            ->editColumn('score', function ($row) {
                return Employee::taskScores($row->user_id);
            })

            ->rawColumns(['name', 'rating', 'score']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Modules\KPI\Entities\TaskPerformance $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(TaskPerformance $model)
    {
        $model = $model->with('user');
        // if (!auth()->user()->hasRole('admin')) {
        //     $model = $model;
        // }

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
            ->setTableId('employees-table')
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
                Button::make(['extend' => 'export', 'buttons' => ['excel', 'csv']])
            )
            ->parameters([
                'initComplete' => 'function () {
                window.LaravelDataTables["employees-table"].buttons().container()
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
            'name' => ['data' => 'name', 'name' => 'user.name'],
            'rating',
            'score',
            // Column::computed('action')
            //     ->exportable(false)
            //     ->printable(false)
            //     ->orderable(false)
            //     ->searchable(false)
            //     ->width(150)
            //     ->addClass('text-center')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Employees_' . date('YmdHis');
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
