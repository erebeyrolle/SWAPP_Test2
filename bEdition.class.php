<?php

class bEdition extends BackModule
{

    function bEdition($formvars = array())
    {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true)
    {
        return parent::update_multi($formvars);
    }

    function create($formvars = array(), $table = null, $langue = true)
    {
        $id = parent::create_multi($formvars, $table, $langue);
        Boutique::getInstance()->createStatut('edition', $id);
        return $id;
    }

    function delete($formvars = array())
    {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array())
    {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau texte</a></span>";
        else {
            $sql = "SELECT edition_nom as data FROM edition WHERE edition_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du texte  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('edition', $formvars['id']);
        }
        //-- SQL

        // Champs du formulaire
        $form = new BackForm("CATEGORIE", "select", "edition_categorie_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT edition_categorie_id, edition_categorie_nom FROM (edition_categorie) ORDER BY edition_categorie_nom"));
        $this->addForm($form);

        $form = new BackForm("CODE", "text", "edition_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("TEXTE", "tinymce", "edition_nom_nom");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'edition_boutique');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id'])) $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array())
    {

        // RECHERCHE
        $form = new BackForm("Cat&eacute;gorie", "select", "e.edition_categorie_id");
        $form->setVar('compare', 'EQUAL');
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT edition_categorie_id, edition_categorie_nom FROM edition_categorie ORDER BY edition_categorie_nom"));
        $this->addForm($form);

        $form = new BackForm("Code", "text", "e.edition_nom");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Texte", "text", "en.edition_nom_nom");
        $form->setVar('compare', 'SPEC');
        $form->addAttr('size', 20);
        $this->addForm($form);

        //-- RECHERCHE

        // SQL
        $sql = "SELECT e.edition_id";
        $sql .= ", e.edition_id";
        $sql .= ", edition_nom";
        $sql .= ", CONCAT('<pre style=\'font-family:arial;font-size:10px\'>',en.edition_nom_nom,'<pre>')";
        $sql .= " FROM (edition e)";
        $sql .= " LEFT JOIN edition_nom en on (e.edition_id = en.edition_id AND en.langue_id = '" . LANGUE . "')";
        $sql .= " WHERE 1";
        if (!empty($_GET['en*edition_nom_nom'])) {
            $sql .= " AND (en.edition_nom_nom LIKE '%" . $_GET['en*edition_nom_nom'] . "%')";
        }

        $this->request = $sql;
        //-- SQL

        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("CODE");
        $this->addLabel($label);

        $label = new BackLabel("TEXTE");
        $label->setVar('align', 'left');
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}