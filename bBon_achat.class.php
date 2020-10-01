<?php

class bBon_achat extends BackModule {

    function bBon_achat($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['bon_achat_prix'] = $taxe->getPrixHt($formvars['bon_achat_prix'], $formvars['taxe_id']);
        $formvars['bon_achat_prix_min'] = $taxe->getPrixHt($formvars['bon_achat_prix_min'], $formvars['taxe_id']);

        return parent::update($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        
        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['bon_achat_prix'] = $taxe->getPrixHt($formvars['bon_achat_prix'], $formvars['taxe_id']);
        $formvars['bon_achat_prix_min'] = $taxe->getPrixHt($formvars['bon_achat_prix_min'], $formvars['taxe_id']);

        return parent::create($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=", ROUND((ba.bon_achat_prix * (1 + taxe_taux)),2) as bon_achat_prix";
            $sql.=", ROUND((ba.bon_achat_prix_min * (1 + taxe_taux)),2) as bon_achat_prix_min";
            $sql.=" FROM (bon_achat ba, taxe t)";
            $sql.=" WHERE bon_achat_id='" . $formvars['id'] . "'";
            $sql.=" AND t.taxe_id = ba.taxe_id";

            $this->request = $sql;
        } else {
            $_GET['bon_achat_date_limit'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1));
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Client ID", "hidden", "client_id");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "bon_achat_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Valeur TTC", "text", "bon_achat_prix");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Valeur min panier TTC", "text", "bon_achat_prix_min");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe) ORDER BY taxe_rang"));
        $this->addForm($form);

        $form = new BackForm("Date limite", "date", "bon_achat_date_limit");
        $this->addForm($form);

        $form = new BackForm("Etat", "select", "bon_achat_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT bon_achat_statut_id, bon_achat_statut_nom FROM bon_achat_statut"));
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT ba.bon_achat_id";
        $sql.=", ba.bon_achat_id";
        $sql.=", ba.bon_achat_nom";
        $sql.=", ROUND((ba.bon_achat_prix * (1 + taxe_taux)),2) as bon_achat_prix";
        $sql.=", ROUND((ba.bon_achat_prix_min * (1 + taxe_taux)),2) as bon_achat_prix_min ";
        $sql.=", ba.bon_achat_date_limit";
        $sql.=", bas.bon_achat_statut_nom";
        $sql.=" FROM (bon_achat ba, bon_achat_statut bas, taxe t)";
        $sql.=" WHERE ba.bon_achat_statut_id = bas.bon_achat_statut_id";
        $sql.=" AND t.taxe_id = ba.taxe_id";

        if (!empty($formvars['client']))
            $sql.=" AND ba.client_id = '" . $formvars['client'] . "'";

        if (!empty($formvars['annonceur']))
            $sql.=" AND ba.client_id = '" . $formvars['annonceur'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("TITRE");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR TTC", "devise");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR MIN PANIER", "devise");
        $this->addLabel($label);

        $label = new BackLabel("VALIDITE",'date');
        $label->setVar('option', array("%d/%m/%Y"));
        $this->addLabel($label);

        $label = new BackLabel("ETAT");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>