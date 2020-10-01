<?php

class bProduit_gamme extends BackModule {

    function bProduit_gamme($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_gamme', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        $form = new BackForm("Univers", "select", "gamme_id");
        $form->addOptionSQL(
                array(
                    array("SELECT c.gamme_id, cn.gamme_nom_nom FROM (gamme c,gamme_nom cn) WHERE parent_id IS NULL AND cn.langue_id='" . LANGUE . "' AND c.gamme_id=cn.gamme_id ORDER BY gamme_nom_nom", false),
                    array("SELECT c.gamme_id, cn.gamme_nom_nom FROM gamme c,gamme_nom cn WHERE parent_id = '%ID%' AND c.gamme_id = cn.gamme_id AND cn.langue_id ='" . LANGUE . "' ORDER BY gamme_nom_nom", true),
                )
        );
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pc.produit_gamme_id";
        $sql.=", pc.produit_gamme_id";
        $sql.=", cn.gamme_nom_nom";
        $sql.=" FROM (produit_gamme pc, gamme_nom cn)";
        $sql.=" WHERE pc.gamme_id=cn.gamme_id";
        $sql.=" AND cn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['produit']))
            $sql.=" AND pc.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>