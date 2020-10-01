<?php

class bAds_boutique extends BackModuleBoutique {

    function __construct($formvars = array()) {
        $this->table = 'ads';

        parent::__construct($formvars);
    }
}