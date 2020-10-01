<?php

class bMarque_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'marque';

        parent::__construct($formvars);
    }
}