<?php

namespace App\DataTables\Tables;


use App\DataTables\Column;
use App\DataTables\Viewer;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;

class Begegnungen extends Viewer
{
    public function __construct(Application $container)
    {
        parent::__construct($container);

        $this->StateRestInterfaceLoad = route('dataTables:stateLoad');
        $this->StateRestInterfaceSave = route('dataTables:stateSave');
        $this->SetTableId(sha1(__CLASS__));
        $this->SetRestInterfaceByName(__CLASS__);
        $this->loadState();
        $this->initColumns();

    }

    protected function initColumns()
    {

        $from = date('Y-m-d H:i:s', strtotime(@$_REQUEST['date_start']));
        $to = date('Y-m-d 23:59:59', strtotime(@$_REQUEST['date_end']));

        $this->ShowTotals = true;

        $sql="SELECT
                    Modus as modus,
                    standort.name as Standort,
                    begegnung.created_at as Datum
                FROM
                    begegnung
                INNER JOIN
                    standort
                    ON
                        standort.id = begegnung.standort_id

        ";


        $this->SetTableQuery('(' . $sql . ') as tbl');

        // Setup Columns
        $columns = [];

        $columns['modus'] = new Column('Modus', 'modus');
        $columns['modus']->GroupKey = true;
        $columns['modus']->IsVisible = true;
        $columns['modus']->IsGroupByAllowed = true;
        $columns['modus']->SortByColumn = true;

        $columns['standort'] = new Column('Standort', 'Standort');
        $columns['standort']->GroupKey = true;
        $columns['standort']->PrimaryKey = true;
        $columns['standort']->IsVisible = true;
        $columns['standort']->IsGroupByAllowed = true;

        $this->AddColumns($columns);


    }
}
