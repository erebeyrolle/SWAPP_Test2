<?php

class bProduit_categorie extends BackModule {

    function bProduit_categorie($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_categorie', $formvars['id']);

        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        $form = new BackForm("Catégorie", "select", "categorie_id");
        $form->addAttr('class', 'required');
        $form->addOption("NULL", "---");
        $form->addCategorieRecursive("","","","1");
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pc.produit_categorie_id";
        $sql.=", pc.produit_categorie_id";
        $sql.=", cn.categorie_nom_nom";
        $sql.=" FROM (produit_categorie pc, categorie_nom cn)";
        $sql.=" WHERE pc.categorie_id=cn.categorie_id";
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