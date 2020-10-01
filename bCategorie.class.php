<?php

class bCategorie extends BackModule {

    function bCategorie($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        if ($formvars['parent_id'] == "NULL")
            unset($formvars['parent_id']);

        // $source = str_replace(SITE_URL, SITE_DIR, $formvars['categorie_image']);
        // $formvars['categorie_image_mini'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 282, 135, 'img'));

        $return =  parent::update_multi($formvars, $table, $langue);

        $categorie = new Categorie;
        $categorie->updateExtId($formvars['back_id']);

        return $return;



    }

    function create($formvars = array(), $table = null, $langue = true) {
        if ($formvars['parent_id'] == "NULL")
            unset($formvars['parent_id']);

        // $source = str_replace(SITE_URL, SITE_DIR, $formvars['categorie_image']);
        // $formvars['categorie_image_mini'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 282, 135, 'img'));

        $id = parent::create_multi($formvars, $table, $langue);

        $categorie = new Categorie;
        $categorie->updateExtId($id);

        Boutique::getInstance()->createStatut('categorie', $id);

        return $id;

    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Cat&eacute;gorie</a></span>";
        else {
            $sql = "SELECT categorie_nom_nom as data FROM categorie_nom WHERE categorie_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la Cat&eacute;gorie : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('categorie', $formvars['id']);
        }
        //-- SQL
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);


        $form = new BackForm("Parent", "select", "parent_id");
        $form->addOption("NULL", "---");
        $form->addCategorieRecursive("","","");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "categorie_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "categorie_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Association boutique", "select", "categorie_assoc_auto");
        $form->setVar('comment', 'Association automatique aux nouvelles boutiques.');
        $form->addOption('1', 'Oui');
        $form->addOption('0', 'Non');
        $this->addForm($form);

        $form = new BackForm("Livraison Special", "select", "categorie_livraison_spe");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '1','Oui') UNION (SELECT '0','Non')"));
        $this->addForm($form);

        $form = new BackForm("Visible", "select", "categorie_visible");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '1','Oui') UNION (SELECT '0','Non')"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'categorie_boutique');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Informations secondaires", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Image", "image", "categorie_image");
        $this->addForm($form);
        $form = new BackForm("Description", "tinymce", "categorie_nom_desc");
        $form->setVar('translate', true);
        $this->addForm($form);

        //GROUP
        $form = new BackForm("R&eacute;f&eacute;rencement", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Ref Titre", "text", "categorie_nom_meta_title");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Desc", "text", "categorie_nom_meta_desc");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Mots-Cl&eacute;s", "text", "categorie_nom_meta_kw");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Autre", "tinymce", "categorie_nom_meta_other");
        $form->setVar('translate', true);
        $this->addForm($form);

        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Cat&eacute;gorie", "select", "c.parent_id");
        $form->addOption("", "---");
        $form->addCategorieRecursive("","","");
        //$form->addOptionSQL(array("SELECT c.categorie_id, cn.categorie_nom_nom FROM categorie c, categorie_nom cn WHERE c.categorie_id = cn.categorie_id AND c.parent_id IS NULL AND cn.langue_id = '" . LANGUE . "' ORDER BY categorie_nom_nom"));
        $this->addForm($form);
        //-- RECHERCHE



        // SQL
        $sql = "SELECT c.categorie_id, c.categorie_id, cn.categorie_nom_nom";
        $sql .= ", CONCAT(COUNT(DISTINCT pc.produit_id), ' article(s)')";
        $sql .= ", c.categorie_rang";
        $sql .= ", IF(c.categorie_visible = 1, 'Oui', 'Non')";
        $sql.=", COUNT(DISTINCT cenf.categorie_id)";
        $sql.=" FROM (categorie c)";
        $sql.=" LEFT JOIN categorie_nom cn ON (c.categorie_id = cn.categorie_id AND cn.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN categorie cenf ON (cenf.parent_id = c.categorie_id)";
        $sql.=" LEFT JOIN produit_categorie pc ON (pc.categorie_id = c.categorie_id OR cenf.categorie_id = pc.categorie_id)";
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

        $label = new BackLabel("VISIBLE");
        $this->addLabel($label);

        $label = new BackLabel("SOUS-CATEGORIES", "listselect");
        $label->setVar('option', array("module" => "<a href='../../categorie/list/?c*parent_id=%ID%'>%IMG%</a>"));
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}