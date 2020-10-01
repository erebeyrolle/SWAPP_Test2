<?php

class bSujet extends BackModule {

    function bSujet($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $id = parent::create_multi($formvars, $table, $langue);
        Boutique::getInstance()->createStatut('sujet', $id);
        return $id;
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Sujet</a></span>";
        else {
            $sql = "SELECT sujet_id as data FROM sujet WHERE sujet_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Sujet  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('sujet', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Sujet", "text", "sujet_nom_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', '75');

        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Email", "text", "sujet_nom_email");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "sujet_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'sujet_boutique');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        //-- RECHERCHE
        // SQL
        $sql = "SELECT s.sujet_id, s.sujet_id, sn.sujet_nom_nom, sn.sujet_nom_email, s.sujet_rang";
        $sql.=" FROM (sujet s, sujet_nom sn)";
        $sql.=" WHERE 1";
        $sql.=" AND s.sujet_id = sn.sujet_id AND sn.langue_id = '" . LANGUE . "'";


        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("EMAIL");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);


        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}