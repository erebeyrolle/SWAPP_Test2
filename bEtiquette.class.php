<?php

class bEtiquette extends BackModule {

    function bEtiquette($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        return parent::create_multi($formvars, $table, $langue);
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle etiquette</a></span>";
        else {
            $sql = "SELECT etiquette_libelle as data FROM etiquette WHERE etiquette_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de l'etiquette  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('etiquette', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        
        $form = new BackForm("Libelle", "text", "etiquette_libelle");
        $form->addAttr('class', 'required');
        $this->addForm($form);
        
        $form = new BackForm("Nom", "text", "etiquette_nom_nom");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Image liste", "image", "etiquette_nom_image_liste");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Image Fiche", "image", "etiquette_nom_image_detail");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'etiquette_boutique');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Libelle", "text", "e.etiquette_libelle");
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT e.etiquette_id, e.etiquette_id, e.etiquette_libelle";
        $sql.=" FROM (etiquette e, etiquette_nom en)";
        $sql.=" WHERE 1";
				$sql.=" AND e.etiquette_id = en.etiquette_id AND en.langue_id = '".LANGUE."'";


        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("LIBELLE");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS
        

        return $this->displayList($formvars['type']);
    }

//--
}