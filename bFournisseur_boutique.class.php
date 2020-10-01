<?php

class bFournisseur_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'fournisseur';

        parent::__construct($formvars);
    }
}