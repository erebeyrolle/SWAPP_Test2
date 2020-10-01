<?php

class bPromotion_fournisseur extends BackModule {

    function bPromotion_fournisseur($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pf.promotion_fournisseur_id";
            $sql.=", pf.promotion_id";
            $sql.=", pf.fournisseur_id";
            $sql.=", pf.promotion_fournisseur_inclus";
            $sql.=" FROM (promotion_fournisseur pf)";
            $sql.=" WHERE pf.promotion_fournisseur_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Promotion ID", "hidden", "promotion_id");
        $this->addForm($form);

        $form = new BackForm("Fournisseur", "select", "fournisseur_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT fournisseur_id, fournisseur_nom FROM (fournisseur) ORDER BY fournisseur_nom"));
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "promotion_fournisseur_inclus");
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
        $sql = "SELECT pf.promotion_fournisseur_id";
        $sql.=", pf.promotion_fournisseur_id";
        $sql.=", f.fournisseur_nom";
        $sql.=", IF(pf.promotion_fournisseur_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (promotion_fournisseur pf, fournisseur f)";
        $sql.=" WHERE pf.fournisseur_id = f.fournisseur_id";
        if (!empty($formvars['promotion']))
            $sql.=" AND pf.promotion_id = '" . $formvars['promotion'] . "'";

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