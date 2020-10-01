<?php

class bPrix_barre_export extends BackModule {

    // applique les prix barres
    function apply_prix($formvars = array()) {

        $prixBarre = new Prix_Barre;
        $prixBarre->apply((!empty($formvars['id'])) ? $formvars['id'] : '');

        echo 'Les prix barrés ont été appliqués avec succès !';
    }

}
?>