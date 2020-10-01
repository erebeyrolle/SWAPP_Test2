<?php

class bMega_menu_boutique extends BackModule
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
            $sql = "SELECT * FROM mega_menu_boutique WHERE boutique_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la boutique : <strong>" . $data['boutique_id'] . "</strong></a></span>";
        }

//        dump($_GET);
        // SQL
        if (!empty($formvars['id'])) {

            $this->request = getTable('mega_menu_boutique', $formvars['id']);

            $sql = "SELECT * FROM mega_menu_boutique mmb";
            $sql.= " LEFT JOIN boutique b ON (mmb.boutique_id = b.boutique_id)";
            $sql.= " WHERE 1";
            $sql.= " AND mmb.mega_menu_boutique_id = '" . $formvars['id']. "'";

            $this->request = $sql;
        }


        $form = new BackForm("boutique","select","boutique_id");
        $form->addAttr('class','required');
        $form->addOptionSQL(array("SELECT b.boutique_id, b.boutique_nom FROM boutique b"));
        $this->addForm($form);

        $form = new BackForm("Mega menu id", "hidden", "mega_menu_id");
        $this->addForm($form);

        $form = new BackForm("Boutique statut", "select", "mega_menu_boutique_statut_id");
        $form->addOption("1", "Oui");
        $form->addOption("2", "Non");
        $this->addForm($form);


//
//
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }


    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT mmb.mega_menu_boutique_id, b.boutique_id, b.boutique_nom, mmb.mega_menu_boutique_statut_id";
        $sql.= " FROM boutique b";
        $sql.= " LEFT JOIN mega_menu_boutique mmb ON (b.boutique_id = mmb.boutique_id)";
        $sql.= " WHERE 1";
        $sql.= " AND mmb.mega_menu_id = '" .$formvars['mega_menu']. "'";
        $this->request = $sql;


        //-- SQL
        // LABELS

        $label = new BackLabel("Boutique ID");
        $this->addLabel($label);

        $label = new BackLabel("Boutique nom");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);


        return $this->displayList($formvars['type']);
    }

}
