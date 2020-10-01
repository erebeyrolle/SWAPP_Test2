<?php

class bCategorie_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'categorie';

        parent::__construct($formvars);
    }
}