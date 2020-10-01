<?php

class bPartenaire_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'partenaire';

        parent::__construct($formvars);
    }
}