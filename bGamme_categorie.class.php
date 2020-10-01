<?php

class bGamme_categorie extends BackModule {

    function bGamme_categorie($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('gamme_categorie', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Gamme ID", "hidden", "gamme_id");
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
        $sql = "SELECT gc.gamme_categorie_id";
        $sql.=", gc.gamme_categorie_id";
        $sql.=", cn.categorie_nom_nom";
        $sql.=" FROM (gamme_categorie gc, categorie_nom cn)";
        $sql.=" WHERE gc.categorie_id=cn.categorie_id";
        $sql.=" AND cn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['gamme']))
            $sql.=" AND gc.gamme_id = '" . $formvars['gamme'] . "'";

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