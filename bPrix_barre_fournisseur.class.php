<?php

class bPrix_barre_fournisseur extends BackModule {

    function bPrix_barre_fournisseur($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pbf.prix_barre_fournisseur_id";
            $sql.=", pbf.prix_barre_id";
            $sql.=", pbf.fournisseur_id";
            $sql.=", pbf.prix_barre_fournisseur_inclus";
            $sql.=" FROM (prix_barre_fournisseur pbf)";
            $sql.=" WHERE pbf.prix_barre_fournisseur_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Prix barre ID", "hidden", "prix_barre_id");
        $this->addForm($form);

        $form = new BackForm("Fournisseur", "select", "fournisseur_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT fournisseur_id, fournisseur_nom FROM (fournisseur) ORDER BY fournisseur_nom"));
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "prix_barre_fournisseur_inclus");
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
        $sql = "SELECT pbf.prix_barre_fournisseur_id";
        $sql.=", pbf.prix_barre_fournisseur_id";
        $sql.=", f.fournisseur_nom";
        $sql.=", IF(pbf.prix_barre_fournisseur_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (prix_barre_fournisseur pbf, fournisseur f)";
        $sql.=" WHERE pbf.fournisseur_id = f.fournisseur_id";
        if (!empty($formvars['prix_barre']))
            $sql.=" AND pbf.prix_barre_id = '" . $formvars['prix_barre'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
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