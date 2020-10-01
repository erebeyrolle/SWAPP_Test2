<?php

class bParrainage extends BackModule {

    function bParrainage($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        
        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['parrainage_gain'] = $taxe->getPrixHt($formvars['parrainage_gain'], $formvars['taxe_id']);
        $formvars['parrainage_gain_filleul'] = $taxe->getPrixHt($formvars['parrainage_gain_filleul'], $formvars['taxe_id']);
        $formvars['parrainage_montant'] = $taxe->getPrixHt($formvars['parrainage_montant'], $formvars['taxe_id']);

        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['parrainage_gain'] = $taxe->getPrixHt($formvars['parrainage_gain'], $formvars['taxe_id']);
        $formvars['parrainage_gain_filleul'] = $taxe->getPrixHt($formvars['parrainage_gain_filleul'], $formvars['taxe_id']);
        $formvars['parrainage_montant'] = $taxe->getPrixHt($formvars['parrainage_montant'], $formvars['taxe_id']);

        return parent::create_multi($formvars, $table, $langue);
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Parrainage</a></span>";
        else {
            $sql = "SELECT parrainage_nom_nom as data FROM parrainage_nom WHERE parrainage_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Parrainage : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=", ROUND(parrainage_gain * (1 + taxe_taux),2) as parrainage_gain";
            $sql.=", ROUND(parrainage_gain_filleul * (1 + taxe_taux),2) as parrainage_gain_filleul";
            $sql.=", ROUND(parrainage_montant * (1 + taxe_taux),2) as parrainage_montant";
            $sql.=" FROM (parrainage p,parrainage_nom pn, taxe t)";
            $sql.=" WHERE p.parrainage_id = '" . $formvars['id'] . "'";
            $sql.=" AND pn.parrainage_id = p.parrainage_id";
            $sql.=" AND t.taxe_id = p.taxe_id";
            $sql.=" AND pn.langue_id = '" . LANGUE . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        
        $form = new BackForm("Nom", "text", "parrainage_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Utilis&eacute; pour le nom du bon d'achat");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Gain Parrain TTC", "text", "parrainage_gain");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Ce montant sera revers&eacute; au parrain sous la forme d'un bon d'achat");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Gain Filleul TTC", "text", "parrainage_gain_filleul");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Ce montant sera revers&eacute; au filleul sous la forme d'un bon d'achat");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Montant TTC", "text", "parrainage_montant");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Montant minimim d'achat de la 1ere commande par le filleul");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe)"));
        $this->addForm($form);

        $form = new BackForm("Validit&eacute; (en jours)", "text", "parrainage_nbjour");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Nombre de jour de validit&eacute; des bons d'achats crees");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'parrainage_boutique');
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT p.parrainage_id, p.parrainage_id, pn.parrainage_nom_nom, ROUND((parrainage_gain * (1 + taxe_taux)),2), ROUND((parrainage_gain_filleul * (1 + taxe_taux)),2),ROUND((parrainage_montant * (1 + taxe_taux)),2), parrainage_nbjour";
        $sql.=" FROM (parrainage p, parrainage_nom pn, taxe t, langue l)";
        $sql.=" WHERE p.taxe_id = t.taxe_id";
        $sql.=" AND pn.parrainage_id=p.parrainage_id";
        $sql.=" AND pn.langue_id='" . LANGUE . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("GAIN Parrain TTC", "devise");
        $this->addLabel($label);

        $label = new BackLabel("GAIN Filleul TTC", "devise");
        $this->addLabel($label);

        $label = new BackLabel("MONTANT TTC", "devise");
        $this->addLabel($label);

        $label = new BackLabel("VALIDITE (J)");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}