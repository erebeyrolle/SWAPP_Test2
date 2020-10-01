<?php

class bCms_page_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'cms_page';

        parent::__construct($formvars);
    }
}