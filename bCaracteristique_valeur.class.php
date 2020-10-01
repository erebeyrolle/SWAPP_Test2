<?php

class bCaracteristique_valeur extends BackModule {

    function bCaracteristique_valeur($formvars = array()) {
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

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('caracteristique_valeur', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("caracteristique ID", "hidden", "caracteristique_id");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "caracteristique_valeur_nom_nom");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $form->addAttr('size', 20);
        $this->addForm($form);

		$form = new BackForm("Visible dans le filtre", "select", "caracteristique_valeur_visible_filtre");
        $form->addOptionSQL(array("(SELECT 0, 'Non') UNION (SELECT 1, 'Oui')"));
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "caracteristique_valeur_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Informations couleur", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Code hexa", "text", "caracteristique_valeur_nom_image");
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT cv.caracteristique_valeur_id, cv.caracteristique_valeur_id, cvn.caracteristique_valeur_nom_nom, caracteristique_valeur_rang";
        $sql.= " , IF(cv.caracteristique_valeur_visible_filtre = 1, 'Oui', 'Non')";
        $sql.=" FROM (caracteristique_valeur cv)";
        $sql.=" LEFT JOIN caracteristique_valeur_nom cvn ON (cvn.caracteristique_valeur_id = cv.caracteristique_valeur_id AND langue_id ='" . LANGUE . "')";
        $sql.=" WHERE 1";
        if (!empty($formvars['caracteristique']))
            $sql.=" AND cv.caracteristique_id = '" . $formvars['caracteristique'] . "'";
        $this->limit = 0;
        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);
        
        $label = new BackLabel("VISIBLE DANS LE FILTRE");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>