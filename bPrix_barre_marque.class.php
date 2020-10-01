<?php

class bPrix_barre_marque extends BackModule {

    function bPrix_barre_marque($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pbm.prix_barre_marque_id";
            $sql.=", pbm.prix_barre_id";
            $sql.=", pbm.marque_id";
            $sql.=", pbm.prix_barre_marque_inclus";
            $sql.=" FROM (prix_barre_marque pbm)";
            $sql.=" WHERE pbm.prix_barre_marque_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Prix barre ID", "hidden", "prix_barre_id");
        $this->addForm($form);

        $form = new BackForm("Marque", "select", "marque_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT marque_id, marque_nom FROM (marque) ORDER BY marque_nom"));
        $this->addForm($form);


        $form = new BackForm("Inclus ?", "select", "prix_barre_marque_inclus");
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
        $sql = "SELECT pbm.prix_barre_marque_id";
        $sql.=", pbm.prix_barre_marque_id";
        $sql.=", m.marque_nom";
        $sql.=", IF(pbm.prix_barre_marque_inclus=1,CONCAT('OUI'),CONCAT('NON'))";
        $sql.=" FROM (prix_barre_marque pbm, marque m)";
        $sql.=" WHERE pbm.marque_id = m.marque_id";
        if (!empty($formvars['prix_barre']))
            $sql.=" AND pbm.prix_barre_id = '" . $formvars['prix_barre'] . "'";

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