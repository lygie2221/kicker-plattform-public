<?php

namespace App\DataTables\Tables;


use App\DataTables\Column;
use App\DataTables\Viewer;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;

class Spiele extends Viewer
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
                    spiel.ergebnis,
                    standort.name as standort
                FROM
                    spiel
                INNER JOIN
                    begegnung
                    ON
                        spiel.begegnung_id = begegnung.id
                INNER JOIN
                    standort
                    ON
                        standort.id = begegnung.standort_id



        ";


        $this->SetTableQuery('(' . $sql . ') as tbl');

        // Setup Columns
        $columns = [];

        $columns['ergebnis'] = new Column('Ergebnis', 'ergebnis');
        $columns['ergebnis']->GroupKey = true;
        $columns['ergebnis']->IsVisible = true;
        $columns['ergebnis']->IsGroupByAllowed = true;
        $columns['ergebnis']->SortByColumn = true;

        $columns['standort'] = new Column('Standort', 'Standort');
        $columns['standort']->GroupKey = true;
        $columns['standort']->PrimaryKey = true;
        $columns['standort']->IsVisible = true;
        $columns['standort']->IsGroupByAllowed = true;

        $this->AddColumns($columns);


    }
}
