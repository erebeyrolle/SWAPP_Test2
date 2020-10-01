<?php

class bBack_administrateur_groupe extends BackModule {

    function bBack_administrateur_groupe($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array()) {
        $formvars['administrateur_id'] = $formvars['back_administrateur_id'];
        return parent::update($formvars);
    }

    function create($formvars = array()) {
        $formvars['administrateur_id'] = $formvars['back_administrateur_id'];
        return parent::create($formvars);
    }

    function delete($formvars = array()) {
        $sql = "DELETE FROM back_administrateur_groupe WHERE administrateur_groupe_id = '" . $formvars['back_id'] . "'";
        return $this->sql->query($sql);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT administrateur_groupe_id";
            $sql.=", administrateur_id as back_administrateur_id";
            $sql.=", groupe_id";
            $sql.=" FROM back_administrateur_groupe";
            $sql.=" WHERE administrateur_groupe_id = '" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("administrateur_id ID", "hidden", "back_administrateur_id");
        $this->addForm($form);

        $form = new BackForm("Groupe", "select", "groupe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT g.groupe_id, g.groupe_nom FROM back_groupe g ORDER BY groupe_rang"));
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT ag.administrateur_groupe_id";
        $sql.=", ag.administrateur_groupe_id";
        $sql.=", bg.groupe_nom";
        $sql.=" FROM (back_administrateur_groupe ag, back_groupe bg)";
        $sql.=" WHERE ag.groupe_id = bg.groupe_id";
        if (!empty($formvars['back_administrateur']))
            $sql.=" AND ag.administrateur_id = '" . $formvars['back_administrateur'] . "'";

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