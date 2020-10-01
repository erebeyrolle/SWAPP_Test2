<?php

class bClient_point_historique extends BackModule {

    function bClient_point_historique($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;
    }

    function update($formvars = array(), $table = null, $langue = true) {
        $return = parent::update($formvars, $table, $langue);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $return = parent::create($formvars, $table, $langue);
        return $return;
    }

    function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (client_point_historique cph)";

            $sql.= " WHERE 1";
            $sql.= " AND cph.client_point_historique_id = '" . $formvars['id'] . "'";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("client ID", "hidden", "client_id");
        $this->addForm($form);

        $form = new BackForm("annonceur ID", "hidden", "annonceur_id");
        $this->addForm($form);

        $form = new BackForm('Points', 'text', 'client_point');
        $this->addForm($form);

        $form = new BackForm('date ajout', 'text', 'client_point_date_ajout');
        $this->addForm($form);

        $form = new BackForm('Commande', 'select', 'commande_id');
        $this->addForm($form);

        $form = new BackForm('Client point historique libelle', 'text', 'client_point_historique_libelle');
        $this->addForm($form);



        // $form->addAttr('class', 'required');
        // $form->addOption("", "---");
        // $form->addOptionSQL(array("SELECT cph.client_point_historique_id, cph.client_point_historique_nom_nom FROM client_point_historique cph"));
        // $form->addOptionSQL(array("(SELECT '2', 'Non') UNION (SELECT '1', 'Oui')"));

        //  $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm('# ID', 'text', 'cph.client_point_historique_id');
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);


        $sql = "SELECT cph.client_point_historique_id, cph.client_point_historique_id";
        $sql.= ", cph.client_point_historique_date_ajout";
        $sql.= ", CONCAT('<strong>',cph.client_point_historique_prev, '</strong> > ', cph.client_point_historique_point) as points";
        $sql.= ", cph.client_point_historique_libelle";
        $sql.= " FROM (client_point_historique cph)";

        $sql.= " WHERE 1";
        if (!empty($formvars['client']))
            $sql.=" AND cph.client_id = '" . $formvars['client'] . "'";
        if (!empty($formvars['annonceur']))
            $sql.=" AND cph.client_id = '" . $formvars['annonceur'] . "'";
        $this->request = $sql;
        
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('DATE', 'date');
        $list->setVar('option', array('%d/%m/%Y %H\hi'));
        $this->addLabel($list);

        $list = new BackLabel('POINTS');
        $this->addLabel($list);

        $list = new BackLabel('LIBELLE');
        $this->addLabel($list);



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}