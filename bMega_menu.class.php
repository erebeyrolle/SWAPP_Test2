<?php

class bMega_menu extends BackModule
{

    function bMarque($formvars = array())
    {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    function update($formvars = array(), $table = null, $langue = true) {

        return parent::update($formvars, $table, $langue);

    }

    function create($formvars = array(), $table = null, $langue = true) {
        if(!empty($formvars['id']))
            $formvars['parent_id'] = $formvars['id'];
        return parent::create($formvars, $table, $langue);
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete($formvars, $table, $langue);
    }

    function Form($formvars = array())
    {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Cat&eacute;gorie</a></span>";
        else {
            $sql = "SELECT * FROM mega_menu WHERE mega_menu_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Mega menu : <strong>" . $data['mega_menu_id'] . "</strong></a></span>";
        }

//        dump($_GET);
        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('mega_menu', $formvars['id']);

            $sql = "SELECT m.* FROM mega_menu m";
            $sql.= " WHERE 1";
            $sql.= " AND m.mega_menu_id = '" . $formvars['id']. "'";

            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        //GROUP
//        if(!empty($formvars['id']){
//
//        }
        $form = new BackForm("Informations Mega menu", "group");
        $this->addForm($form);

        $form = new BackForm("Mega menu nom", "text", "mega_menu_nom");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Boutiques associées", "group");
        $form->addGroupOpts('class', 'mega_menu_boutique');
        $this->addForm($form);

        $form = new BackForm("Categorie associées au Mega menu", "group");
        $form->addGroupOpts('class', 'mega_menu_categorie');
        $this->addForm($form);


        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }


    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT m.mega_menu_id, m.mega_menu_id, m.mega_menu_nom, COUNT(DISTINCT mmc.mega_menu_categorie_id)";
        $sql.= " FROM (mega_menu m)";
        $sql.= " LEFT JOIN mega_menu_boutique mmb ON (mmb.mega_menu_id = m.mega_menu_id)";
        $sql.= " LEFT JOIN boutique b ON (b.boutique_id = mmb.boutique_id)";
        $sql.= " LEFT JOIN mega_menu_categorie mmc ON (mmc.mega_menu_id = m.mega_menu_id)";

        $this->request = $sql;
        //-- SQL
        // LABELS

        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("Mega menu Nom");
        $this->addLabel($label);

        $label = new BackLabel("Nombre de categorie", "listselect");
        $label->setVar('option', array("module" => "<a href='/back/html/mega_menu_categorie/list/?mmc*mega_menu_id=%ID%'>%IMG%</a>"));
        $this->addLabel($label);

        return $this->displayList($formvars['type']);
    }

}
