<?php

class bPrix_barre_produit extends BackModule {

    function bPrix_barre_produit($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pbp.prix_barre_produit_id";
            $sql.=", pbp.prix_barre_id";
            $sql.=", pbp.produit_id";
            $sql.=", pbp.prix_barre_produit_inclus";
            $sql.=" FROM (prix_barre_produit pbp)";
            $sql.=" WHERE pbp.prix_barre_produit_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Prix barre ID", "hidden", "prix_barre_id");
        $this->addForm($form);

        $form = new BackForm("Produit", "autocomplete", "produit_id");
        $form->addAttr('class', 'required');
        $form->addAttr('style', 'width:400px');
        $form->setVar('method', '/produit/custom/autocomplete/?produit_nom_nom=');
        $form->setVar('param', 'produit');
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "prix_barre_produit_inclus");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pbp.prix_barre_produit_id";
        $sql.=", pbp.prix_barre_produit_id";
        $sql.=", p.produit_ref";
        $sql.=", p.produit_ref_frs";
        $sql.=", pn.produit_nom_nom";
        $sql.=", IF(pbp.prix_barre_produit_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (prix_barre_produit pbp, produit p, produit_nom pn)";
        $sql.=" WHERE pbp.produit_id = p.produit_id";
        $sql.=" AND pn.produit_id = p.produit_id";
        $sql.=" AND pn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['prix_barre']))
            $sql.=" AND pbp.prix_barre_id = '" . $formvars['prix_barre'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("REF");
        $this->addLabel($label);

        $label = new BackLabel("REF FRS");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("INCLUS");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>