<?php

class bProduit_option_valeur extends BackModule {

    function bProduit_option_valeur($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        return parent::create_multi($formvars, $table, $langue);
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('produit_option_valeur', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit Option ID", "hidden", "produit_option_id");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "produit_option_valeur_nom");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "produit_option_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pov.produit_option_valeur_id, pov.produit_option_valeur_id, povn.produit_option_valeur_nom, produit_option_rang";
        $sql.=" FROM (produit_option_valeur pov)";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn ON (povn.produit_option_valeur_id = pov.produit_option_valeur_id AND langue_id ='" . LANGUE . "')";
        $sql.=" WHERE 1";
        if (!empty($formvars['produit_option']))
            $sql.=" AND pov.produit_option_id = '" . $formvars['produit_option'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>