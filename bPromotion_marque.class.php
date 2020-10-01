<?php

class bPromotion_marque extends BackModule {

    function bPromotion_marque($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pm.promotion_marque_id";
            $sql.=", pm.promotion_id";
            $sql.=", pm.marque_id";
            $sql.=", pm.promotion_marque_inclus";
            $sql.=" FROM (promotion_marque pm)";
            $sql.=" WHERE pm.promotion_marque_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Promotion ID", "hidden", "promotion_id");
        $this->addForm($form);

        $form = new BackForm("Marque", "select", "marque_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT marque_id, marque_nom FROM (marque) ORDER BY marque_nom"));
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "promotion_marque_inclus");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pm.promotion_marque_id";
        $sql.=", pm.promotion_marque_id";
        $sql.=", m.marque_nom";
        $sql.=", IF(pm.promotion_marque_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (promotion_marque pm, marque m)";
        $sql.=" WHERE pm.marque_id = m.marque_id";
        if (!empty($formvars['promotion']))
            $sql.=" AND pm.promotion_id = '" . $formvars['promotion'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("INCLUS");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>