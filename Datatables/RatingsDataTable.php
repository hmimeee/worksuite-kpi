<?php

namespace Modules\KPI\Datatables;

use App\Task;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
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

            ->editColumn('heading', function ($row) {
                $name = '<a href="javascript:;" onclick="showTask(' . $row->id . ')">' . $row->heading . '</a>';

                return $name;
            })

            ->editColumn('rating', function ($row) {
                $rating = $row->rating;
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

                return $html;
            })

            ->editColumn('users', function ($row) {
                $members = '';
                foreach ($row->users as $member) {
                    $members .= '<a href="' . route('admin.employees.show', [$member->id]) . '">';
                    $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($member->name) . '" src="' . $member->image_url . '"
                alt="user" class="img-circle" width="25" height="25"> ';
                    $members .= '</a>';
                }
                return $members;
            })

            ->rawColumns(['users', 'heading', 'rating']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Task $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Task $model)
    {
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');
        $date = request()->month ? Carbon::createFromDate($year, $month, date('d')) : Carbon::now();
        $startDate = $date->firstOfMonth()->format('Y-m-d H:i');
        $endDate = $date->endOfMonth()->format('Y-m-d H:i');

        $model = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
        ->leftJoin('users as creator_user', 'creator_user.id', '=', 'tasks.created_by')
        ->select('tasks.*', 'task_users.user_id')
        ->groupBy('tasks.id');


        $model = $model->where('tasks.board_column_id', 2)->whereBetween('tasks.completed_on', [$startDate, $endDate]);

        if (request()->employee) {
            $model = $model->where('task_users.user_id', request()->employee)
            ->orWhere('tasks.created_by', request()->employee);
        }
        // dd($model->get());

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
            ->setTableId('tasks-table')
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
                window.LaravelDataTables["tasks-table"].buttons().container()
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
            'title' => ['data' => 'heading', 'name' => 'heading'],
            'rating' => ['data' => 'rating', 'name' => 'rating'],
            'assigned_to' => ['data' => 'users', 'name' => 'users.name'],
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
        return 'Review_Rating_' . date('YmdHis');
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
