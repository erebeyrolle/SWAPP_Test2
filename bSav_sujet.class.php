<?php

class bSav_sujet extends BackModule
{

    function bSav_sujet($formvars = array())
    {
        parent::BackModule($formvars);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        return parent::create_multi($formvars, $table, $langue);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function Form($formvars = array())
    {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un sujet</a></span>";
        else {
            $sql = "SELECT * FROM sav_sujet WHERE sav_sujet_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Sujet : <strong>" . $data['mega_menu_id'] . "</strong></a></span>";
        }

//        dump($_GET);
        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('sav_sujet', $formvars['id']);

//            $sql = "SELECT ss.* FROM sav_sujet ss INNER JOIN sav_sujet_nom as ssn ON (ssn.sav_sujet_id = ss.sav_sujet_id AND ssn.langue_id = ".LANGUE.") ";
//            $sql.= " WHERE 1";
//            $sql.= " AND ss.sav_sujet_id = '" . $formvars['id']. "'";
//            $this->request = $sql;
        }

        $form = new BackForm("Informations SAV", "group");
        $this->addForm($form);

        $form = new BackForm("Code ines", "text", "sav_sujet_code_ines");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "sav_sujet_nom_nom");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "sav_sujet_rang");
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "sav_sujet_statut_id");
        $form->addOptionSQL(array("SELECT sav_sujet_statut_id, sav_sujet_statut_nom FROM sav_sujet_statut ORDER BY sav_sujet_statut_nom"));
        $this->addForm($form);
        //--GROUP

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }


    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT ss.sav_sujet_id, ss.sav_sujet_id, ss.sav_sujet_code_ines, ssn.sav_sujet_nom_nom, ss.sav_sujet_rang, ss.sav_sujet_statut_id ";
        $sql.= " FROM (sav_sujet ss) ";
        $sql.= " INNER JOIN sav_sujet_nom as ssn ON (ssn.sav_sujet_id = ss.sav_sujet_id AND ssn.langue_id = '".LANGUE_ID."') ";

        $this->request = $sql;
        //-- SQL
        // LABELS

        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("Code Ines");
        $this->addLabel($label);

        $label = new BackLabel("Nom");
        $this->addLabel($label);

        $label = new BackLabel("Rang");
        $this->addLabel($label);

        $label = new BackLabel("STATUT","statut");
        $this->addLabel($label);

        return $this->displayList($formvars['type']);
    }

}
