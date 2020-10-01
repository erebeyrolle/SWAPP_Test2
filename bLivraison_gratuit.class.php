<?php

class bLivraison_gratuit extends BackModule {

    function bLivraison_gratuit($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['livraison_gratuit_prix'] = $taxe->getPrixHt($formvars['livraison_gratuit_prix'], $formvars['taxe_id']);
        
        return parent::update($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['livraison_gratuit_prix'] = $taxe->getPrixHt($formvars['livraison_gratuit_prix'], $formvars['taxe_id']);

        return parent::create($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Zone Gratuite</a></span>";
        else {
            $sql = "SELECT livraison_gratuit_id as data FROM livraison_gratuit WHERE livraison_gratuit_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la Zone Gratuite : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=", ROUND((lg.livraison_gratuit_prix * (1 + t.taxe_taux)), 2) as livraison_gratuit_prix";
            $sql.=" FROM (livraison_gratuit lg, taxe t)";
            $sql.=" WHERE livraison_gratuit_id='" . $formvars['id'] . "'";
            $sql.=" AND t.taxe_id = lg.taxe_id";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire


         $form = new BackForm("Livraison", "select", "livraison_id");
        $form->addOptionSQL(array("SELECT ln.livraison_id, CONCAT(l.livraison_code,' - ',ln.livraison_nom_nom) FROM (livraison l,livraison_nom ln) WHERE l.livraison_id = ln.livraison_id AND ln.langue_id = '" . LANGUE . "' ORDER BY livraison_rang"));
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addOption("NULL", "Toutes les boutiques");
        $form->addOptionSQL(array("SELECT b.boutique_id, b.boutique_nom FROM (boutique b) ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("Zone", "select", "zone_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT z.zone_id, zn.zone_nom_nom FROM (zone z, zone_nom zn) WHERE z.zone_id = zn.zone_id AND zn.langue_id = '" . LANGUE . "' ORDER BY zone_rang"));
        $this->addForm($form);

        $form = new BackForm("Panier TTC", "text", "livraison_gratuit_prix");
        $form->setVar('comment', 'D&eacute;finit le montant TTC du panier &agrave; atteindre pour obtenir les frais de port gratuit');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe) ORDER BY taxe_rang"));
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        $form = new BackForm("Livraison", "select", "lg.livraison_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT ln.livraison_id, CONCAT(l.livraison_code,' - ',ln.livraison_nom_nom) FROM (livraison l,livraison_nom ln) WHERE l.livraison_id = ln.livraison_id AND ln.langue_id = '" . LANGUE . "' ORDER BY livraison_rang"));
        $this->addForm($form);

        $form = new BackForm("Zone", "select", "lg.zone_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT ln.zone_id, ln.zone_nom_nom FROM (zone l,zone_nom ln) WHERE l.zone_id = ln.zone_id AND ln.langue_id = '" . LANGUE . "' ORDER BY zone_rang"));
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "lg.boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_id"));
        $this->addForm($form);

        // SQL
        $sql = "SELECT lg.livraison_gratuit_id, lg.livraison_gratuit_id";
        $sql.=", CONCAT(l.livraison_code,' - ',ln.livraison_nom_nom)";
        $sql.=", IF(lg.boutique_id IS NULL, 'Toutes les boutiques', b.boutique_nom)";
        $sql.=", zn.zone_nom_nom";
        $sql.=", ROUND((lg.livraison_gratuit_prix * (1 + t.taxe_taux)), 2)";
        $sql.=" FROM (livraison_gratuit lg, zone z, taxe t, livraison l, livraison_nom ln)";
        $sql.=" LEFT JOIN zone_nom zn ON (z.zone_id = zn.zone_id AND zn.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN boutique b ON (lg.boutique_id = b.boutique_id)";
        $sql.=" WHERE z.zone_id = lg.zone_id";
        $sql.=" AND t.taxe_id = lg.taxe_id";
        $sql.=" AND l.livraison_id = lg.livraison_id";
         $sql.=" AND ln.livraison_id = l.livraison_id AND ln.langue_id = '".LANGUE."'";
       

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("LIVRAISON");
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("ZONE");
        $this->addLabel($label);

        $label = new BackLabel("PANIER TTC", "devise");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}