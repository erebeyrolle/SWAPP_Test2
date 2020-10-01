<?php

class bGamme_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'gamme';

        parent::__construct($formvars);
    }
}