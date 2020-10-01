<?php

class bGroupe extends BackModule {

    function bGroupe($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array()) {

        $return = parent::update_multi($formvars);
        return $return;
    }

    function create($formvars = array()) {

        $return = parent::create_multi($formvars);
        return $return;
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {
        
        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau groupe</a></span>";
        else {
            $sql = "SELECT groupe_nom_nom as data FROM groupe_nom WHERE groupe_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du groupe : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('groupe', $formvars['id']);
        }
        //-- SQL
        
        $form = new BackForm("Categorie ID", "hidden", "categorie_id");
        $this->addForm($form);
                
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom", "text", "groupe_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "groupe_rang");
        $form->addAttr('class', 'required');
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
        $sql = "SELECT g.groupe_id, g.groupe_id, gn.groupe_nom_nom, gn.groupe_rang";
        $sql.=" FROM (groupe g)";
        $sql.= " LEFT JOIN groupe_nom gn ON(gn.groupe_id = g.groupe_id)";
        $sql.=" WHERE 1";
        if (!empty($formvars['categorie']))
            $sql.=" AND g.categorie_id = '" . $formvars['categorie'] . "'";
        $this->request = $sql;
        //-- SQL
        // LABELS

        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("Rang");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>