<?php

class bBoutique_email extends BackModule {

    function __construct($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true)
    {
        $return = parent::update($formvars,$table,$langue);

        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true)
    {
        $return = parent::create($formvars,$table,$langue);

        return $return;
    }

    function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (custom_email ce)";
            $sql.= " WHERE 1";
            $sql.= " AND ce.custom_email_id = '" . $formvars['id'] . "' LIMIT 1";
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

        $form = new BackForm('Date', 'date', 'boutique_contrat_date');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Montant (en &euro;)', 'text', 'boutique_contrat_montant');
        $form->addAttr('class', 'required');
        $this->addForm($form);
//
//        $form = new BackForm('Points alloués', 'text', 'boutique_contrat_point');
//        $this->addForm($form);
//
//        $form = new BackForm('boutique_euro_point', 'text', 'boutique_euro_point');
//        $this->addForm($form);



        // $form->addAttr('class', 'required');
        // $form->addOption("", "---");
        // $form->addOptionSQL(array("SELECT bc.boutique_contrat_id, bc.boutique_contrat_nom_nom FROM boutique_contrat bc"));
        // $form->addOptionSQL(array("(SELECT '2', 'Non') UNION (SELECT '1', 'Oui')"));

        //  $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE


        $sql = "SELECT ce.custom_email_id, ce.custom_email_id";
        $sql.= ", ce.custom_email_sujet";
        $sql.= ", ce.custom_email_contenu";

        $sql.= " FROM (boutique_contrat ce)";

        $sql.= " WHERE 1";
        if (!empty($formvars['custom_email']))
            $sql.=" AND ce.custom_email_id = '" . $formvars['custom_email'] . "'";
        $this->request = $sql;


        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('SUJET');
        $this->addLabel($list);

        $list = new BackLabel('CONTENU');
        $this->addLabel($list);


        /*
                $list = new BackLabel('BOUTIQUE CONTRAT POINT');
                $this->addLabel($list);*/



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}