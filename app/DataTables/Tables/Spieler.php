<?php

namespace App\DataTables\Tables;


use App\DataTables\Column;
use App\DataTables\Viewer;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;

class Spieler extends Viewer
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
                    users.name as name,
                    users.email as email,
                    standort.name as Standort
                FROM
                    users
                LEFT JOIN
                    standort
                    ON
                        standort.id = users.standort_id

        ";



        $this->SetTableQuery('(' . $sql . ') as tbl');

        // Setup Columns
        $columns = [];

        $columns['name'] = new Column('Name', 'name');
        $columns['name']->GroupKey = true;
        $columns['name']->IsVisible = true;
        $columns['name']->IsGroupByAllowed = true;
        $columns['name']->SortByColumn = true;

        $columns['email'] = new Column('E-Mail', 'email');
        $columns['email']->IsVisible = true;
        $columns['email']->IsGroupByAllowed = true;
        $columns['email']->SortByColumn = true;

        $columns['standort'] = new Column('Standort', 'Standort');
        $columns['standort']->PrimaryKey = true;
        $columns['standort']->IsVisible = true;
        $columns['standort']->IsGroupByAllowed = true;

        $this->AddColumns($columns);


    }
}
