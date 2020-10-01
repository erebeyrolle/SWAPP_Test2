<?php

class bPaiement_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'paiement';

        parent::__construct($formvars);
    }
}