<?php

class bAdresse extends BackModule {

    function bAdresse($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(),$table = null, $langue = true) {
        $return = parent::update($formvars,$table , $langue );
        if (!isset($formvars['back_default']))
            $formvars['back_default'] = "";
        $this->setDefault($this->name_url, 'client', $formvars['back_id'], $formvars['back_default']);

        return $return;
    }

    function create($formvars = array(),$table = null, $langue = true) {
        $return = parent::create($formvars,$table , $langue );
        if (!isset($formvars['back_default']))
            $formvars['back_default'] = "";
        $this->setDefault($this->name_url, 'client', $return, $formvars['back_default']);
        return $return;
    }

    function delete($formvars = array()) {
        $zeID = $this->getValue($this->name_url, 'client_id', $this->name_url . '_id', $formvars['back_id']);
        $return = parent::delete($formvars);
        $this->setDefault($this->name_url, 'client', $zeID, NULL);
        return $return;
    }

    function Form($formvars = array()) {
        if(isset($formvars['annonceur_id'])) {
            $formvars['client_id'] = $formvars['annonceur_id'];
        }
        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle adresse</a></span>";
        else {
            $sql = "SELECT adresse_id as data FROM adresse WHERE adresse_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de l'adresse  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=", adresse_default as back_default";
            $sql.=" FROM (adresse)";
            $sql.=" WHERE adresse_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }



        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Client ID", "hidden", "client_id");
        $this->addForm($form);

        $form = new BackForm("Libelle", "text", "adresse_libelle");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Civilit&eacute;", "select", "civilite_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT civilite_id, civilite_nom_nom FROM (civilite_nom) WHERE langue_id = '" . LANGUE . "' ORDER BY civilite_id"));
        $this->addForm($form);

        $form = new BackForm("Societe", "text", "adresse_societe");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "adresse_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Pr&eacute;nom", "text", "adresse_prenom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rue", "text", "adresse_rue");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Compl&eacute;ment", "text", "adresse_rue2");
        $this->addForm($form);

        $form = new BackForm("Code Postal", "text", "adresse_cp");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Ville", "text", "adresse_ville");
        $form->addAttr('class', 'required');
        $this->addForm($form);
        /*
          $form = new BackForm("Etat", "text", "adresse_etat");
          $this->addForm($form);
         */
        $form = new BackForm("Pays", "select", "pays_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT pays_id, pays_nom_nom FROM (pays_nom) WHERE langue_id = '" . LANGUE . "' ORDER BY pays_nom_nom"));
        $this->addForm($form);

        $form = new BackForm("T&eacute;l&eacute;phone", "text", "adresse_telephone");
        $this->addForm($form);

        $form = new BackForm("D&eacute;faut", "checkbox", "back_default");
        $form->setVar('comment', 'Cochez cette case pour d&eacute;finir cette adresse comment &eacute;tant celle par d&eacute;faut.');
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT a.adresse_id";
        $sql.=", a.adresse_id";
        $sql.=", a.adresse_libelle";
        $sql.=", CONCAT(a.adresse_societe,'<br/>',civilite_nom_nom,' ',a.adresse_prenom,' ',a.adresse_nom,'<br/>',adresse_rue,'<br/>',adresse_rue2,'<br/>',adresse_cp,' ',adresse_ville,'<br/>',pays_nom_nom)";
        $sql.=", a.adresse_default";
        $sql.=" FROM (adresse a, civilite_nom cn, pays_nom pn)";
        $sql.=" WHERE a.civilite_id = cn.civilite_id AND cn.langue_id = '" . LANGUE . "'";
        $sql.=" AND a.pays_id = pn.pays_id AND pn.langue_id = '" . LANGUE . "'";
        if (!empty($formvars['client']))
            $sql.=" AND a.client_id = '" . $formvars['client'] . "'";
        if (!empty($formvars['annonceur']))
            $sql.=" AND a.client_id = '" . $formvars['annonceur'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("LIBELLE");
        $this->addLabel($label);
        $label = new BackLabel("ADRESSE");
        $this->addLabel($label);


        $label = new BackLabel("DEFAUT", 'default');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>