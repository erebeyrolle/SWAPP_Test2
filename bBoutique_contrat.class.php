<?php

class bBoutique_contrat extends BackModule {

    function bBoutique_contrat($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['DEL'] = false;
    }

    function update($formvars = array(), $table = null, $langue = true)
    {
        $boutique = Boutique::getInstance()->getConfig($formvars['boutique_id']);
        $formvars['boutique_contrat_point'] = $formvars['boutique_contrat_montant'] * $boutique['boutique_euro_point'];
        $return = parent::update($formvars,$table,$langue);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true)
    {
        $boutique = Boutique::getInstance()->getConfig($formvars['boutique_id']);
        $formvars['boutique_contrat_point'] = $formvars['boutique_contrat_montant'] * $boutique['boutique_euro_point'];
//        Boutique::getInstance()->updatePoints($formvars);
        $return = parent::create($formvars,$table,$langue);
        return $return;
    }

    function delete($formvars = array())
    {
        // Suppression de l'historique point

        return parent::delete($formvars);
    }

    function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (boutique_contrat bc)";
            $sql.= " LEFT JOIN boutique_config bco ON bc.boutique_id = bco.boutique_id";
            $sql.= " WHERE 1";
            $sql.= " AND bc.boutique_contrat_id = '" . $formvars['id'] . "' LIMIT 1";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array('SELECT boutique_id, boutique_nom FROM boutique'));
        $this->addForm($form);


        $form = new BackForm('R&eacute;f&eacute;rence', 'text', 'boutique_contrat_reference');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Contrat", "select", "boutique_contrat_type_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array('SELECT id, type FROM boutique_contrat_type'));
        $this->addForm($form);

        $form = new BackForm('Annulé ?', 'select', 'boutique_contrat_annule');
        $form->addOption('0', 'Non');
        $form->addOption('1', 'Oui');
        $this->addForm($form);

        $form = new BackForm('Date de création', 'date', 'boutique_contrat_date');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Date de fin', 'date', 'boutique_contrat_date_fin');
//        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Montant (en &euro;)', 'text', 'boutique_contrat_montant');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Historique des points", "group");
        $form->addGroupOpts('class', 'client_point');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm('# ID', 'text', 'bc.boutique_contrat_id');
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);


        $sql = "SELECT bc.boutique_contrat_id, bc.boutique_contrat_id";
        if (empty($formvars['boutique'])){
            $sql.=", CONCAT('<a href=\"/back/html/boutique/form/', bc.boutique_id, '/#field_boutique_contrat\">', '#', b.boutique_id , ' - ', b.boutique_nom, '</a>') as boutique_nom";
        }
        $sql.= ", bc.boutique_contrat_reference";
        $sql.= ", bct.type";
        $sql.= ", bc.boutique_contrat_montant";
        $sql.= ", IF(bc.boutique_contrat_annule = 1, 'OUI', 'NON')";
        $sql.= ", bc.boutique_contrat_date";
        $sql.= ", IF(bc.boutique_contrat_date_fin IS NULL, '0000-00-00', bc.boutique_contrat_date_fin) as boutique_contrat_date_fin";
//        $sql.= ", bc.boutique_contrat_point";

        $sql.= " FROM (boutique_contrat bc)";
        $sql.=" LEFT JOIN boutique b ON (b.boutique_id = bc.boutique_id)";
		$sql.=" LEFT JOIN boutique_contrat_type bct ON (bct.id = bc.boutique_contrat_type_id)";

        $sql.= " WHERE 1";
        if (!empty($formvars['boutique']))
            $sql.=" AND bc.boutique_id = '" . $formvars['boutique'] . "'";
        $this->request = $sql;


        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        if (empty($formvars['boutique'])){
            $label = new BackLabel("BOUTIQUE");
            $this->addLabel($label);
        }

        $list = new BackLabel('REFERENCE');
        $this->addLabel($list);

		$list = new BackLabel('TYPE CONTRAT');
        $this->addLabel($list);

        $list = new BackLabel('MONTANT');
        $this->addLabel($list);

        $list = new BackLabel('ANNULE');
        $this->addLabel($list);

        $list = new BackLabel('DATE', 'date');
        $list->setVar('option', array("%d/%m/%Y"));
        $this->addLabel($list);

        $list = new BackLabel('DATE FIN', 'date');
        $list->setVar('option', array("%d/%m/%Y"));
        $this->addLabel($list);

/*
        $list = new BackLabel('BOUTIQUE CONTRAT POINT');
        $this->addLabel($list);*/



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}