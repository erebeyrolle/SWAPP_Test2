<?php

class bCode_inscription extends BackModule {

    function bCode_inscription($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array()) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['code_inscription_valeur'] = $taxe->getPrixHt($formvars['code_inscription_valeur'], $formvars['taxe_id']);
        $formvars['code_inscription_montant_min'] = $taxe->getPrixHt($formvars['code_inscription_montant_min'], $formvars['taxe_id']);

        return parent::update($formvars);
    }

    function create($formvars = array()) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['code_inscription_valeur'] = $taxe->getPrixHt($formvars['code_inscription_valeur'], $formvars['taxe_id']);
        $formvars['code_inscription_montant_min'] = $taxe->getPrixHt($formvars['code_inscription_montant_min'], $formvars['taxe_id']);

        return parent::create($formvars);
    }


    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Code d'inscription</a></span>";
        else {
            $sql = "SELECT code_inscription_nom as data FROM code_inscription WHERE code_inscription_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du code inscription : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {

            $sql = "SELECT *";
            $sql.=", ROUND(code_inscription_valeur * (1 + taxe_taux),2) as code_inscription_valeur";
            $sql.=", ROUND(code_inscription_montant_min * (1 + taxe_taux),2) as code_inscription_montant_min";
            $sql.=" FROM (code_inscription ci, taxe t)";
            $sql.=" WHERE ci.code_inscription_id = '" . $formvars['id'] . "'";
            $sql.=" AND t.taxe_id = ci.taxe_id";

            $this->request = $sql;

            //$this->request = getTable('code_inscription', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire

        $form = new BackForm("Identification", "group");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "code_inscription_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Code", "text", "code_inscription_code");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Code a renseign&eacute; par l'internaute lors de son inscription");
        $this->addForm($form);
        

        $form = new BackForm("Validit&eacute;", "group");
        $this->addForm($form);
        
        $form = new BackForm("Nb utilisation", "text", "code_inscription_utilisation");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Nombre maximum d'utilisation de ce code (-1 = illimit&eactue;)");
        $this->addForm($form);

        $form = new BackForm("Date début", "datetime", "code_inscription_debut");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Date fin", "datetime", "code_inscription_fin");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Bon d'achat", "group");
        $this->addForm($form);

        $form = new BackForm("Montant", "text", "code_inscription_valeur");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Ce montant sera revers&eacute; au membre sous la forme d'un bon d'achat");
        $this->addForm($form);

        $form = new BackForm("Montant min d'achat", "text", "code_inscription_montant_min");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Pr&eacute;ciser le montant a partir duquel le BA est utilisable.");
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe)"));
        $this->addForm($form);

        $form = new BackForm("Validit&eacute; (en jours)", "text", "code_inscription_nbjour");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Validit&eacute; du bon d'achat revers&eacute; &agrave; l'internaute");
        $form->addAttr('size', 20);
        $this->addForm($form);

        
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT ci.code_inscription_id, ci.code_inscription_id";
        $sql.=", ci.code_inscription_nom, ci.code_inscription_code";
        $sql.=", ci.code_inscription_utilisation, ci.code_inscription_debut, ci.code_inscription_fin";
        $sql.=", ROUND(ci.code_inscription_valeur * (1 + taxe_taux),2)";
        $sql.=", ROUND(ci.code_inscription_montant_min * (1 + taxe_taux),2), ci.code_inscription_nbjour";
        $sql.=" FROM (code_inscription ci, taxe t)";
        $sql.=" WHERE ci.taxe_id = t.taxe_id";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("CODE");
        $this->addLabel($label);

        $label = new BackLabel("NB UTILISATION");
        $this->addLabel($label);
        $label = new BackLabel("DATE DEBUT","date");
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);
        $label = new BackLabel("DATE FIN","date");
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);


        $label = new BackLabel("MONTANT TTC",'devise');
        $this->addLabel($label);

        $label = new BackLabel("MONTANT MIN ACHAT TTC",'devise');
        $this->addLabel($label);

        $label = new BackLabel("VALIDITE (J)");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>