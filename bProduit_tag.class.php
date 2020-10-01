<?php

class bProduit_tag extends BackModule {

    function bProduit_tag($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_tag', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire        
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);


        $form = new BackForm("Tag", "autocomplete", "tag_id");
        $form->addAttr('class', 'required');
        $form->addAttr('style', 'width:400px');
        $form->setVar('method', '/tag/custom/autocomplete/?tag_nom_nom=');
        $form->setVar('param', 'tag');
     
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pt.produit_tag_id";
        $sql.=", pt.tag_id";
        $sql.=", tn.tag_nom_nom";
        $sql.=" FROM (produit_tag pt, tag_nom tn)";
        $sql.=" WHERE pt.tag_id=tn.tag_id";
        $sql.=" AND tn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['produit']))
            $sql.=" AND pt.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("TAG");
        $this->addLabel($label);
        
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>