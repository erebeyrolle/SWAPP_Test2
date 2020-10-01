<?php

class bPartenaire extends BackModule {

    function bPartenaire($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        $return = parent::update($formvars, $table, $langue);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $return = parent::create($formvars, $table, $langue);
        Boutique::getInstance()->createStatut('partenaire', $return);
        return $return;
    }

    function Form($formvars = array()) {


        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un Partenaire </a></span>";
        else {
            $sql = "SELECT p.partenaire_id as data FROM (partenaire p)";

            $sql.= " WHERE 1";
            $sql.= " AND p.partenaire_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Partenaire : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (partenaire p)";

            $sql.= " WHERE 1";
            $sql.= " AND p.partenaire_id = '" . $formvars['id'] . "'";
            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire

        $form = new BackForm('Nom', 'text', 'partenaire_nom');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Image', 'image', 'partenaire_image');
        $form->addAttr('class', 'required');
        $this->addForm($form);



        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'partenaire_boutique');
        $this->addForm($form);

        // $form->addAttr('class', 'required');
        // $form->addOption("", "---");
        // $form->addOptionSQL(array("SELECT p.partenaire_id, p.partenaire_nom_nom FROM partenaire p"));
        // $form->addOptionSQL(array("(SELECT '2', 'Non') UNION (SELECT '1', 'Oui')"));

        //  $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm('# ID', 'text', 'p.partenaire_id');
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);


        $sql = "SELECT p.partenaire_id, p.partenaire_id";
        $sql.= ", p.partenaire_nom";
        $sql.= ", p.partenaire_image";

        $sql.= " FROM (partenaire p)";

        $sql.= " WHERE 1";
        $this->request = $sql;


        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('NOM');
        $this->addLabel($list);

        $list = new BackLabel('IMAGE');
        $this->addLabel($list);



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}