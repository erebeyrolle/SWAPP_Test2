<?php

class bMarque extends BackModule {

    function bMarque($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    function update($formvars = array(), $table = null, $langue = true) {

        $formvars['marque_nom_nom'] = $formvars['marque_nom'];
        /*
        $source = str_replace(SITE_URL, SITE_DIR, $formvars['marque_image']);
        $formvars['marque_image_mini'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 186, 186, 'img'));

        $source_liste = str_replace(SITE_URL, SITE_DIR, $formvars['marque_image_liste']);
        // Y => 972
        // ratio de Y/972 -> X*ratio = Z
        // X => Z
        list($width, $height) = getimagesize($source_liste);
        $ratio = $width/972;
        $goodHeight = round($height/$ratio,0);
        $formvars['marque_image_liste_redim'] = str_replace(SITE_DIR, SITE_URL, redimImage($source_liste, 972, $goodHeight, 'img'));
        */
        $return = parent::update_multi($formvars);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {

        $formvars['marque_nom_nom'] = $formvars['marque_nom'];
        /*
        $source = str_replace(SITE_URL, SITE_DIR, $formvars['marque_image']);
        $formvars['marque_image_mini'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 186, 186, 'img'));

        $source_liste = str_replace(SITE_URL, SITE_DIR, $formvars['marque_image_liste']);
        // Y => 972
        // ratio de Y/972 -> X*ratio = Z
        // X => Z
        list($width, $height) = getimagesize($source_liste);
        $ratio = $width/972;
        $goodHeight = round($height/$ratio,0);
        $formvars['marque_image_liste_redim'] = str_replace(SITE_DIR, SITE_URL, redimImage($source_liste, 972, $goodHeight, 'img'));
        */
        $id = parent::create_multi($formvars);
        Boutique::getInstance()->createStatut('marque', $id);
        return $id;
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Marque</a></span>";
        else {
            $sql = "SELECT marque_nom as data FROM marque m LEFT JOIN marque_nom mn ON mn.marque_id = m.marque_id WHERE m.marque_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la Marque : <strong>" . $data['data'] . "</strong></a></span>";
        }
//        var_dump($sql);

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('marque', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom", "text", "marque_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Référence", "text", "marque_ref");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Logo", "image", "marque_image");
        $this->addForm($form);

        $form = new BackForm("Logo (Page: Description marque, largeur: 972px)", "image", "marque_image_liste");
        $this->addForm($form);
/*
        $form = new BackForm("Logo (Page: Slideshow, Taille: 158px X 74px)", "image", "marque_image_mini");
        $this->addForm($form);
*/

        $form = new BackForm("Description", "tinymce", "marque_nom_desc");
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "marque_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT 1, 'ACTIF') UNION (SELECT 2, 'INACTIF')"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'marque_boutique');
        $this->addForm($form);
/*
        $form = new BackForm("Slideshow", "select", "marque_statut_slideshow_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT 1, 'ACTIF') UNION (SELECT 2, 'INACTIF')"));
        $this->addForm($form);

        $form = new BackForm("Menu", "select", "marque_statut_menu_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT 1, 'VISIBLE') UNION (SELECT 2, 'INVISIBLE')"));
        $this->addForm($form);
*/

        //GROUP
        $form = new BackForm("R&eacute;f&eacute;rencement", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Ref Titre", "text", "marque_nom_meta_title");
        $form->addAttr('size', '100');
        $this->addForm($form);

        $form = new BackForm("Ref Desc", "text", "marque_nom_meta_desc");
        $form->addAttr('size', '100');
        $this->addForm($form);

        $form = new BackForm("Ref Mots-Cl&eacute;s", "text", "marque_nom_meta_kw");
        $form->addAttr('size', '100');
        $this->addForm($form);

        $form = new BackForm("Ref Autre", "tinymce", "marque_nom_meta_other");
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT m.marque_id, m.marque_id, mn.marque_nom_nom, CAST(m.marque_ref AS SIGNED), marque_statut_id";
        $sql .= ", CONCAT(COUNT(p.produit_id), ' article(s)')";
        $sql.=" FROM (marque m)";
        $sql .= " LEFT JOIN marque_nom mn ON(m.marque_id = mn.marque_id)";
        $sql .= " LEFT JOIN produit p ON(m.marque_id = p.marque_id)";
        $sql.=" WHERE 1";
        $this->request = $sql;
        //-- SQL
        // LABELS

        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("REF");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        $label = new BackLabel("NB PRODUIT");
        $this->addLabel($label);

        return $this->displayList($formvars['type']);
    }

//--
}