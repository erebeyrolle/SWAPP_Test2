<?php

class bParrainage_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'parrainage';

        parent::__construct($formvars);
    }
}