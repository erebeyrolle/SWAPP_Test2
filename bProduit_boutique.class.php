<?php

class bProduit_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'produit';

        parent::__construct($formvars);
    }
}