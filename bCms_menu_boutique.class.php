<?php

class bCms_menu_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'cms_menu';

        parent::__construct($formvars);
    }
}