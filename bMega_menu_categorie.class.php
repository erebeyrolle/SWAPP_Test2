<?php

class bMega_menu_categorie extends BackModule
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

        if(!empty($formvars['mega_menu_id']))
            $formvars['parent_id'] = $formvars['mega_menu_id'];

        if ($_GET['mmc*parent_id'])
            $formvars['parent_id'] = $_GET['mmc*parent_id'];

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
            $sql = "SELECT * FROM mega_menu_categorie WHERE mega_menu_categorie_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Mega menu : <strong>" . $data['mega_menu_id'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {

            $this->request = getTable('mega_menu_categorie', $formvars['id']);

            $sql = "SELECT * FROM mega_menu_categorie mmc";
            $sql.= " WHERE 1";
            $sql.= " AND mmc.mega_menu_categorie_id = '" . $formvars['id']. "'";

            $this->request = $sql;
        }
//
//        dump($formvars);
//
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);

        $form = new BackForm("Categorie nom", "hidden", "mega_menu_id");
        $this->addForm($form);

        $form = new BackForm("Categorie nom", "text", "mega_menu_categorie_nom");
        $this->addForm($form);

        $form = new BackForm("Categorie rang", "text", "mega_menu_categorie_rang");
        $this->addForm($form);

        $form = new BackForm("Categorie url", "text", "mega_menu_categorie_url");
        $this->addForm($form);
        //--GROUP

//        if(!empty($formvars['id'])){
//            $form = new BackForm("Categorie associées à la categorie ". $formvars['id'] . "", "group");
//            $form->addGroupOpts('class', 'mega_menu_categorie');
//            $this->addForm($form);
//        }


//
//
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }


    function Listing($formvars = array()) {

        if(!empty($_GET['mmc*parent_id']))
            unset($_GET['mmc*mega_menu_id']);

        // RECHERCHE
        $form = new BackForm("Méga menu", "select", "mmc*mega_menu_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT mm.mega_menu_id, mm.mega_menu_nom FROM mega_menu mm ORDER BY mega_menu_nom "));
        $this->addForm($form);

        $form = new BackForm("categorie", "select", "mmc*parent_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT mmc.mega_menu_categorie_id, mmc.mega_menu_categorie_nom FROM mega_menu_categorie mmc "));
        $this->addForm($form);

//
//        //-- RECHERCHE

        // SQL
        $sql = "SELECT mmc.mega_menu_categorie_id, mmc.mega_menu_categorie_id, mmc.mega_menu_categorie_nom, mmc.mega_menu_categorie_rang, mmc.mega_menu_categorie_url,";
        $sql.= " COUNT(DISTINCT mmc2.mega_menu_categorie_id)";
        $sql.= " FROM mega_menu_categorie mmc";
        $sql.= " LEFT JOIN mega_menu_categorie mmc2 ON (mmc2.parent_id = mmc.mega_menu_categorie_id)";
        $sql.= " WHERE 1";
        if (!empty($formvars['mega_menu']))
            $sql.= " AND mmc.mega_menu_id = '" . $formvars['mega_menu'] . "'";
        elseif(!empty($_GET['mmc*parent_id'])){
            $sql.= " AND mmc.parent_id = '" . $_GET['mmc*parent_id'] . "'";
        }

        $this->request = $sql;

        //-- SQL
        // LABELS

        $label = new BackLabel("Mega menu categorie ID");
        $this->addLabel($label);

        $label = new BackLabel("Mega menu categorie nom");
        $this->addLabel($label);

        $label = new BackLabel("Mega menu categorie rang");
        $this->addLabel($label);

        $label = new BackLabel("Mega menu categorie url");
        $this->addLabel($label);

        $label = new BackLabel("Nombre de categorie", "listselect");
        $label->setVar('option', array("module" => "<a href='/back/html/mega_menu_categorie/list/?mmc*parent_id=%ID%'>%IMG%</a>"));
        $this->addLabel($label);


        return $this->displayList($formvars['type']);
    }

}