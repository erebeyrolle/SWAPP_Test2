<?php

/**
 * Created by PhpStorm.
 * User: Yannick
 * Date: 28/08/2015
 * Time: 17:55
 */
class bCarte extends BackModule
{

    function bCarte($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    function Form($formvars = array())
    {
        // SQL
        if (!empty($formvars['id'])) {
            $sql="SELECT c.*";
            $sql.=" FROM carte c";
            $sql.=" WHERE c.carte_id='".$formvars['id']."'";
            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Numero de carte", "text", "carte_numero");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Client", "select", "client_id");
        $form->addAttr('class', 'required');
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT client_id, CONCAT(client_nom, ' ', client_prenom) FROM (client) WHERE 1 AND client_nom <> '' ORDER BY client_nom"));
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addAttr('class', 'required');
        $form->addAttr('onchange', 'loadBoutiqueContrat(jQuery(this))');
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT DISTINCT(b.boutique_id), b.boutique_nom FROM (boutique b) LEFT JOIN boutique_contrat bc ON bc.boutique_id = b.boutique_id WHERE bc.boutique_contrat_date_fin >= NOW() AND boutique_nom <> '' ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("Contrat", "select", "boutique_contrat_id");
        $form->addAttr('class', 'required');
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT boutique_contrat_id, 
                                          CONCAT('(#', b.boutique_id, ' ' , b.boutique_nom , ') ', boutique_contrat_reference) 
                                          FROM boutique_contrat bc
                                          LEFT JOIN boutique b ON b.boutique_id = bc.boutique_id 
                                          WHERE bc.boutique_contrat_date_fin >= NOW() ORDER BY bc.boutique_id, bc.boutique_contrat_reference"));
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "carte_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT carte_statut_id, carte_statut_nom FROM (carte_statut) ORDER BY carte_statut_id"));
        $this->addForm($form);

        $form = new BackForm("Date de création", "date", "carte_creation_date");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Utilisable à partir du ", "date", "carte_use_min_date");
        $this->addForm($form);

        $form = new BackForm("Et à Jusqu'au ", "date", "carte_use_max_date");
        $this->addForm($form);

        $form = new BackForm("Date d'utilisation", "date", "carte_utilisation_date");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;

        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Client", "select", "c*client_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT client_id, CONCAT(client_nom, ' ', client_prenom) FROM (client) WHERE 1 AND client_nom <> '' ORDER BY client_nom"));
        $this->addForm($form);

        $form = new BackForm("N°", "text", "c*carte_numero");
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "c*boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT DISTINCT(b.boutique_id), b.boutique_nom FROM (boutique b) LEFT JOIN boutique_contrat bc ON bc.boutique_id = b.boutique_id WHERE bc.boutique_contrat_date_fin >= NOW() AND boutique_nom <> '' ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "c*carte_statut_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT carte_statut_id, carte_statut_nom FROM (carte_statut) ORDER BY carte_statut_id"));
        $this->addForm($form);

        //-- RECHERCHE
        // SQL
        $sql = "SELECT c.carte_id, c.carte_id, b.boutique_nom, bc.boutique_contrat_reference, c.carte_numero";
        $sql .= ", c.carte_creation_date, CONCAT(c.carte_use_min_date, ' au ', c.carte_use_max_date), c.carte_utilisation_date, cs.carte_statut_nom";
        $sql.=" FROM (carte c, carte_statut cs)";
        $sql.=" LEFT JOIN boutique b ON b.boutique_id = c.boutique_id";
        $sql.=" LEFT JOIN boutique_contrat bc ON bc.boutique_contrat_id = c.boutique_contrat_id";
        $sql.=" WHERE 1";
        $sql.=" AND c.carte_statut_id = cs.carte_statut_id";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("CONTRAT");
        $this->addLabel($label);

        $label = new BackLabel("NUMERO");
        $this->addLabel($label);

        $label = new BackLabel("DATE DE CREATION");
        $this->addLabel($label);

        $label = new BackLabel("PERIODE D'UTILISATION");
        $this->addLabel($label);

        $label = new BackLabel("DATE D'UTILISATION");
        $this->addLabel($label);


        $label = new BackLabel("STATUT");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}