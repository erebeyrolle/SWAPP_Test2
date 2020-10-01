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

        $newletterAdmin = new NewsletterAdmin();


        $email = $this->sql->getOne("SELECT * FROM email_custom WHERE email_custom_id = {$formvars['email_custom_id']}");

        $row = [
            'langue_id' => $formvars['langue_id'],
            'newsletter_nom' => $email['email_nom'],
            'email_config_id' => '',
            'newsletter_sujet' => $formvars['email_custom_nom_sujet'],
            'newsletter_contenu' => '',
            'newsletter_contenu_html' => $formvars['email_custom_nom_content']
        ];

        $boutiques = Boutique::getInstance()->get();

        foreach ($boutiques as $boutique) {
            $row['boutique_id'] = $boutique['boutique_id'];
            error_log($boutique['boutique_id']);
            $newletterAdmin->add($row);
        }

        return $return;
    }

    function Form($formvars = array()) {

        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (email_custom_nom cen)";
            $sql.= " WHERE 1";
            $sql.= " AND cen.email_custom_nom_id = '" . $formvars['id'] . "' LIMIT 1";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("E-mail", "select", "email_custom_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array('SELECT email_custom_id, email_custom_nom FROM email_custom'));
        $this->addForm($form);

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addAttr('class', 'required');
        $form->addOption('NULL', 'Default');
        $form->addOptionSQL(array('SELECT boutique_id, boutique_nom FROM boutique'));
        $this->addForm($form);

        $form = new BackForm("Sujet", "text", "email_custom_nom_sujet");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Corps", "tinymce", "email_custom_nom_content");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("EMAIL CUSTOM ID", "hidden", "email_custom_id");
        $this->addForm($form);

        $form = new BackForm("Tags disponibles", "content", null);
        $operationemail = new CustomEmail();
        $form->setVar('content', $operationemail->listTags());
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        $this->groupBy = 'email_custom_nom_id';
        // RECHERCHE

        $sql = "SELECT ecn.email_custom_nom_id, ecn.email_custom_nom_id";
        $sql.= ", ecn.email_custom_nom_sujet";
        if (!empty($formvars['email_custom'])) {
            $sql.= ", IF(b.boutique_id IS NULL, 'Défaut' ,b.boutique_nom)";
        }
        if (!empty($formvars['boutique'])) {
            $sql .= ", ec.email_custom_nom";
        }
        $sql.= " FROM (email_custom_nom ecn)";
        if (!empty($formvars['email_custom'])) {
            $sql.= " LEFT JOIN boutique b ON b.boutique_id = ecn.boutique_id";
        }
        if (!empty($formvars['boutique'])) {
            $sql.= " LEFT JOIN email_custom ec ON ec.email_custom_id = ecn.email_custom_id";
        }


        $sql.= " WHERE 1";
        if (!empty($formvars['email_custom'])) {
            $sql .= " AND ecn.email_custom_id = '" . $formvars['email_custom'] . "'";
        }
        if (!empty($formvars['boutique'])) {
            $sql .= " AND ecn.boutique_id = '" . $formvars['boutique'] . "'";
        }
        $this->request = $sql;
        
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('SUJET');
        $this->addLabel($list);

        if (!empty($formvars['email_custom'])) {
            $list = new BackLabel('BOUTIQUE');
            $this->addLabel($list);
        }
        if (!empty($formvars['boutique'])) {
            $list = new BackLabel('EMAIL');
            $this->addLabel($list);
        }


        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}