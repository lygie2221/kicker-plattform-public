<?php

namespace  App\DataTables\Filter;

abstract class ItemList {
    protected $container;
    protected $item_table = "";
    protected $item_key = "id";
    protected $item_name = "name";
    protected $item_query = '';


    public function getResolver() {
        return $this;
    }

    public function getList() {
        $id = $this->item_key;
        $name = $this->item_name;
        $tbl = $this->item_table;
        $where = $this->item_query;

        $SQL = "SELECT $id, $name FROM $tbl $where";

        $pdo = \DB::connection()->getPdo();

        $sth = $pdo->prepare($SQL);
        $sth->execute();

        $result = [];
        foreach($sth->fetchAll(\PDO::FETCH_ASSOC) as $d) {
            $result[ $d[$this->item_key] ] = utf8_decode($d[$this->item_name]);
        }

        asort($result);

        return $result;
    }
}