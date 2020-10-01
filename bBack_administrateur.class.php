<?php

class bBack_administrateur extends BackModule {

    function bBack_administrateur($formvars = array()) {
        parent::BackModule($formvars);
    }

    function delete($formvars = array()) {
        $sql = "DELETE FROM back_administrateur WHERE administrateur_id = '" . $formvars['back_id'] . "'";
        return $this->sql->query($sql);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouvel Utilisateur</a></span>";
        else {
            $sql = "SELECT CONCAT(administrateur_nom,' ',administrateur_prenom,' - #', administrateur_id ,'') as data FROM back_administrateur WHERE administrateur_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de l'Utilisateur : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=" FROM back_administrateur";
            $sql.=" WHERE administrateur_id ='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom", "text", "administrateur_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Pr&eacute;nom", "text", "administrateur_prenom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("E-mail (identifiant)", "mail", "administrateur_email");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Mot de passe", "text", "administrateur_password");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "actif_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT actif_id, actif_nom FROM actif ORDER BY actif_nom"));
        $this->addForm($form);

        $form = new BackForm("Groupes", "group");
        $form->addGroupOpts('class', 'back_administrateur_groupe');
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {
        // SQL
        $sql = "SELECT a.administrateur_id";
        $sql.=", a.administrateur_id";
        $sql.=", CONCAT(administrateur_nom,' ',administrateur_prenom,'') as nom";
        $sql.=", administrateur_email";
        $sql.=", COUNT(ag.administrateur_groupe_id)";
        $sql.=" FROM (back_administrateur a)";
        $sql.=" LEFT JOIN back_administrateur_groupe ag ON (a.administrateur_id = ag.administrateur_id)";
        $sql.=" WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM PRENOM");
        $this->addLabel($label);

        $label = new BackLabel("IDENTIFIANT", "mail", "E-MAIL");
        $this->addLabel($label);

        $label = new BackLabel("GROUPES", "listselect");
        $label->setVar('option', array("group" => "back_administrateur_groupe"));
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>