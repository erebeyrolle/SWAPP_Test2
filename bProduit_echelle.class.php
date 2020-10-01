<?php

class bProduit_echelle extends BackModule {

    function bProduit_echelle($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_echelle', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        $form = new BackForm("Echelle", "select", "echelle_id");
        $form->addOptionSQL(array("SELECT e.echelle_id, e.echelle_valeur FROM (echelle e) ORDER BY echelle_valeur"));
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pe.produit_echelle_id";
        $sql.=", pe.produit_echelle_id";
        $sql.=", e.echelle_valeur";
        $sql.=" FROM (produit_echelle pe)";
        $sql.=" LEFT JOIN echelle e ON(pe.echelle_id = e.echelle_id)";
        $sql.=" WHERE 1";
        if (!empty($formvars['produit']))
            $sql.=" AND pe.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>