<?php

class bPays extends BackModule {

    function bPays($formvars = array()) {
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

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouveau Pays</a></span>";
        else {
            $sql = "SELECT pays_id as data FROM pays WHERE pays_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Pays  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('pays', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        

        $form = new BackForm("Nom", "text", "pays_nom_nom");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("ISO 2", "text", "pays_iso_code_2");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 2);
        $form->setVar('comment', 'Code ISO &agrave; deux lettres. ex: FR pour France');
        $this->addForm($form);

        $form = new BackForm("ISO 3", "text", "pays_iso_code_3");
        $form->addAttr('size', 3);
        $form->setVar('comment', 'Code ISO &agrave; trois lettres. ex: FRA pour France');
        $this->addForm($form);
        $form = new BackForm("Zone", "select", "zone_id");
        
        $form->addOption("NULL", "Aucune Zone");
        $form->addOptionSQL(array("SELECT z.zone_id, zn.zone_nom_nom FROM zone z, zone_nom zn WHERE z.zone_id = zn.zone_id AND zn.langue_id = '" . LANGUE . "'"));
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Nom", "text", "pn.pays_nom_nom");
        $this->addForm($form);

        $form = new BackForm("ISO 2", "text", "p.pays_iso_code_2");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("ISO 3", "text", "p.pays_iso_code_3");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);


        $form = new BackForm("Zone", "select", "p.zone_id");
        $form->addOption("", "---");
        $form->addOption("NULL", "Aucune Zone");
        $form->addOptionSQL(array("SELECT z.zone_id, zn.zone_nom_nom FROM zone_nom zn, zone z WHERE z.zone_id = zn.zone_id AND zn.langue_id = '" . LANGUE . "' GROUP BY z.zone_id ORDER BY zone_nom_nom"));
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT p.pays_id, p.pays_id, pn.pays_nom_nom, p.pays_iso_code_2, p.pays_iso_code_3,zn.zone_nom_nom";
        $sql.=" FROM (pays p, langue l)";
        $sql.=" LEFT JOIN zone_nom zn ON (p.zone_id = zn.zone_id AND zn.langue_id = '".LANGUE."')";
        $sql.=" LEFT JOIN pays_nom pn ON (p.pays_id = pn.pays_id AND pn.langue_id = '" . LANGUE . "')";
        $sql.=" WHERE 1";



        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);


        $label = new BackLabel("ISO2");
        $this->addLabel($label);
        $label = new BackLabel("ISO3");
        $this->addLabel($label);
        $label = new BackLabel("ZONE");
        $this->addLabel($label);




        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS
        

        return $this->displayList($formvars['type']);
    }

//--
}
?>