<?php

class bEtiquette_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'etiquette';

        parent::__construct($formvars);
    }
}