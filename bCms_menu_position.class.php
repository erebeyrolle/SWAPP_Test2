<?php

class bCms_menu_position extends BackModule {

    function bCms_menu_position($formvars = array()) {
        $GLOBALS['displayMceEditor'] = 1;
        parent::BackModule($formvars);
        $this->droits['DEL'] = false;
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Menu</a></span>";
        else {
            $sql = "SELECT menu_position_nom as data FROM cms_menu_position WHERE menu_position_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition d'une position de menu : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT mp.menu_position_id";
            $sql.=", mp.menu_position_nom ";
            $sql.=" FROM (cms_menu_position mp)";
            $sql.=" WHERE mp.menu_position_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom de la position", "text", "menu_position_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        //echo $sql;
        $sql = " SELECT mp.menu_position_id, mp.menu_position_id, mp.menu_position_nom";
        $sql.=" FROM (cms_menu_position mp)";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>