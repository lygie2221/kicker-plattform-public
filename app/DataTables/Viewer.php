<?php
namespace App\DataTables;


use App\DataTables\Column;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Viewer {
    public $c = null;

    /**
     * @var \PDO
     */
    private $pdo = null;

    /**
     * @var $Columns Column[]
     */
    public $Columns = [];
    public $where = [];

    public $RestInterface = '';
    public $ID;
    public $TableQuery = '';
    public $ShowTotals = false;
    public $CustomOrderSuffix = '';
    public $CustomOrderPrefix = '';

    public $State = '';
    public $StateRestInterfaceLoad = '';
    public $StateRestInterfaceSave = '';
    public $trendURL="";
    public $dom=false;

    public $filter_select_options = [];
    protected $filter_functions = [];

    public $lengthMenu = [
        10 => 10,
        25 => 25,
        50 => 50,
        100 => 100
    ];
    public $iDisplayLength = 10;

    public function __construct(Application $container){
        $this->c = $container;
        $this->pdo = DB::connection()->getPdo();
    }

    public function SetTableId($id) {
        $this->ID = $id;
    }

    public function saveState($auth, $json_state) {
        $user = (string)$auth::user()->id;

        $sth = $this->pdo->prepare("INSERT INTO
                                          dt_states
                                      SET
                                        username = :username,
                                        table_id = :table_id,
                                        data = :data
                                        ON DUPLICATE KEY UPDATE data =:data2");

        $sth->bindParam(':table_id', $this->ID, \PDO::PARAM_STR);
        $sth->bindParam(':username', $user, \PDO::PARAM_STR);
        $sth->bindParam(':data', $json_state, \PDO::PARAM_STR);
        $sth->bindParam(':data2', $json_state, \PDO::PARAM_STR);
        $sth->execute();

        echo json_encode(time());
    }

    public function loadState() {
        $user = \Auth::user()->id;

        $sql="SELECT `data` FROM dt_states WHERE username = :username AND table_id = :table_id";

        $sth = $this->pdo->prepare($sql);
        $sth->bindParam(':table_id', $this->ID, \PDO::PARAM_STR);
        $sth->bindParam(':username', $user, \PDO::PARAM_STR);
        $sth->execute();
        $d = $sth->fetchColumn();

        $json = !empty($d)
            ? $d
            : json_encode([false]);

        $this->State = $json;
    }




    public function SetFilterFunction($key, $function) {
        $this->filter_functions[$key] = $function;

    }

    public function SetFilterSelectOptions($key, $options) {
        $this->filter_select_options[$key] = $options;
    }

    public function GetFilterSelectOptions() {
        return $this->filter_select_options;
    }

    public function handleFilters($filters) {
        $filters_result_info = [];
        foreach($filters as $filter => $filter_value) {
            if(isset($this->filter_functions[$filter])) {
                $filters_result_info[$filter][] = $this->filter_functions[$filter]($this, $filter_value);
            }
        }

        return $filters_result_info;
    }

    public function handleFiltersForApi($filters) {
        $filters_result_info = [];
        foreach($filters as $filter => $filter_value) {
            if(isset($this->filter_functions[$filter])) {
                $filters_result_info[$filter][] = $this->filter_functions[$filter]($this, $filter_value);
            }
        }

        return $filters_result_info;
    }

    public function GetSortByColumn() {
        foreach($this->Columns as $Idx => $C) {
            if($C->SortByColumn) return ['Column' => $Idx, 'Order' => $C->SortByColumn ];
        }
        return false;
    }

    public function AddColumn(Column $column) {
        $this->Columns[] = $column;
    }

    public function AddColumns($columns) {
        foreach($columns as $id => $c) {
            $c->id=$id;
            $this->Columns[] = $c;
        }
    }

    public function removeColumn($identifier){
        foreach($this->Columns as $id => $column){
            if($column->id==$identifier){
                unset($this->Columns[$id]);
            }
        }
        $this->Columns = array_values($this->Columns);

    }

    public function GetColumnsForSSP() {
        $viscols = array_map('intval', explode(",", $_REQUEST['viscols']));
        $columns = [];

        foreach($this->Columns as $Pos => $C) {
            if(!($C instanceof Column)){
                continue;
            }
            $columns[$Pos]['dt'] = $Pos;
            $columns[$Pos]['db_col'] = $C->DBColumn;
            $columns[$Pos]['search_visible'] = in_array($Pos, $viscols);
            $columns[$Pos]['db'] = empty($C->DBColumnSelectQuery) ? $C->DBColumn : $C->DBColumnSelectQuery;

            if(!is_null($C->InternalFormatterFunction)) {
                $columns[$Pos]['formatter'] = $C->InternalFormatterFunction;
            }
        }
        return $columns;
    }

    public function GetColumnsForTotal() {
        $columns = [];
        foreach($this->Columns as $Pos => $C) {
            if($C->ShowInTotalsAsSum) {
                $columns[$Pos]['dt'] = $Pos;
                $columns[$Pos]['dbq'] = empty($C->DBColumnSelectQuery) ? $C->DBColumn : $C->DBColumnSelectQuery;
                $columns[$Pos]['db'] = $C->DBColumn;

                if(!is_null($C->InternalFormatterFunction)) {
                    $columns[$Pos]['formatter'] = $C->InternalFormatterFunction;
                }
            }
        }
        return $columns;
    }

    public function GetColumnsForAverage() {
        $columns = [];
        foreach($this->Columns as $Pos => $C) {
            if($C->ShowInTotalsAsAverage) {
                $columns[$Pos]['dt'] = $Pos;
                $columns[$Pos]['dbq'] = empty($C->DBColumnSelectQuery) ? $C->DBColumn : $C->DBColumnSelectQuery;
                $columns[$Pos]['db'] = $C->DBColumn;

                if(!is_null($C->InternalFormatterFunction)) {
                    $columns[$Pos]['formatter'] = $C->InternalFormatterFunction;
                }
            }
        }
        return $columns;
    }

    public function SetTableQuery($Query) {
        $this->TableQuery = $Query;
    }

    public function SetRestInterfaceByName($name) {
        $this->RestInterface = route($name);
    }

    public function SetRestInterfaceByNameMode($name,$mode) {
        $this->RestInterface = route($name);
    }

    public function SetTrendURLByMame($name){
        $this->trendURL = route($name);

    }

    public function GetPrimaryKey() {
        foreach($this->Columns as $C) {
            if($C->PrimaryKey) return $C->DBColumn;
        }
        return false;
    }

    public function GetPrimaryKeyIndex() {
        foreach($this->Columns as $Idx => $C) {
            if($C->PrimaryKey) return $Idx;
        }
        return false;
    }

    public function GetGroupByForSSP() {
        $viscols = array_map('intval', explode(",", $_REQUEST['viscols']));

        $columns = [];

        foreach($this->GetGroupColumns() as $Idx => $C) {
            if(in_array($Idx, $viscols)) {
                $columns[] = $C->DBColumn;
            }
        }


        //if(in_array($columns)) $columns[] =  $this->GetPrimaryKey();
        if(!empty($columns)) {
            if(!in_array($this->GetPrimaryKey(), $columns)) $columns[] =  $this->GetPrimaryKey();
            return 'GROUP BY ' . implode(',', $columns);
        } elseif(!empty($this->GetPrimaryKey())) {
            return 'GROUP BY ' . $this->GetPrimaryKey();
        }
        return '';
    }

    public function GetGroupColumns() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if($C->IsGroupByAllowed) $columns[$Idx] = $C;
        }
        return $columns;
    }


    public function GetHiddenColumns() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if(!$C->IsVisible) $columns[] = $Idx;
        }
        return $columns;
    }

    public function GetUnsearchableColumns() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if(!$C->Searchable) $columns[] = $Idx;
        }
        return $columns;
    }

    public function GetClassNames() {
        $classNames = [];
        foreach($this->Columns as $Idx => $C) {
            if(!empty($C->CSSClassName)) $classNames[trim($C->CSSClassName)][$Idx] = $Idx;
        }
        return $classNames;
    }

    public function GetShownInColumnVisbilityColumns() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if($C->ShownInColumnVisiblity === true && $C->PrimaryKey === false) $columns[] = $Idx;
        }
        return $columns;
    }


    public function GetColumnsForPdfExport() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if($C->isAllowedForPdfExport === true ){
                $columns[] = $Idx;
            }
        }
        return $columns;
    }

    public function GetColumnsForCsvExport() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            $columns[] = $Idx;
        }
        return $columns;
    }

    public function dtViewJSConfig() {
        $jsConfig = new \stdClass();

        //$jsConfig->dom = !empty($this->dom) ? $this->dom : 'Bf<"#'.$this->ID.'Conditions"><"#'.$this->ID.'Chart.dt-table-chart">rtlip';
        $jsConfig->dom = !empty($this->dom) ? $this->dom : 'rt<"#'.$this->ID.'Conditions"><"dataTables_footer"ip>';

        $jsConfig->columns = [];
        foreach($this->Columns as $column) {
            $col = new \stdClass();
            $col->data = $column->DBColumn;
            $jsConfig->columns[] = $col;
        }

        if($this->GetSortByColumn()) $jsConfig->order = [ [ $this->GetSortByColumn()['Column'], $this->GetSortByColumn()['Order'] ] ];


        $columnDefs_orderSequence = new \stdClass();
        $columnDefs_orderSequence->orderSequence = ['desc', 'asc'];
        $columnDefs_orderSequence->targets = array_values($this->GetInvertOrderColumns());

        $columnDefs_width = new \stdClass();
        $columnDefs_width->width = '20%';
        $columnDefs_width->targets = array_values($this->GetWideColumns());

        $columnDefs_searchable = new \stdClass();
        $columnDefs_searchable->searchable = false;
        $columnDefs_searchable->targets = array_values($this->GetUnsearchableColumns());

        $columnDefs_visible = new \stdClass();
        $columnDefs_visible->visible = false;
        $columnDefs_visible->targets = array_values($this->GetHiddenColumns());


        $jsConfig->columnDefs = [
            $columnDefs_orderSequence,
            $columnDefs_width,
            $columnDefs_searchable,
            $columnDefs_visible
        ];

        foreach($this->GetClassNames() as $className => $columns) {
            $columnDefs_class = new \stdClass();
            $columnDefs_class->className = $className;
            $columnDefs_class->targets = array_values($columns);

            $jsConfig->columnDefs[] = $columnDefs_class;
        }

        $jsConfig->iDisplayLength = $this->iDisplayLength;
        $jsConfig->lengthMenu = [array_keys($this->lengthMenu), array_values($this->lengthMenu) ];


        $jsConfig->extra = new \stdClass();
        $jsConfig->extra->columnsForPdfExport = array_values($this->GetColumnsForPdfExport());
        $jsConfig->extra->restInterface = $this->RestInterface;
        $jsConfig->extra->stateRestInterfaceSave = $this->StateRestInterfaceSave;
        $jsConfig->extra->piwikEnabled = false;

        $jsConfig->extra->conditionFilterOptions = new \stdClass();
        $jsConfig->extra->conditionFilterOptions->buttonLabels = true;
        $jsConfig->extra->conditionFilterOptions->dateFormat = "yy-mm-dd";
        $jsConfig->extra->conditionFilterOptions->highlight = true;
        $jsConfig->extra->conditionFilterOptions->fields = [];


        foreach($this->GetConditionFilterOptions() as $Column) {

            if($Column->ConditionFilterOptions->isListType()) {
                $filter = new \stdClass();
                $filter->id = $Column->Name;
                $filter->type = 'list';
                $filter->label = $Column->Name;
                $filter->list = [];
                foreach($Column->ConditionFilterOptions->getResolver()->getList() as $key => $value) {
                    $element=new \stdClass();
                    $element->id = $key;
                    $element->label = utf8_encode($value);
                    $filter->list[]=$element;

                }
            } else {
                $filter = new \stdClass();
                $filter->id = $Column->Name;
                $filter->type = $Column->ConditionFilterOptions->getType();
                $filter->label = utf8_encode($Column->Name);
            }

            $jsConfig->extra->conditionFilterOptions->fields[] = $filter;

        }


        $jsConfig->State = $this->State;
        $jsConfig->elementId = $this->ID;

        return base64_encode(json_encode($jsConfig));
    }


    public function GetWideColumns() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if(@$C->isWideColumn === true ){
                $columns[] = $Idx;
            }
        }
        return $columns;
    }

    public function GetInvertOrderColumns(){
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if($C->hasInvertOrder === true ){
                $columns[] = $Idx;
            }
        }
        return $columns;
    }

    /**
     * @return Column[]
     */
    public function GetConditionFilterOptions() {
        $columns = [];
        foreach($this->Columns as $Idx => $C) {
            if($C->ConditionFilterOptions->isEnabled()) $columns[$Idx] = $C;
        }

        usort($columns, function($a, $b) {
            return $a->Name > $b->Name;
        });

        return $columns;
    }

    public function HandleRestRequest(Request $request, $args) {
        $this->pdo->exec("SET NAMES utf8;");
        $data=SSP::complex(
            $this,
            $_REQUEST,
            $this->pdo,
            $this->TableQuery,
            $this->GetPrimaryKey(),
            $this->GetColumnsForSSP(),
            null,
            $this->where,
            $this->GetGroupByForSSP($request)
        );
        foreach($data["data"] as &$row){
            $object = new \stdClass();
            foreach ($row as $key => $value)
            {
                $object->$key = $value;
            }
            $row=$object;
        }
        unset($row);

        return response()->json($data);
    }

    public function GetConditionColumnIdByColumnName($DBColumn) {
        foreach($this->Columns as $Idx => $C) {
            if($C->Name == $DBColumn && !empty($C->ConditionFilterOptions) ) return $Idx;
        }
        return false;
    }

    public function GetConditionColumnByColumnName($DBColumn) {
        foreach($this->Columns as $Idx => $C) {
            if($C->Name == $DBColumn && !empty($C->ConditionFilterOptions)) return $C;
        }
        return false;
    }


    public function sortColumns($sortby){
        $newcolumns=[];
        foreach($sortby as $identifier){
            foreach($this->Columns as $id => $column){
                if($identifier==$column->id){
                    $newcolumns[]=$column;
                    unset($this->Columns[$id]);
                }
            }
        }
        $this->Columns=array_values($this->Columns);
        $this->Columns=array_merge($newcolumns,$this->Columns);
    }
}
