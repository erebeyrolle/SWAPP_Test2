<?php

class bCms_menu extends BackModule {

    function bCms_menu($formvars = array()) {
        $GLOBALS['displayMceEditor'] = 1;
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $id = parent::create_multi($formvars, $table, $langue);

        Boutique::getInstance()->createStatut('cms_menu', $id);
        return $id;
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Menu</a></span>";
        else {
            $sql = "SELECT menu_nom_nom as data FROM cms_menu_nom WHERE cms_menu_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Menu : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT m.cms_menu_id";
            $sql.=", m.menu_position_id, mn.menu_nom_nom";
            $sql.=", m.menu_rang, m.menu_statut_id";
            $sql.=" FROM (cms_menu m, cms_menu_nom mn)";
            $sql.=" WHERE m.cms_menu_id='" . $formvars['id'] . "'";
            $sql.=" AND m.cms_menu_id = mn.cms_menu_id";

            $this->request = $sql;
        }
        //-- SQL
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP
        // Champs du formulaire
        $form = new BackForm("Position du menu", "select", "menu_position_id");
        $form->addAttr('class', 'required');
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT menu_position_id, menu_position_nom FROM cms_menu_position"));
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "menu_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "menu_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "menu_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT menu_statut_id, menu_statut_nom FROM cms_menu_statut"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'cms_menu_boutique');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        //echo $sql;
        $sql = " SELECT m.cms_menu_id, m.cms_menu_id, mn.menu_nom_nom, cmp.menu_position_nom, m.menu_rang, m.menu_statut_id";
        $sql.=" FROM (cms_menu m, cms_menu_nom mn)";
        $sql.=" LEFT JOIN cms_page p on (p.cms_menu_id = m.cms_menu_id)";
        $sql.=" LEFT JOIN cms_menu_position cmp on (cmp.menu_position_id = m.menu_position_id)";
        $sql.=" WHERE 1 AND mn.cms_menu_id = m.cms_menu_id";
        $sql.=" AND mn.langue_id = '" . LANGUE . "'";


        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("POSITION");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}