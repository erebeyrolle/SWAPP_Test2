<?php
class bAds extends BackModule {

    function bAds($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        /*
        $source = str_replace(SITE_URL, SITE_DIR, $formvars['ads_nom_image']);
        $formvars['ads_nom_image'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 932, 273, 'img'));
        $formvars['ads_nom_image_redim'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 220, 65, 'img'));
        */
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        /*
        $source = str_replace(SITE_URL, SITE_DIR, $formvars['ads_nom_image']);
        $formvars['ads_nom_image'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 932, 273, 'img'));
        $formvars['ads_nom_image_redim'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 220, 65, 'img'));
        */
        $id = parent::create_multi($formvars, $table, $langue);
        Boutique::getInstance()->createStatut('ads', $id);
        return $id;
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Pub</a></span>";
        else {
            $sql = "SELECT ads_nom_nom as data FROM ads_nom WHERE ads_id = '" . $formvars['id'] . "' AND langue_id = '".LANGUE."'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la Pub  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=" FROM (ads a, ads_nom an)";

            $sql.=" WHERE a.ads_id='" . $formvars['id'] . "'";
            $sql.=" AND an.ads_id = a.ads_id AND an.langue_id = '" . LANGUE . "'";
            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Emplacement", "select", "ads_emplacement_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT ads_emplacement_id, ads_emplacement_nom FROM ads_emplacement"));
        $this->addForm($form);        
        
        $form = new BackForm("Nom", "text", "ads_nom_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);
        
        $form = new BackForm("Image", "image", "ads_nom_image");
        $this->addForm($form);

        $form = new BackForm("Lien", "text", "ads_nom_texte");
        $form->addAttr('size', 100);
        $this->addForm($form);

        $form = new BackForm("Date de d&eacute;but", "datetime", "ads_date_debut");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Date de fin", "datetime", "ads_date_fin");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Actif", "select", "actif_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT actif_id, actif_nom FROM actif"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'ads_boutique');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Emplacement", "select", "a.ads_emplacement_id");
        $form->setVar("compare", "EQUAL");
        $form->addOption("", "---");        
        $form->addOptionSQL(array("SELECT ads_emplacement_id, ads_emplacement_nom FROM ads_emplacement"));
        $this->addForm($form);
        // ! RECHERCHE

        $sql = "SELECT a.ads_id, a.ads_id, ae.ads_emplacement_nom, an.ads_nom_nom,  a.ads_date_debut, a.ads_date_fin";
        $sql.=", GROUP_CONCAT(b.boutique_nom)";
        $sql.=", a.actif_id";
        $sql.=" FROM (ads a)";
        $sql.=" LEFT JOIN ads_nom an on (a.ads_id = an.ads_id AND an.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN ads_emplacement ae ON (ae.ads_emplacement_id = a.ads_emplacement_id)";
        $sql.=" LEFT JOIN ads_boutique ab ON (a.ads_id = ab.ads_id)";
        $sql.=" LEFT JOIN boutique b ON (ab.boutique_id = b.boutique_id)";
        $sql.=" WHERE 1";


        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("EMPLACEMENT");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("DATE DE DEBUT", "date");
        $label->setVar('option', array('%d-%m-%Y %H:%i:%s'));
        $this->addLabel($label);


        $label = new BackLabel("DATE DE FIN", "date");
        $label->setVar('option', array('%d-%m-%Y %H:%i:%s'));
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        /*$label = new BackLabel("STATUT", 'statut_boutique_listing');
        $this->addLabel($label);*/

        $label = new BackLabel("ACTIF", 'actif');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}