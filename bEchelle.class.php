<?php

class bEchelle extends BackModule {

    function bEchelle($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Echelle</a></span>";
        else {
            $sql = "SELECT echelle_valeur as data FROM echelle WHERE echelle_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de l'echelle : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('echelle', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Valeur", "text", "echelle_valeur");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "echelle_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT e.echelle_id, e.echelle_id, e.echelle_valeur, e.echelle_rang";
        $sql .= ", CONCAT(COUNT(pe.produit_id), ' article(s)')";
        $sql.=" FROM (echelle e)";
        $sql .= " LEFT JOIN produit_echelle pe ON(e.echelle_id = pe.echelle_id)";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("NB PRODUIT");
        $this->addLabel($label);

        return $this->displayList($formvars['type']);
    }

//--
}
?>