<?php

class bPromotion_categorie extends BackModule {

    function bPromotion_categorie($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pc.promotion_categorie_id";
            $sql.=", pc.promotion_id";
            $sql.=", pc.categorie_id";
            $sql.=", pc.promotion_categorie_inclus";
            $sql.=" FROM (promotion_categorie pc)";
            $sql.=" WHERE pc.promotion_categorie_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Promotion ID", "hidden", "promotion_id");
        $this->addForm($form);

        $form = new BackForm("Cat&eacute;gorie", "select", "categorie_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(
                array(
                    array("SELECT c.categorie_id, cn.categorie_nom_nom FROM (categorie c,categorie_nom cn) WHERE parent_id IS NULL AND cn.langue_id='" . LANGUE . "' AND c.categorie_id=cn.categorie_id ORDER BY categorie_nom_nom", false),
                    array("SELECT c.categorie_id, cn.categorie_nom_nom FROM categorie c,categorie_nom cn WHERE parent_id = '%ID%' AND c.categorie_id = cn.categorie_id AND cn.langue_id ='" . LANGUE . "' ORDER BY categorie_nom_nom", true),
                )
        );
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "promotion_categorie_inclus");
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
        $sql = "SELECT pc.promotion_categorie_id";
        $sql.=", pc.promotion_categorie_id";
        $sql.=", cn.categorie_nom_nom";
        $sql.=", IF(pc.promotion_categorie_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (promotion_categorie pc, categorie c, categorie_nom cn)";
        $sql.=" WHERE pc.categorie_id = c.categorie_id";
        $sql.=" AND cn.categorie_id = c.categorie_id";
        $sql.=" AND cn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['promotion']))
            $sql.=" AND pc.promotion_id = '" . $formvars['promotion'] . "'";

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