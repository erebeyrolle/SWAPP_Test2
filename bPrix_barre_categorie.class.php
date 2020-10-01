<?php

class bPrix_barre_categorie extends BackModule {

    function bPrix_barre_categorie($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pbc.prix_barre_categorie_id";
            $sql.=", pbc.prix_barre_id";
            $sql.=", pbc.categorie_id";
            $sql.=", pbc.prix_barre_categorie_inclus";
            $sql.=" FROM (prix_barre_categorie pbc)";
            $sql.=" WHERE pbc.prix_barre_categorie_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Prix barre ID", "hidden", "prix_barre_id");
        $this->addForm($form);

        $form = new BackForm("Cat&eacute;gorie", "select", "categorie_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(
                array(
                    array("SELECT c.categorie_id, cn.categorie_nom_nom FROM (categorie c,categorie_nom cn) WHERE parent_id IS NULL AND cn.langue_id='" . LANGUE . "' AND c.categorie_id=cn.categorie_id ORDER BY categorie_nom_nom", false),
                    array("SELECT c.categorie_id, cn.categorie_nom_nom FROM categorie c,categorie_nom cn WHERE parent_id = '%ID%' AND c.categorie_id = cn.categorie_id AND cn.langue_id ='" . LANGUE . "' ORDER BY categorie_nom_nom", true),
                )
        );
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "prix_barre_categorie_inclus");
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
        $sql = "SELECT pbc.prix_barre_categorie_id";
        $sql.=", pbc.prix_barre_categorie_id";
        $sql.=", cn.categorie_nom_nom";
        $sql.=", IF(pbc.prix_barre_categorie_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (prix_barre_categorie pbc, categorie c, categorie_nom cn)";
        $sql.=" WHERE pbc.categorie_id = c.categorie_id";
        $sql.=" AND cn.categorie_id = c.categorie_id";
        $sql.=" AND cn.langue_id='" . LANGUE . "'";
        if (!empty($formvars['prix_barre']))
            $sql.=" AND pbc.prix_barre_id = '" . $formvars['prix_barre'] . "'";

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