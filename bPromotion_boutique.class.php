<?php

class bPromotion_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'promotion';

        parent::__construct($formvars);
    }
}