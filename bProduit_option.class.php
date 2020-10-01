<?php

class bProduit_option extends BackModule {

    function bProduit_option($formvars = array()) {
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

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle option</a></span>";
        else {
            $sql = "SELECT produit_option_nom as data FROM produit_option_nom WHERE produit_option_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de l'option  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('produit_option', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Nom", "text", "produit_option_nom");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Valeurs", "group");
        $form->addGroupOpts('class', 'produit_option_valeur');
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT po.produit_option_id, po.produit_option_id, pon.produit_option_nom";
        $sql.=", COUNT(DISTINCT pov.produit_option_valeur_id)";
        $sql.=" FROM (produit_option po)";
        $sql.=" LEFT JOIN produit_option_nom pon ON (pon.produit_option_id = po.produit_option_id AND pon.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN produit_option_valeur pov ON (pov.produit_option_id = po.produit_option_id)";
        $sql.=" WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("VALEURS", 'listselect');
        $label->setVar('option', array("group" => "produit_option_valeur"));
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>