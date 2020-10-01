<?php

class bCms_page extends BackModule {

    function bCms_page($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        if (isset($formvars['parent_id']) && $formvars['parent_id'] == "NULL")
            unset($formvars['parent_id']);
        $id = parent::update_multi($formvars, $table, $langue);
        return $id;
    }

    function create($formvars = array(), $table = null, $langue = true) {
        if (isset($formvars['parent_id']) && $formvars['parent_id'] == "NULL")
            unset($formvars['parent_id']);
        $id =  parent::create_multi($formvars, $table, $langue);

        Boutique::getInstance()->createStatut('cms_page', $id);
        return $id;
    }

    function duplicate($formvars = array(), $table = null, $langue = true) {
       
        unset($formvars['page_nom_url']);
        
        $return = $this->create($formvars, $table, $langue);
        
        return $return;
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }
    

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Page</a></span>";
        else {
            $sql = "SELECT page_nom_nom as data FROM cms_page_nom WHERE cms_page_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la page : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT p.cms_page_id";
            $sql.=", p.cms_menu_id, p.parent_id, pn.page_nom_nom, pn.page_nom_image, pn.page_nom_url, p.formulaire_id";
            $sql.=", pn.page_nom_contenu, pn.page_nom_contenu_court, pn.page_nom_meta_title, pn.page_nom_meta_keyword, pn.page_nom_meta_desc, pn.page_nom_meta_other";
            $sql.=", p.page_rang, p.page_statut_id";
            $sql.=", pn.page_nom_fichier";
            $sql.=" FROM (cms_page p, cms_page_nom pn)";
            $sql.=" WHERE p.cms_page_id='" . $formvars['id'] . "'";
            $sql.=" AND p.cms_page_id = pn.cms_page_id";
            //echo $sql;exit();
            $this->request = $sql;
        }
        //-- SQL
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP
        // Champs du formulaire
        if (!isset($_GET['p*parent_id']) || empty($_GET['p*parent_id']) || $_GET['p*parent_id'] == 'NULL') {
            $form = new BackForm("Attach&eacute; au menu", "select", "cms_menu_id");
            $form->addAttr('class', 'required');
            $form->addOption("NULL", "---");
            $form->addOptionSQL(array("SELECT cms_menu_id, menu_nom_nom FROM cms_menu_nom"));
            $this->addForm($form);
        } else {

            $form = new BackForm("Page Parent", "select", "parent_id");
            $form->addOption("NULL", "---");
            $form->addOptionSQL(array("SELECT p.cms_page_id, pn.page_nom_nom FROM cms_page p, cms_page_nom pn WHERE p.parent_id IS NULL AND p.cms_page_id = pn.cms_page_id AND pn.langue_id ='" . LANGUE . "' ORDER BY page_nom_nom", false));
            $this->addForm($form);
        }

        $form = new BackForm("Nom", "text", "page_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        //$form = new BackForm("Image","image","page_nom_image");
        //$form->addAttr('class','required');
        //$this->addForm($form);

        $form = new BackForm("Url", "text", "page_nom_url");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Fichier", "file", "page_nom_fichier");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ouverture du formulaire", "select", "formulaire_id");
        $form->setVar("comment", "Si renseigné avec un formulaire valide, le lien ne sera pas pris en compte");
        $form->addOption("", " --- ");
        $form->addOptionSQL(array(" SELECT f.formulaire_id, CONCAT(b.boutique_nom,' - ', f.formulaire_nom) FROM formulaire as f INNER JOIN boutique as b ON b.boutique_id = f.boutique_id AND f.formulaire_date_debut_affichage <= NOW() AND f.formulaire_date_fin_affichage >= NOW() AND f.formulaire_actif = 1 ORDER BY boutique_nom, formulaire_nom "));
        $this->addForm($form);

        // $form = new BackForm("Icone", "text", "page_nom_image");
        // $this->addForm($form);

        $form = new BackForm("Contenu", "tinymce", "page_nom_contenu");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        //$form = new BackForm("Contenu Court","tinymce","page_nom_contenu_court");
        //$form->setVar('translate',true);
        //$this->addForm($form);

        $form = new BackForm("Rang", "text", "page_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "page_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT page_statut_id, page_statut_nom FROM cms_page_statut"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'cms_page_boutique');
        $this->addForm($form);

         //GROUP
         $form = new BackForm("R&eacute;f&eacute;rencement","group");
         $this->addForm($form);
         //--GROUP
         $form = new BackForm("Meta title","text","page_nom_meta_title");
         $form->setVar('translate',true);
         $this->addForm($form);
         $form = new BackForm("Meta keywords","text","page_nom_meta_keyword");
         $form->setVar('translate',true);
         $this->addForm($form);
         $form = new BackForm("Meta description","text","page_nom_meta_desc");
         $form->setVar('translate',true);
         $this->addForm($form);
         $form = new BackForm("Ref Autre","tinymce","page_nom_meta_other");
         $form->setVar('translate',true);
         $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Menu", "select", "p.cms_menu_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT cms_menu_id, menu_nom_nom FROM cms_menu_nom WHERE langue_id = " . LANGUE));
        $this->addForm($form);
        //-- RECHERCHE
        //echo $sql;
        $sql = " SELECT p.cms_page_id, p.cms_page_id, pn.page_nom_nom, mn.menu_nom_nom, mp.menu_position_nom, pn.page_nom_url, p.page_rang";
        //$sql.=", IF(p.parent_id IS NULL,COUNT(DISTINCT pe.cms_page_id),'--')";
        // $sql.=", IF(p.page_statut_id = 1, 'Oui', 'Non')";
        $sql.=", CONCAT('<img src=\"/back/styles/images/actif', p.page_statut_id, '.png\" />')";
        $sql.=" FROM (cms_page p)";
        $sql.=" LEFT JOIN cms_page_nom pn on (pn.cms_page_id = p.cms_page_id)";
        $sql.=" LEFT JOIN cms_page pe on (pe.parent_id = p.cms_page_id)";
        $sql.=" LEFT JOIN cms_menu_nom mn on (mn.cms_menu_id = p.cms_menu_id)";
        $sql.=" LEFT JOIN cms_menu cm on (mn.cms_menu_id = cm.cms_menu_id)";
        $sql.=" LEFT JOIN cms_menu_position mp on (cm.menu_position_id = mp.menu_position_id)";

        $sql.="WHERE 1";
        if (!isset($_GET['p*parent_id']) || empty($_GET['p*parent_id']) || $_GET['p*parent_id'] == 'NULL')
            $sql.=" AND p.parent_id IS NULL";
        if (!empty($formvars['menu']))
            $sql.=" AND p.menu_id = '" . $formvars['menu'] . "'";
        //echo $sql;

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("MENU");
        $this->addLabel($label);

        $label = new BackLabel("POSITION");
        $this->addLabel($label);

        $label = new BackLabel("URL");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);
        
        $label = new BackLabel("PUBLIE");
        $label->setVar('align', 'center');
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}