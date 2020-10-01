<?php

class bPromotion_fdp extends BackModule {

    function bPromotion_fdp($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {

            $this->request = getTable('promotion_fdp', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Promotion ID", "hidden", "promotion_id");
        $this->addForm($form);

        $form = new BackForm("Zone", "select", "zone_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL("SELECT z.zone_id, zn.zone_nom_nom FROM (zone z,zone_nom zn) WHERE zn.langue_id='" . LANGUE . "' AND z.zone_id=zn.zone_id ORDER BY zone_nom_nom");
        $this->addForm($form);

        $form = new BackForm("Livraison", "select", "livraison_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL("SELECT l.livraison_id, ln.livraison_nom_nom FROM (livraison l,livraison_nom ln) WHERE ln.langue_id='" . LANGUE . "' AND l.livraison_id = ln.livraison_id ORDER BY livraison_nom_nom");
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pfp.promotion_fdp_id";
        $sql.=", pfp.promotion_fdp_id";
        $sql.=", zn.zone_nom_nom";
        $sql.=", ln.livraison_nom_nom";
        $sql.=" FROM (promotion_fdp pfp)";
        $sql.= "LEFT JOIN zone_nom zn ON(zn.zone_id = pfp.zone_id AND zn.langue_id='" . LANGUE . "')";
        $sql.= "LEFT JOIN livraison_nom ln ON(ln.livraison_id = pfp.livraison_id AND ln.langue_id='" . LANGUE . "')";
        $sql.=" WHERE 1";
        if (!empty($formvars['promotion']))
            $sql.=" AND pfp.promotion_id = '" . $formvars['promotion'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("ZONE");
        $this->addLabel($label);

        $label = new BackLabel("LIVRAISON");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>