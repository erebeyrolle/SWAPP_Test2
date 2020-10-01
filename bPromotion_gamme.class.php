<?php

class bPromotion_gamme extends BackModule {

    function bPromotion_gamme($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pg.promotion_gamme_id";
            $sql.=", pg.promotion_id";
            $sql.=", pg.gamme_id";
            $sql.=", pg.promotion_gamme_inclus";
            $sql.=" FROM (promotion_gamme pg)";
            $sql.=" WHERE pg.promotion_gamme_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Promotion ID", "hidden", "promotion_id");
        $this->addForm($form);

        $form = new BackForm("Gamme", "select", "gamme_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(
                array(
                    array("SELECT c.gamme_id, cn.gamme_nom_nom FROM (gamme c,gamme_nom cn) WHERE parent_id IS NULL AND cn.langue_id='" . LANGUE . "' AND c.gamme_id=cn.gamme_id ORDER BY gamme_nom_nom", false),
                    array("SELECT c.gamme_id, cn.gamme_nom_nom FROM gamme c,gamme_nom cn WHERE parent_id = '%ID%' AND c.gamme_id = cn.gamme_id AND cn.langue_id ='" . LANGUE . "' ORDER BY gamme_nom_nom", true),
                )
        );
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "promotion_gamme_inclus");
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
        $sql = "SELECT pg.promotion_gamme_id";
        $sql.=", pg.promotion_gamme_id";
        $sql.=", gn.gamme_nom_nom";
        $sql.=", IF(pg.promotion_gamme_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (promotion_gamme pg, gamme g, gamme_nom gn)";
        $sql.=" WHERE pg.gamme_id = g.gamme_id";
        $sql.=" AND g.gamme_id = gn.gamme_id AND gn.langue_id = '" . LANGUE . "'";
        if (!empty($formvars['promotion']))
            $sql.=" AND pg.promotion_id = '" . $formvars['promotion'] . "'";

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