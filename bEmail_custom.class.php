<?php

class bEmail_custom extends BackModule {

    function __construct($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
        $GLOBALS['displayMceEditor'] = true;
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
            $sql = "SELECT * FROM (email_custom ec)";
            $sql.= " WHERE 1";
            $sql.= " AND ec.email_custom_id = '" . $formvars['id'] . "' LIMIT 1";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addAttr('class', 'required');
        $form->addOption('NULL', 'Default');
        $form->addOptionSQL(array('SELECT boutique_id, boutique_nom FROM boutique'));
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "email_custom_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Code", "text", "email_custom_code");
        $form->addAttr('class', 'required');
        if (!empty($formvars['id'])) {
            $form->addAttr('readonly', 'readonly');
        }
        $this->addForm($form);

        $form = new BackForm("Description", "textarea", "email_custom_description");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "email_custom_statut");
        $form->addAttr('class', 'required');
        $form->addOption('1', 'Activé');
        $form->addOption('0', 'Desactivé');
        $this->addForm($form);

        $form = new BackForm("Email", "group");
        $form->addGroupOpts('class', 'boutique_email');
        $this->addForm($form);

//        $form = new BackForm("Sujet", "text", "email_custom_nom_sujet");
//        $form->addAttr('class', 'required');
//        $form->setVar('translate', true);
//        $this->addForm($form);
//
//        $form = new BackForm("Corps", "tinymce", "email_custom_nom_content");
//        $form->addAttr('class', 'required');
//        $form->setVar('translate', true);
//        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE

        $sql = "SELECT ec.email_custom_id, ec.email_custom_id";
        $sql.= ", ec.email_custom_code";
        $sql.= ", ec.email_custom_nom";

        $sql.= " FROM (email_custom ec)";

        $sql.= " WHERE 1";
        if (!empty($formvars['boutique']))
            $sql.=" AND ec.boutique_id = '" . $formvars['boutique'] . "'";
        $this->request = $sql;

        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);


        $list = new BackLabel('CODE');
        $this->addLabel($list);

        $list = new BackLabel('NOM');
        $this->addLabel($list);



        /*
                $list = new BackLabel('BOUTIQUE CONTRAT POINT');
                $this->addLabel($list);*/



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}