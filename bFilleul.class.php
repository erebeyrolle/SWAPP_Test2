<?php

class bFilleul extends BackModule {

    function bFilleul($formvars = array()) {
        parent::BackModule($formvars);

        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT c.client_id";
        $sql.=", c.client_id";
        $sql.=", c.client_nom";
        $sql.=", c.client_prenom";
        $sql.=", c.client_email";
        $sql.=" FROM (client c)";
        $sql.=" WHERE 1";
        if (!empty($formvars['client'])) {
            $sql.=" AND c.parrain_id = '" . $formvars['client'] . "'";
        }
        if (!empty($formvars['annonceur'])) {
            $sql.=" AND c.parent_id = '" . $formvars['annonceur'] . "'";
        }

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID CLIENT");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("PRENOM");
        $this->addLabel($label);

        $label = new BackLabel("EMAIL");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
