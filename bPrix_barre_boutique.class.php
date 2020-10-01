<?php

class bPrix_barre_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'prix_barre';

        parent::__construct($formvars);
    }
}