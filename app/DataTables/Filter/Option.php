<?php

namespace  App\DataTables\Filter;

class Option {
    protected $container;

    protected $enabled = false;
    protected $type = "list";
    protected $mode = "WHERE";
    protected $resolver;
    protected $ChartSupport = false;

    public function isListType() {
        return $this->type == 'list';
    }

    public function setType_List() {
        $this->type = 'list';
        return $this;
    }

    public function getMode() {
        return $this->mode;
    }

    public function getType() {
        return $this->type;
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function enable() {
        $this->enabled = true;
        return $this;
    }

    public function setMode_WHERE() {
        $this->type = 'WHERE';
        return $this;
    }

    public function setMode_HAVING() {
        $this->type = 'WHERE';
        return $this;
    }

    public function enableChartSupport() {
        $this->ChartSupport = true;
        return $this;
    }

    public function setResolverByName($name) {
        $resolver = new ItemResolver();
        $this->resolver = $resolver->getResolver($name);
        return $this;
    }

    public function getResolver() {
        return $this->resolver;
    }

}
