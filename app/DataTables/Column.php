<?php

namespace App\DataTables;

use App\DataTables\Filter\Option;

class Column {

    public $Name = false;
    public $DBColumn = false;
    public $IsGroupByAllowed = false;
    public $DColumnSelectQuery = false;
    public $SortByColumn = false;
    public $PrimaryKey = false;
    public $GroupKey = false;
    public $DisabledOrdering = false;
    public $IsVisible = true;
    public $ShowInTotalsAsSum = false;
    public $ShowInTotalsAsAverage = false;
    public $CSSClassName = '';
    public $ShownInColumnVisiblity = true;
    public $Searchable = true;
    public $isAllowsForCsfExport = false;
    public $isAllowedForPdfExport = false;
    public $hasInvertOrder=false;
    public $id=false;

    /**
     * @var Option
     */
    public $ConditionFilterOptions = null;

    public $InternalFormatterFunction = null;

    public function __construct($Name, $DBColumn, $DColumnSelectQuery=false) {
        $this->Name = $Name;
        $this->DBColumn = $DBColumn;
        $this->DBColumnSelectQuery = $DColumnSelectQuery;
        $this->ConditionFilterOptions = new Option();
    }

    public function SetFormatterFunction($func) {
        $this->InternalFormatterFunction = $func;
    }
}
