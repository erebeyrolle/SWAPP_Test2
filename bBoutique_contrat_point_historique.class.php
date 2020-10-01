<?php

class bboutique_contrat_point_historique extends BackModule {

    function bboutique_contrat_point_historique($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;
    }

    function update($formvars = array(), $table = null, $langue = true) {
        $return = parent::update($formvars,$table,$langue);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $return = parent::create($formvars,$table,$langue);
        return $return;
    }

    function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (boutique_contrat_point_historique bcph)";

            $sql.= " WHERE 1";
            $sql.= " AND bcph.boutique_contrat_point_historique_id = '" . $formvars['id'] . "'";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("boutique ID", "hidden", "boutique_id");
        $this->addForm($form);

        $form = new BackForm('Boutique point', 'text', 'boutique_contrat_point_historique_poin');
        $this->addForm($form);

        $form = new BackForm('Boutique point date ajout', 'text', 'boutique_contrat_point_historique_date_ajout');
        $this->addForm($form);



        // $form->addAttr('class', 'required');
        // $form->addOption("", "---");
        // $form->addOptionSQL(array("SELECT bcph.boutique_contrat_point_historique_id, bcph.boutique_contrat_point_historique_nom_nom FROM boutique_contrat_point_historique bcph"));
        // $form->addOptionSQL(array("(SELECT '2', 'Non') UNION (SELECT '1', 'Oui')"));

        //  $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm('# ID', 'text', 'bcph.boutique_contrat_point_historique_id');
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);

        $sql = "SELECT 
                  bcph.boutique_contrat_point_historique_id,
                  bcph.boutique_contrat_point_historique_id, 
                  bcph.boutique_contrat_point_historique_point, 
                  IF(bcph.boutique_contrat_point_historique_type = '" . Points::CREDIT . "', 'Crédit', 'Débit'), 
                  COALESCE(bcph.boutique_contrat_point_historique_libelle, '--'), 
                  bcph.boutique_contrat_point_historique_date_ajout
                FROM (boutique_contrat_point_historique bcph)
         WHERE 1";
        if (!empty($formvars['boutique_contrat']))
            $sql.=" AND bcph.boutique_contrat_id = '" . $formvars['boutique_contrat'] . "'";
        $this->request = $sql;

        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('POINTS');
        $this->addLabel($list);

        $list = new BackLabel('TYPE');
        $this->addLabel($list);

        $list = new BackLabel('COMMENTAIRE');
        $this->addLabel($list);

        $list = new BackLabel('DATE', 'date');
        $list->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($list);



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}