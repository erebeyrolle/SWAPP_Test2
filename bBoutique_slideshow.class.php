<?php

/**
 * Created by PhpStorm.
 * User: Yannick
 * Date: 13/11/2015
 * Time: 15:05
 */
class bBoutique_slideshow extends BackModule
{
    public function __construct($formvars = array())
    {
        parent::BackModule($formvars);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $return = parent::create($formvars, $table, $langue);

        return $return;
    }

    function update($formvars = array(), $table = null, $langue = true) {
        $return = parent::update($formvars, $table, $langue);

        return $return;
    }

    public function Form($formvars = array())
    {
        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=" FROM (boutique_slideshow)";
            $sql.=" WHERE boutique_slideshow_id='" . $formvars['id'] . "'";

            $this->request = $sql;

            $data_boutique = $this->sql->getOne($sql);
        }

        //-- SQL
        // Champs du formulaire
        if (!empty($formvars['boutique_id'])) {
            $form = new BackForm("Boutique ID", "hidden", "boutique_id");
            $this->addForm($form);
        }

        $form = new BackForm("Lien externe", "text", "boutique_slideshow_url");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Image", "image", "boutique_slideshow_image");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 1920x512px');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Nouvelle fenêtre ?", "select", "boutique_slideshow_target");
        $form->addOption("_self", "non");
        $form->addOption("_blank", "oui");
        $this->addForm($form);

        if (!empty($formvars['boutique_id']) || !empty($data_boutique["boutique_id"])) {
            $form = new BackForm("Ouverture du formulaire", "select", "formulaire_id");
            $form->setVar("comment", "Si renseigné avec un formulaire valide, le lien ne sera pas pris en compte");
            $form->addOption("", " --- ");
            $form->addOptionSQL(array(" SELECT formulaire_id, formulaire_nom FROM formulaire where boutique_id = ".(!empty($formvars['boutique_id'])?$formvars['boutique_id']:$data_boutique['boutique_id'])." AND formulaire_date_debut_affichage <= NOW() AND formulaire_date_fin_affichage >= NOW() AND formulaire_actif = 1 "));
            $this->addForm($form);
        }

        $form = new BackForm("Rang", "text", "boutique_slideshow_rang");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 10);
        $this->addForm($form);

        $form = new BackForm("Date debut", "date", "boutique_slideshow_date_debut");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Date fin", "date", "boutique_slideshow_date_fin");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Statut","select","boutique_statut_id");
        $form->addOptionSQL("SELECT boutique_statut_id, boutique_statut_nom FROM boutique_statut ORDER BY boutique_statut_id");
        $form->addAttr('class','required');
        $this->addForm($form);
        

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    public function Listing($formvars = array())
    {
        // SQL
        $sql = "SELECT bs.boutique_slideshow_id";
        $sql.=", bs.boutique_slideshow_id";
        $sql.=", bs.boutique_slideshow_url";
        $sql.=", bs.boutique_slideshow_image";
        $sql.=", bs.boutique_slideshow_rang";
        $sql.=", bss.boutique_statut_id";
        $sql.=" FROM (boutique_slideshow bs)";
        $sql.=" LEFT JOIN boutique_statut bss ON (bs.boutique_statut_id = bss.boutique_statut_id)";
        $sql.=" WHERE 1";
        if (!empty($formvars['boutique'])) {
            $sql.=" AND bs.boutique_id = '" . $formvars['boutique'] . "'";
        } else {
            $sql.=" AND bs.boutique_id IS NULL";
        }
        $this->request = $sql;

        //-- SQL
        // LABELS

        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("LIEN EXTERNE");
        $this->addLabel($label);

        $label = new BackLabel("IMAGE", 'image');
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", 'statut');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}