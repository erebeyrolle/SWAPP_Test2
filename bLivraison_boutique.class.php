<?php

class bLivraison_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'livraison';

        parent::__construct($formvars);
    }
}