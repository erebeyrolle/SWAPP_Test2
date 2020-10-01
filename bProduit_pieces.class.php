<?php

class bProduit_pieces extends BackModule {

    function bProduit_pieces($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_pieces', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        $form = new BackForm("Produit", "autocomplete", "produit_a_id");
        $form->addAttr('class', 'required');
        $form->addAttr('style', 'width:400px');
        $form->setVar('method', '/produit/custom/autocomplete/?produit_nom_nom=');
        $form->setVar('param', 'produit');
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pa.produit_pieces_id";
        $sql.=", pa.produit_pieces_id";
        $sql.=", pn.produit_nom_nom";
        $sql.=" FROM (produit_pieces pa, produit p, produit_nom pn)";
        $sql.=" WHERE pa.produit_a_id = p.produit_id";
        $sql.=" AND pn.produit_id = p.produit_id";
        $sql.=" AND pn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['produit']))
            $sql.=" AND pa.produit_id = '" . $formvars['produit'] . "'";

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