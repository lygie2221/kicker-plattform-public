<?php

namespace  App\DataTables\Filter;

class ItemResolver {

    public function getResolver($name) {
        if(class_exists(__NAMESPACE__.'\\'.$name)) {
            $class = __NAMESPACE__.'\\'.$name;
            $item = new $class();
            return $item;
        }
        return null;
    }

    public function getList($name) {
        if(class_exists(__NAMESPACE__.'\\'.$name)) {
            $class = __NAMESPACE__.'\\'.$name;
            $item = new $class();
            return $item->getList();
        }

    }
}