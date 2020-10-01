<?php
class bCaracteristique extends BackModule {

    function bCaracteristique($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMceEditor'] = true;
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
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle caractéristique</a></span>";
        else {
            $sql = "SELECT caracteristique_nom_nom as data FROM caracteristique_nom WHERE caracteristique_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la caracteristique : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('caracteristique', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom", "text", "caracteristique_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Code", "hidden", "caracteristique_prefix");
        $form->addAttr('class', 'required');
        $this->addForm($form);
        
        
        $form = new BackForm("Ouverture auto", "select", "caracteristique_ouverture");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT 0, 'Non') UNION (SELECT 1, 'Oui')"));
        $this->addForm($form);
                
        $form = new BackForm("Rang listing", "text", "caracteristique_rang");
        $this->addForm($form);

        $form = new BackForm("Visible", "select", "caracteristique_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT 0, 'Non') UNION (SELECT 1, 'Oui')"));
        $this->addForm($form);
        
        $form = new BackForm("Valeurs", "group");
        $form->addGroupOpts('class', 'caracteristique_valeur');
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT c.caracteristique_id, c.caracteristique_id, cn.caracteristique_nom_nom, c.caracteristique_rang";
        $sql .= ", IF(c.caracteristique_statut_id = 1, 'Oui', 'Non')";
        $sql.=", COUNT(DISTINCT cv.caracteristique_valeur_id)";
        $sql.=" FROM (caracteristique c)";
        $sql.=" LEFT JOIN caracteristique_nom cn ON (c.caracteristique_id = cn.caracteristique_id AND cn.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN caracteristique_valeur cv ON (cv.caracteristique_id = c.caracteristique_id)";
        $sql.=" WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("RANG LISTING");
        $this->addLabel($label);
        
        $label = new BackLabel("VISIBLE");
        $this->addLabel($label);
        
        $label = new BackLabel("VALEURS", 'listselect');
        $label->setVar('option', array("group" => "caracteristique_valeur"));
        $this->addLabel($label);

        
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>