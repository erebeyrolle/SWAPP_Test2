<?php

class bEdition_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'edition';

        parent::__construct($formvars);
    }
}