<?php

class bProduit_cara extends BackModule {

    function bProduit_cara($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_cara', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        $form = new BackForm("Caractéristique", "select", "caracteristique_id");
        $form->addOptionSQL(array("SELECT c.caracteristique_id, cn.caracteristique_nom_nom FROM (caracteristique c) LEFT JOIN caracteristique_nom cn ON(c.caracteristique_id = cn.caracteristique_id) ORDER BY caracteristique_nom_nom"));
        $this->addForm($form);

        $form = new BackForm("Valeur", "text", "produit_cara_valeur");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        /*
        $form = new BackForm("Visible", "select", "produit_cara_visible");
        $form->addAttr('class', 'required');
        $form->addOption("1", "Oui");
        $form->addOption("0", "Non");
        */

        // $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pc.produit_cara_id";
        $sql.=", pc.produit_cara_id";
        $sql.=", cn.caracteristique_nom_nom";
        $sql.=", pc.produit_cara_valeur";
        // $sql.=", IF(pc.produit_cara_visible = 1, 'Oui', 'Non')";
        $sql.=" FROM (produit_cara pc)";
        $sql.=" LEFT JOIN caracteristique_nom cn ON(pc.caracteristique_id = cn.caracteristique_id)";
        $sql.=" WHERE 1";
        if (!empty($formvars['produit']))
            $sql.=" AND pc.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR");
        $this->addLabel($label);

        /*
        $label = new BackLabel("VISIBLE");
        $this->addLabel($label);
        */
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>