<?php

class bSujet_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'sujet';

        parent::__construct($formvars);
    }
}