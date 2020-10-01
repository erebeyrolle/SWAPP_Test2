<?php

class bClub_fidelite extends BackModule {

    function bClub_fidelite($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array()) {
        
        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['club_fidelite_montant'] = $taxe->getPrixHt($formvars['club_fidelite_montant'], $formvars['taxe_id']);

        return parent::update_multi($formvars);
    }

    function create($formvars = array()) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['club_fidelite_montant'] = $taxe->getPrixHt($formvars['club_fidelite_montant'], $formvars['taxe_id']);

        return parent::create_multi($formvars);
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Club Fidelit&eacute;</a></span>";
        else {
            $sql = "SELECT club_fidelite_nom_nom as data FROM club_fidelite_nom WHERE club_fidelite_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Club Fidelit&eacute; : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=", ROUND(club_fidelite_montant * (1 + taxe_taux),2) as club_fidelite_montant";
            $sql.=" FROM (club_fidelite p,club_fidelite_nom pn, taxe t)";
            $sql.=" WHERE p.club_fidelite_id = '" . $formvars['id'] . "'";
            $sql.=" AND pn.club_fidelite_id = p.club_fidelite_id";
            $sql.=" AND t.taxe_id = p.taxe_id";
            $sql.=" AND pn.langue_id = '" . LANGUE . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Nom", "text", "club_fidelite_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Pourcentage Revers&eacute;", "text", "club_fidelite_pourcent");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Ce pourcentage sera revers&eacute; au membre sous la forme d'un bon d'achat");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Montant TTC", "text", "club_fidelite_montant");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Montant minimim d'achat de la commande par le membre pour le Gain");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe)"));
        $this->addForm($form);

        $form = new BackForm("Validit&eacute; (en jours)", "text", "club_fidelite_nbjour");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Nombre de jour de validit&eacute; du bon d'achat cree");
        $form->addAttr('size', 20);
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT p.club_fidelite_id, p.club_fidelite_id, pn.club_fidelite_nom_nom, CONCAT(club_fidelite_pourcent,'%'), ROUND((club_fidelite_montant * (1 + taxe_taux)),2), club_fidelite_nbjour";
        $sql.=" FROM (club_fidelite p, club_fidelite_nom pn, taxe t, langue l)";
        $sql.=" WHERE p.taxe_id = t.taxe_id";
        $sql.=" AND pn.club_fidelite_id=p.club_fidelite_id";
        $sql.=" AND pn.langue_id='" . LANGUE . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("POURCENT");
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
?>