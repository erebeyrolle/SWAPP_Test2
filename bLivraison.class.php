<?php

class bLivraison extends BackModule {

    function bLivraison($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true)
    {
        return parent::update_multi($formvars);
    }

    function create($formvars = array(), $table = null, $langue = true)
    {
        $id = parent::create_multi($formvars);
        Boutique::getInstance()->createStatut('livraison', $id);
        return $id;
    }

    function delete($formvars = array())
    {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau mode de livraison</a></span>";
        else {
            $sql = "SELECT livraison_id as data FROM livraison WHERE livraison_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du mode de livraison  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('livraison', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire

        //GROUP
        $form = new BackForm("Informations Principales", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom", "text", "livraison_nom_nom");
        $form->addAttr('class', 'required');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "livraison_rang");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);
        
        $form = new BackForm("Statut", "select", "livraison_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT livraison_statut_id, livraison_statut_nom FROM livraison_statut ORDER BY livraison_statut_id"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'livraison_boutique');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Parametrages", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Code", "text", "livraison_code");
        $this->addForm($form);
        
        $form = new BackForm("Icone", "image", "livraison_icon");
        $this->addForm($form);
        
        $form = new BackForm("Url de suivi", "text", "livraison_suivi");
        $form->setVar('comment', 'Utiliser le tag %COLIS% dans l\'url');
        $form->addAttr('size', '100');
        $this->addForm($form);
        $form = new BackForm("Delai Min", "text", "livraison_delai_min");
        $this->addForm($form);

        $form = new BackForm("Delai Max", "text", "livraison_delai_max");
        $this->addForm($form);

        $form = new BackForm("Prix MIN HT", "text", "livraison_prix_min");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Prix MAX HT", "text", "livraison_prix_max");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Informations Secondaires", "group");
        $this->addForm($form);
        //--GROUP
        
        $form = new BackForm("Plus d'informations", "textarea", "livraison_nom_desc");
        $form->setVar('translate', true);
        $this->addForm($form);

        

        
/*
        

        

        $form = new BackForm("Zones Gratuites", "group");
        $form->addGroupOpts('class', 'livraison_gratuit');
        $this->addForm($form);

        $form = new BackForm("Zones Payantes", "group");
        $form->addGroupOpts('class', 'livraison_zone');
        $this->addForm($form);*/

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT l.livraison_id, l.livraison_id, ln.livraison_nom_nom, livraison_code";
        $sql.=", COUNT(DISTINCT z.zone_id) AS nb_zones";
        $sql.=", COUNT(DISTINCT g.zone_id) AS nb_zones_g";
        $sql .= ", l.livraison_prix_min, l.livraison_prix_max";

        $sql.=", livraison_rang, livraison_statut_id";
        $sql.=" FROM (livraison l, livraison_nom ln)";
        $sql.=" LEFT JOIN livraison_zone z ON (z.livraison_id = l.livraison_id)";
        $sql.=" LEFT JOIN livraison_gratuit g ON (g.livraison_id = l.livraison_id)";
        $sql.=" WHERE 1";
        $sql.=" AND l.livraison_id = ln.livraison_id";
        $sql.=" AND ln.langue_id = '" . LANGUE . "'";


        //echo $sql;
        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("CODE");
        $this->addLabel($label);

        $label = new BackLabel("NB ZONES PAYANTES", "listselect");
        $label->setVar('option', array("module" => "<a href='../../livraison_zone/list/?p*livraison_id=%ID%'>%IMG%</a>"));
        //$label->setVar('option', array("group" => "livraison_zone"));
        $this->addLabel($label);

        $label = new BackLabel("NB ZONES GRATUITES", "listselect");
        $label->setVar('option', array("module" => "<a href='../../livraison_gratuit/list/?p*livraison_id=%ID%'>%IMG%</a>"));
        //$label->setVar('option', array("group" => "livraison_gratuit"));
        $this->addLabel($label);

        $label = new BackLabel("PRIX MIN", "devise");
        $this->addLabel($label);

        $label = new BackLabel("PRIX MAX", "devise");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}