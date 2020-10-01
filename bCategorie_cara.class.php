<?php

class bCategorie_cara extends BackModule {

    function bCategorie_cara($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('categorie_not_cara', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("categorie ID", "hidden", "categorie_id");
        $this->addForm($form);

        $form = new BackForm("caracteristique", "select", "caracteristique_id");
        $form->addOptionSQL(array("SELECT c.caracteristique_id, cn.caracteristique_nom_nom FROM (caracteristique c, caracteristique_nom cn) WHERE cn.langue_id='" . LANGUE . "' AND c.caracteristique_id=cn.caracteristique_id ORDER BY caracteristique_nom_nom"));
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT cnc.categorie_not_cara_id";
        $sql.=", cnc.categorie_not_cara_id";
        $sql.=", cn.caracteristique_nom_nom";
        $sql.=" FROM (categorie_not_cara cnc, caracteristique_nom cn)";
        $sql.=" WHERE cnc.caracteristique_id=cn.caracteristique_id";
        $sql.=" AND cn.langue_id='" . LANGUE . "'";
        $sql.=" AND cnc.categorie_id = '" . $formvars['categorie'] . "'";

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