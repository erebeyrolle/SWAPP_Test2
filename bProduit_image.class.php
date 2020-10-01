<?php
class bProduit_image extends BackModule {

    function bProduit_image($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {

        $source = str_replace(SITE_URL, SITE_DIR, $formvars['produit_image']);

        $formvars['produit_image_zoom'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 800, 800, 'img'));
        $formvars['produit_image_liste'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 186, 186, 'img'));
        $formvars['produit_image_detail'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 400, 400, 'img'));
        $formvars['produit_image_special'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 140, 140, 'img'));
        $formvars['produit_image_mini'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 97, 97, 'img'));

        $return = parent::update($formvars, $table, $langue);

        if (!isset($formvars['back_default']))
            $formvars['back_default'] = "";
        $this->setDefault($this->name_url, 'produit', $formvars['back_id'], $formvars['back_default']);

        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {

        $source = str_replace(SITE_URL, SITE_DIR, $formvars['produit_image']);


        $formvars['produit_image_zoom'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 800, 800, 'img'));
        $formvars['produit_image_liste'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 186, 186, 'img'));
        $formvars['produit_image_detail'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 400, 400, 'img'));
        $formvars['produit_image_special'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 140, 140, 'img'));
        $formvars['produit_image_mini'] = str_replace(SITE_DIR, SITE_URL, redimImage($source, 97, 97, 'img'));

        $return = parent::create($formvars, $table, $langue);

        if (!isset($formvars['back_default']))
            $formvars['back_default'] = "";
        $this->setDefault($this->name_url, 'produit', $return, $formvars['back_default']);

        return $return;
    }

    function delete($formvars = array()) {
        $zeID = $this->getValue($this->name_url, 'produit_id', $this->name_url . '_id', $formvars['back_id']);
        $return = parent::delete($formvars);

        $this->setDefault($this->name_url, 'produit', $zeID, NULL);

        return $return;
    }

    function Form($formvars = array()) {
        
        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *, produit_image_default as back_default";
            $sql.=" FROM (produit_image)";
            $sql.=" WHERE produit_image_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        if (!empty($formvars['id'])) {
            $form = new BackForm("Attribut", "select", "produit_attribut_id");
            $form->addOption("NULL", "---");
            $form->addOptionSQL(array("
            SELECT pa.produit_attribut_id, IF(povnbis.produit_option_valeur_nom != '' && povn.produit_option_valeur_nom != '', CONCAT(povn.produit_option_valeur_nom,' & ',povnbis.produit_option_valeur_nom), IF(povnbis.produit_option_valeur_nom != '', povnbis.produit_option_valeur_nom, povn.produit_option_valeur_nom)) as produit_option_valeur_nom
            FROM (produit_attribut pa, produit_image pi)
            LEFT JOIN produit_option_valeur_nom povn ON (pa.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . $_SESSION['langue_id'] . "')
            LEFT JOIN produit_option_valeur_nom povnbis ON (pa.produit_option_valeur_id_bis = povnbis.produit_option_valeur_id AND povnbis.langue_id='" . $_SESSION['langue_id'] . "')
            WHERE pi.produit_id = pa.produit_id AND pi.produit_image_id = '" . $formvars['id'] . "'
        "));
            $this->addForm($form);
        }

        $form = new BackForm("Nom", "text", "produit_image_nom");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Image", "image", "produit_image");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "produit_image_rang");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 10);
        $this->addForm($form);

        $form = new BackForm("Par d&eacute;faut", "checkbox", "back_default");
        $form->setVar('comment', 'Cochez cette case pour d&eacute;finir cette image comment &eacute;tant celle par d&eacute;faut.');
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pi.produit_image_id";
        $sql.=", pi.produit_image_id";
        $sql.=", pi.produit_image_nom";
        $sql.=", IF(povnbis.produit_option_valeur_nom != '' && povn.produit_option_valeur_nom != '', CONCAT(povn.produit_option_valeur_nom,' & ',povnbis.produit_option_valeur_nom), IF(povnbis.produit_option_valeur_nom != '', povnbis.produit_option_valeur_nom, povn.produit_option_valeur_nom)) as produit_option_valeur_nom";
        $sql.=", pi.produit_image";
        $sql.=", pi.produit_image_rang";
        $sql.=", pi.produit_image_default";
        $sql.=" FROM (produit_image pi)";
        $sql.=" LEFT JOIN produit_attribut pa ON (pi.produit_attribut_id = pa.produit_attribut_id)";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn ON (pa.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . $_SESSION['langue_id'] . "')";
        $sql.=" LEFT JOIN produit_option_valeur_nom povnbis ON (pa.produit_option_valeur_id_bis = povnbis.produit_option_valeur_id AND povnbis.langue_id='" . $_SESSION['langue_id'] . "')";

        $sql.=" WHERE 1";
        if (!empty($formvars['produit']))
            $sql.=" AND pi.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("ATTRIBUT");
        $this->addLabel($label);

        $label = new BackLabel("IMAGE", 'image');
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("DEFAUT", 'default');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}