<?php

class bReferencement extends BackModule {

    function bReferencement($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        return parent::create_multi($formvars, $table, $langue);
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Recherche</a></span>";
        else {
            $sql = "SELECT referencement_url as data FROM (referencement r, referencement_nom rn) WHERE r.referencement_id = '" . $formvars['id'] . "' AND r.referencement_id = rn.referencement_id and rn.langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du R&eacute;f&eacute;rencement de l'URL : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('referencement', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire

        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_id"));
        $this->addForm($form);

        $form = new BackForm("Nom Informatif", "text", "referencement_nom_informatif");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("URL", "text", "referencement_url");
        $form->setVar('translate', true);
        $form->addAttr('size', 100);
        $form->setVar('comment', 'exemple : pour www.monsite.com/index.html mettre /index.html');
        $this->addForm($form);

        $form = new BackForm("Ref Titre", "text", "referencement_meta_title");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Desc", "text", "referencement_meta_desc");
        $form->setVar('translate', true);
        $form->addAttr('size', 80);
        $this->addForm($form);

        $form = new BackForm("Ref Mots-Cl&eacute;s", "text", "referencement_meta_kw");
        $form->setVar('translate', true);
        $form->addAttr('size', 80);
        $this->addForm($form);

        $form = new BackForm("Ref Autre", "tinymce", "referencement_meta_other");
        $form->setVar('translate', true);
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {
        // RECHERCHE
        $form = new BackForm("URL", "text", "rn.referencement_url");
        $form->setVar("compare", "LIKE");
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "r.boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_id"));
        $this->addForm($form);
        // /RECHERCHE
        // SQL
        $sql = "SELECT r.referencement_id, r.referencement_id";
        $sql.=", rn.referencement_nom_informatif";
        $sql.=", rn.referencement_url";
        $sql.=", rn.referencement_meta_title";
        $sql.=", rn.referencement_meta_desc";
        $sql.=", rn.referencement_meta_kw";
        $sql.=" FROM (referencement r, referencement_nom rn)";
        $sql.=" WHERE 1";
        $sql.=" AND rn.referencement_id = r.referencement_id AND rn.langue_id = '" . LANGUE . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("URL");
        $this->addLabel($label);

        $label = new BackLabel("TITLE");
        $this->addLabel($label);

        $label = new BackLabel("DESC");
        $this->addLabel($label);

        $label = new BackLabel("KW");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}