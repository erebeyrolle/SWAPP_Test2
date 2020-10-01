<?php

class bGamme extends BackModule {

    function bGamme($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $id = parent::create_multi($formvars, $table, $langue);
        Boutique::getInstance()->createStatut('gamme', $id);
        return $id;
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Gamme</a></span>";
        else {
            $sql = "SELECT gamme_nom_nom as data FROM gamme_nom WHERE gamme_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la Gamme : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=" FROM gamme c, gamme_nom cn";
            $sql.=" WHERE c.gamme_id='" . $formvars['id'] . "'";
            $sql.=" AND c.gamme_id = cn.gamme_id";
            $sql.=" AND cn.langue_id='" . LANGUE . "'";

            $this->request = getTableMutli('gamme', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP
        /*
         * form = new BackForm("Parent","select","parent_id");
         * form->addOption("NULL","---");
         * form->addOptionSQL(array("SELECT c.gamme_id, cn.gamme_nom_nom FROM gamme c, gamme_nom cn WHERE c.parent_id IS NULL AND c.gamme_id = cn.gamme_id AND cn.langue_id ='".LANGUE."' ORDER BY gamme_nom_nom",false));
         * this->addForm($form);
         */
        $form = new BackForm("Nom", "text", "gamme_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);
/*
        $form = new BackForm("Nom Header", "text", "gamme_nom_spec");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Nom Home", "text", "gamme_nom_home");
        $form->setVar('translate', true);
        $this->addForm($form);*/

        $form = new BackForm("Rang", "text", "gamme_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Visible", "select", "gamme_visible");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT 0, 'Non') UNION (SELECT 1, 'Oui')"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'gamme_boutique');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Informations secondaires", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Etiquette", "image", "gamme_image");
        $this->addForm($form);

        $form = new BackForm("Description", "tinymce", "gamme_nom_desc");
        $form->setVar('translate', true);
        $this->addForm($form);

        //GROUP
        $form = new BackForm("R&eacute;f&eacute;rencement", "group");
        $this->addForm($form);
        //--GROUP
        $form = new BackForm("Ref Titre", "text", "gamme_nom_meta_title");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Desc", "text", "gamme_nom_meta_desc");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Mots-Cl&eacute;s", "text", "gamme_nom_meta_kw");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Autre", "tinymce", "gamme_nom_meta_other");
        $form->setVar('translate', true);
        $this->addForm($form);


        //GROUP
        $form = new BackForm("Categories", "group");
        $form->addGroupOpts('class', 'gamme_categorie');
        $this->addForm($form);
        //--GROUP

        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Gamme", "select", "c.parent_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT c.gamme_id, cn.gamme_nom_nom FROM gamme c, gamme_nom cn WHERE c.gamme_id = cn.gamme_id AND c.parent_id IS NULL AND cn.langue_id = '" . LANGUE . "' ORDER BY gamme_nom_nom"));
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT c.gamme_id, c.gamme_id, cn.gamme_nom_nom";
        $sql .= ", CONCAT(COUNT(pg.produit_id), ' article(s)')";
        $sql .= ", c.gamme_rang, IF(c.gamme_visible = 1, 'Oui', 'Non')";
        //$sql.=", IF(c.parent_id IS NULL,COUNT(DISTINCT cenf.gamme_id),'--'), c.actif_id";
        $sql.=" FROM (gamme c)";
        $sql.=" LEFT JOIN gamme_nom cn ON (c.gamme_id = cn.gamme_id AND cn.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN gamme cenf ON (cenf.parent_id = c.gamme_id)";
        $sql.=" LEFT JOIN produit_gamme pg ON (pg.gamme_id = c.gamme_id)";
        $sql.=" WHERE 1";
        if (!isset($_GET['c*parent_id']) || empty($_GET['c*parent_id']) || $_GET['c*parent_id'] == 'NULL')
            $sql.=" AND c.parent_id IS NULL";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("NB PRODUIT");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);
        /*
         * label = new BackLabel("SOUS-GAMMES","listselect");
         * label->setVar('option',array("module" => "<a href='../../gamme/list/?c*parent_id=%ID%'>%IMG%</a>"));
         * this->addLabel($label);
         */
        $label = new BackLabel("AFFICHAGE MENU");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}