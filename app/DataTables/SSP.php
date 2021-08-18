<?php

namespace App\DataTables;

use PDO;

class SSP {

    static function limit($request, $columns) {
        $limit = '';
        if (isset($request['start']) && $request['length'] != -1) {
            $limit = "LIMIT " . intval($request['start']) . ", " . intval($request['length']);
        }
        return $limit;
    }


    static function order($request, $columns, Viewer $dtv, $sql_logic=true) {
        $order = '';

        if (isset($request['order']) && count($request['order'])) {
            $orderBy = array();
            $dtColumns = self::pluck($columns, 'db_col');
            for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
                // Convert the column index into the column data property
                $columnIdx = intval($request['order'][$i]['column']);
                $requestColumn = $request['columns'][$columnIdx];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                if ($requestColumn['orderable'] == 'true') {
                    $dir = $request['order'][$i]['dir'] === 'asc' || $request['order'][$i]['dir'] === 'ASC'  ? 'ASC' : 'DESC';
                    $orderBy[] = '' . $column['db'] . ' ' . $dir;
                }
            }
            if (empty($orderBy)) {
                $orderBy = ['NULL'];
            }
            $order = 'ORDER BY '. $dtv->CustomOrderPrefix . ' ' . implode(', ', $orderBy);
            $order .= $dtv->CustomOrderSuffix;

        }
        return $order;
    }


    static function pluck($a, $prop) {
        $out = array();
        for ($i = 0, $len = count($a); $i < $len; $i++) {
            $out[] = $a[$i][$prop];
        }
        return $out;
    }


    static function filter($request, $columns, &$bindings) {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns = self::pluck($columns, 'db_col');

        if (isset($request['search']) && $request['search']['value'] != '') {
            $str = $request['search']['value'];
            for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                if ($requestColumn['searchable'] == 'true' && $column['search_visible']) {
                    $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);

                    if(strpos($column['db'], " as ")!==false){
                        $tmp=explode(" as ",$column['db']);
                        $globalSearch[] = "`" . $tmp[0] . "` LIKE " . $binding;
                    } else {
                        $globalSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
                    }
                }
            }
        }
        // Individual column filtering
        for ($i = 0, $ien = count(@$request['columns']); $i < $ien; $i++) {
            $requestColumn = $request['columns'][$i];
            $columnIdx = array_search($requestColumn['data'], $dtColumns);
            $column = $columns[$columnIdx];
            $str = $requestColumn['search']['value'];
            if ($requestColumn['searchable'] == 'true' && $str != '' && $str != '^\-$') {
                $str = str_replace("$", "", $str);
                $str = str_replace("^", "", $str);
                $binding = self::bind($bindings, '' . $str . '', PDO::PARAM_STR);

                $columnSearch[] = "`" . $column['db'] . "` like " . $binding;
            }
        }
        // Combine the filters into a single string
        $where = '';
        if (count($globalSearch)) {
            $where = '(' . implode(' OR ', $globalSearch) . ')';
        }
        if (count($columnSearch)) {
            $where = $where === '' ? implode(' AND ', $columnSearch) : $where . ' AND ' . implode(' AND ', $columnSearch);
        }
        if ($where !== '') {
            $where = 'WHERE ' . $where;
        }
        return $where;
    }

    static function bind(&$a, $val, $type) {
        $key = ':binding_' . count($a);
        $a[] = array('key' => $key, 'val' => $val, 'type' => $type);
        return $key;
    }

    static function sql_exec(\PDO $db, $bindings, $sql = null) {
        // Argument shifting
        if ($sql === null) {
            $sql = $bindings;
        }
        try{
            $stmt = $db->prepare($sql);
          } catch (\Exception $e) {
            return;
        }
        //echo $sql;
        // Bind parameters
        if (is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }
        // Execute
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            print($sql);
            self::fatal("An SQL error occurred: " . $e->getMessage());
        }
        // Return all
        return $stmt->fetchAll();
    }

    static function fatal($msg) {
        echo json_encode(array("error" => $msg));
        exit(0);
    }
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Internal methods
     */

    static function data_output($columns, $data, Viewer $dtv) {
        $out = array();

        $pk_index = $dtv->GetPrimaryKeyIndex();


        for ($i = 0, $ien = count($data); $i < $ien; $i++) {
            $row = array();
            for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
                $column = $columns[$j];

                $key = $dtv->Columns[(int)$column['dt']]->DBColumn;

                if (isset($column['formatter_internal']) && method_exists(__CLASS__, $column['formatter_internal'])) {
                    $row[$key] = self::$column['formatter_internal']($data[$i][$column['db']], $data[$i]);
                } else if (isset($column['formatter']) && !empty($column['formatter'])) {
                    $row[$key] = $column['formatter']($data[$i][$column['db']], $data[$i]);
                } else {
                    $row[$key] = $data[$i][$columns[$j]['db']];
                }

                if($pk_index !== false && $pk_index == $column['dt']) {
                    $row['DT_RowId'] = $data[$i][$columns[$j]['db']];
                }
            }
            $out[] = $row;
        }
        return $out;
    }


    static function complex(Viewer $dtv, $request, $db, $table, $primaryKey, $columns, $whereResult = null, $whereAll = null, $group = null) {
        $bindings = array();
        $localWhereResult = array();
        $localWhereAll = array();
        $whereAllSql = '';

        // Build the SQL query string from the request
        $limit = self::limit($request, $columns);
        $order = self::order($request, $columns, $dtv);
        $where = self::filter_multi($request, $columns, $bindings);
        $where = self::filter_conditions_where($dtv, $request, $columns, $bindings, $where);
        $whereResult = self::_flatten($whereResult);
        $whereAll = self::_flatten($whereAll);
        if ($whereResult) {
            $where = $where ? $where . ' AND ' . $whereResult : 'WHERE ' . $whereResult;
        }
        if ($whereAll) {
            $where = $where ? $where . ' AND ' . $whereAll : 'WHERE ' . $whereAll;
            $whereAllSql = 'WHERE ' . $whereAll;
        }

        // Main query to actually get the data

        $fields=[];
        foreach (self::pluck($columns, 'db') as $field) {
            $fields[] = $field;
        }

        if (strpos($group, "HAVING") !== false) {
            $group .=  self::filter_conditions_having($dtv, $request, $columns, $bindings, true);
        } else {
            $group .= self::filter_conditions_having($dtv, $request, $columns, $bindings, false);
        }

        $data = self::sql_exec($db, $bindings, "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $fields) . "
			 FROM $table
			 $where
			 $group
			 $order
			 $limit");
        // Data set length after filtering
        $resFilterLength = self::sql_exec($db, "SELECT FOUND_ROWS()");
        $recordsFiltered = $resFilterLength[0][0];
        // Total data set length

        if (strpos($group, "HAVING") !== false) {
            $group = substr($group, 0, strpos($group, "HAVING"));
        }

        $resTotalLength = self::sql_exec($db, $bindings, "SELECT SQL_CALC_FOUND_ROWS $primaryKey
			 FROM   $table  " . $whereAllSql . $group . " ORDER BY NULL");
        $recordsTotal = self::sql_exec($db, "SELECT FOUND_ROWS()");
        $recordsTotal = $recordsTotal[0][0];


        $sum_query = '';
        $columns_total = [];
        $columns_avg = [];
        if($dtv->ShowTotals) {
            $columns_total = $dtv->GetColumnsForTotal();
            $sum_cols = [];
            foreach($columns_total as $Col) {
                $sum_cols[] .= $Col['dbq'] == $Col['db'] ? ' SUM('.$Col['dbq'].') as '.$Col['db'] : ''.$Col['dbq'].' as '.$Col['db'];
            }

            $columns_avg = $dtv->GetColumnsForAverage();
            foreach($columns_avg as $Col) {
                $sum_cols[] .= $Col['dbq'] == $Col['db'] ? ' SUM('.$Col['dbq'].') as '.$Col['db'] : ''.$Col['dbq'].' as '.$Col['db'];
            }

            $sum_query = implode(",\n", $sum_cols);
        }

        if(!empty($sum_query)) {
            $sumTotalResult = null;
            $sumTotal = self::sql_exec( $db, $bindings, "SELECT ".$sum_query." FROM $table ".$where );

            foreach($columns_total + $columns_avg as $pos => $column) {

                if (isset($column['formatter_internal']) && method_exists(__CLASS__, $column['formatter_internal'])) {
                    $value = self::$column['formatter_internal']($sumTotal[0][$column['db']], $sumTotal[0]);
                } else if (isset($column['formatter']) && !empty($column['formatter'])) {
                    $value = $column['formatter']($sumTotal[0][$column['db']], $sumTotal[0]);
                } else {
                    $value = $sumTotal[0][$column['db']];
                }

                $sumTotalResult[] = [
                    'db' => $column['db'],
                    'pos' => $pos,
                    'value' => $value
                ];
            }
        } else {
            $sumTotalResult = null;
        }


        return [
            "draw" => intval($request['draw']),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => self::data_output($columns, $data, $dtv),
            "total" => $sumTotalResult
        ];
    }

    static function filter_multi($request, $columns, &$bindings) {
        if (!isset($request['search']) || $request['search']['value'] == '' || strpos($request['search']['value'], " ") === false) {
            return self::filter($request, $columns, $bindings);
        }
        $globalSearchs = array();
        $dtColumns = self::pluck($columns, 'db_col');
        foreach (explode(" ", $request['search']['value']) as $searchstring) {
            if(empty($searchstring)) continue;
            $str = $searchstring;
            $globalSearch = array();
            for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
                $requestColumn = $request['columns'][$i];
                $columnIdx = array_search($requestColumn['data'], $dtColumns);
                $column = $columns[$columnIdx];

                if ($requestColumn['searchable'] == 'true' && $column['search_visible']) {
                    $binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);

                    if(strpos($column['db'], " as ")!==false){
                        $tmp=explode(" as ",$column['db']);
                        $globalSearch[] = "`" . $tmp[0] . "` LIKE " . $binding;
                    } else {
                        $globalSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
                    }
                }
            }
            $globalSearchs[] = $globalSearch;

        }


        // Combine the filters into a single string
        $where = '1=1';
        if (count($globalSearchs)) {
            foreach ($globalSearchs as $globalSearch) {
                $where .= ' AND (' . implode(' OR ', $globalSearch) . ')';
            }
        }

        if ($where !== '') {
            $where = 'WHERE ' . $where;
        }
        return $where;
    }

    /**
     * Return a string from an array or a string
     *
     * @param  array|string $a Array to join
     * @param  string $join Glue for the concatenation
     * @return string Joined string
     */
    static function _flatten($a, $join = ' AND ') {
        if (!$a) {
            return '';
        } else if ($a && is_array($a)) {
            return implode($join, $a);
        }
        return $a;
    }


    static function filter_conditions_where(Viewer $dtv, $request, $columns, &$bindings, $where=false) {
        if(!isset($request['conditions']) || empty($request['conditions'])) return $where;
        $conditions = json_decode($request['conditions'], true);
        $globalSearchs = array();

        foreach($conditions as $condition) {
            $id = $dtv->GetConditionColumnIdByColumnName($condition['field']['value']);
            if($id === false) continue;
            $column_definition = $dtv->GetConditionColumnByColumnName($condition['field']['value']);
            if($column_definition->ConditionFilterOptions->getMode() != 'WHERE') continue;
            $column = $columns[$id];
            if(empty($column)) continue;

            switch($condition['operator']['value']) {
                case 'gt':
                    $binding = self::bind($bindings, str_replace(',', '.',$condition['value']['value']), PDO::PARAM_STR);
                    $col = preg_replace('/ as (.*)/i', '', $column['db']);
                    $globalSearchs[] = "" . $col . " > " . $binding;
                    break;
                case 'lt':
                    $binding = self::bind($bindings, str_replace(',', '.',$condition['value']['value']), PDO::PARAM_STR);
                    $col = preg_replace('/ as (.*)/i', '', $column['db']);
                    $globalSearchs[] = "" . $col . " < " . $binding;
                    break;
                case 'eq':
                    $binding = self::bind($bindings, str_replace(',', '.',$condition['value']['value']), PDO::PARAM_STR);
                    $col = preg_replace('/ as (.*)/i', '', $column['db']);
                    $globalSearchs[] = "" . $col . " = " . $binding;
                    break;
                case 'in':
                    $values = explode(',', $condition['value']['value']);
                    $sub_searches_in = [];
                    foreach($values as $value) {
                        $binding = self::bind($bindings, $value, PDO::PARAM_STR);
                        $col = preg_replace('/ as (.*)/i', '', $column['db']);
                        $sub_searches_in[]= "" . $col . " = " . $binding;
                    }
                    $globalSearchs[] = '('.implode(' OR ', $sub_searches_in).')';
                    break;
                default:

            }
        }

        // Combine the filters into a single string
        if (count($globalSearchs)) {
            if(!$where) {
                $where .= ' WHERE (' . implode(' AND ', $globalSearchs) . ') ';
            } else {
                $where .= ' AND (' . implode(' AND ', $globalSearchs) . ') ';
            }

        }
        return $where;
    }

    static function filter_conditions_having(Viewer $dtv, $request, $columns, &$bindings, $having=false)
    {
        if (!isset($request['conditions']) || empty($request['conditions'])) return false;
        $conditions = json_decode($request['conditions'], true);
        $globalSearchs = array();

        foreach ($conditions as $condition) {
            $id = $dtv->GetConditionColumnIdByColumnName($condition['field']['value']);
            if ($id === false) continue;
            $column_definition = $dtv->GetConditionColumnByColumnName($condition['field']['value']);
            if ($column_definition->ConditionFilterOptions->getMode() != 'HAVING') continue;
            $column = $columns[$id];
            if (empty($column)) continue;

            switch ($condition['operator']['value']) {
                case 'gt':
                    $binding = self::bind($bindings, str_replace(',', '.', $condition['value']['value']), PDO::PARAM_STR);
                    $col = preg_replace('/ as (.*)/i', '', $column['db']);
                    $globalSearchs[] = "" . $col . " > " . $binding;
                    break;
                case 'lt':
                    $binding = self::bind($bindings, str_replace(',', '.', $condition['value']['value']), PDO::PARAM_STR);
                    $col = preg_replace('/ as (.*)/i', '', $column['db']);
                    $globalSearchs[] = "" . $col . " < " . $binding;
                    break;
                case 'eq':
                    $binding = self::bind($bindings, str_replace(',', '.', $condition['value']['value']), PDO::PARAM_STR);
                    $col = preg_replace('/ as (.*)/i', '', $column['db']);
                    $globalSearchs[] = "" . $col . " = " . $binding;
                    break;
                case 'in':
                    $values = explode(',', $condition['value']['value']);
                    $sub_searches_in = [];
                    foreach ($values as $value) {
                        $binding = self::bind($bindings, $value, PDO::PARAM_STR);
                        $col = preg_replace('/ as (.*)/i', '', $column['db']);
                        $sub_searches_in[] = "" . $col . " = " . $binding;
                    }
                    $globalSearchs[] = '(' . implode(' OR ', $sub_searches_in) . ')';
                    break;
                default:
            }
        }
    }
}
