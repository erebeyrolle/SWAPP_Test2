<?php

class bClient_point extends BackModule
{

    public function bClient_point($formvars = array())
    {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
    }

    public function update($formvars = array(), $table = null, $langue = true)
    {
        $return = parent::update($formvars, $table, $langue);
        return $return;
    }

    public function create($formvars = array(), $table = null, $langue = true)
    {
        $return = parent::create($formvars, $table, $langue);
        return $return;
    }

    public function delete($formvars = array())
    {
        $q_client_point = " SELECT * FROM `client_point` WHERE `client_point_id` = ".$formvars["id"]." ";
        $tab_client_point = $this->sql->getOne($q_client_point);

        if(empty($tab_client_point)){
            // La ligne n'a pas été trouvé
            return false;
        }else{
            // Les débit de points ne sont pas supprimable
            if($tab_client_point["client_point_type"] == 'D'){
                return false;
            }
            // Le client ne peut pas avoir un solde de point négatif
            $q_solde_client = " SELECT SUM( client_point_point ) as client_solde_point FROM `client_point` WHERE `client_id` = ".$tab_client_point["client_id"];
            $tab_solde_client_point = $this->sql->getOne($q_solde_client);
            if(($tab_solde_client_point["client_solde_point"] - $tab_client_point["client_point_point"]) < 0){
                return false;
            }
            return parent::delete($formvars);
        }
    }

    public function Listing($formvars = array())
    {
        // Affichage en sous module
        if(!empty($formvars['client']) || !empty($formvars['annonceur']) || !empty($formvars['boutique_contrat'])) {
            // RECHERCHE
            $form = new BackForm('# ID', 'text', 'cph.client_point_historique_id');
            $form->addAttr('size', 20);
            $form->setVar('compare', 'EQUAL');
            $this->addForm($form);

            $sql = "SELECT 
                      DISTINCT(cp.client_point_id),
                      cp.client_point_id, 
                      cp.client_point_point, 
                      IF(cp.client_point_type = '" . Points::CREDIT . "', 'Crédit', 'Débit'), 
                      COALESCE(cp.client_point_titre, '--'), 
                     cp.client_point_date
                    FROM (client_point cp)
             WHERE 1";
            if (!empty($formvars['client']))
                $sql .= " AND cp.client_id = '" . $formvars['client'] . "'";
            if (!empty($formvars['annonceur']))
                $sql .= " AND cp.client_id = '" . $formvars['annonceur'] . "'";
            if (!empty($formvars['boutique_contrat']))
                $sql .= " AND cp.boutique_contrat_id = '" . $formvars['boutique_contrat'] . "'";
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
        }else{
            // Affichage en module principal
            // RECHERCHE
            $form = new BackForm('Boutique', 'select', 'b.boutique_id');
            $form->addOption("", "");
            $form->addOptionSQL(array(" SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_nom "));
            $this->addForm($form);

            $form = new BackForm('Contrat', 'select', 'cp.boutique_contrat_id');
            $form->addOption("", "");
            $form->addOptionSQL(array(" SELECT bc.boutique_contrat_id, CONCAT(b.boutique_nom,' - ',bc.boutique_contrat_reference) FROM boutique_contrat as bc INNER JOIN boutique b ON b.boutique_id = bc.boutique_id ORDER BY b.boutique_nom, bc.boutique_contrat_reference "));
            $this->addForm($form);

            $form = new BackForm('# Client', 'text', 'c.client_id');
            $this->addForm($form);

            $form = new BackForm("Date", "between", "client_point_date");
            $this->addForm($form);

            $form = new BackForm("Type", "select", "client_point_type");
            $form->addOption("", "");
            $form->addOption("C", "Crédit");
            $form->addOption("D", "Débit");
            $this->addForm($form);

            $sql = "SELECT 
                      DISTINCT(cp.client_point_id),
                      cp.client_point_id,
                      b.boutique_nom,
                      bc.boutique_contrat_reference,
                      bct.type,
                      CONCAT(c.client_nom, ' ', c.client_prenom, ' - ', c.client_email) as client,
                      cp.client_point_point, 
                      IF(cp.client_point_type = '" . Points::CREDIT . "', 'Crédit', 'Débit'), 
                      cp.client_point_date
                    FROM (client_point cp)
                    LEFT JOIN client as c ON c.client_id = cp.client_id 
                    LEFT JOIN boutique as b ON b.boutique_id = c.boutique_id
                    LEFT JOIN boutique_contrat as bc ON bc.boutique_contrat_id = cp.boutique_contrat_id
                    LEFT JOIN boutique_contrat_type bct ON (bct.id = bc.boutique_contrat_type_id)
             WHERE cp.client_point_point != 0 ";
/*
            if (!empty($formvars['b.boutique_id']))
                $sql .= " AND b.boutique_id = '" . $formvars['b.boutique_id'] . "'";
            if (!empty($formvars['client_id']))
                $sql .= " AND cp.client_id = '" . $formvars['client_id'] . "'";
            if (!empty($formvars['client_point_date'][0]) && !empty($formvars['client_point_date'][1]))
                $sql .= " AND cp.client_point_date BETWEEN '" . $formvars['client_point_date'][0] . "' AND '".$formvars['client_point_date'][1]."' ";
            if (!empty($formvars['client_point_type']))
                $sql .= " AND cp.client_point_type = '" . $formvars['client_point_type'] . "'";*/
            $this->request = $sql;

            // LABELS
            $label = new BackLabel("ID");
            $this->addLabel($label);

            $list = new BackLabel('BOUTIQUE');
            $this->addLabel($list);

            $list = new BackLabel('CONTRAT');
            $this->addLabel($list);

            $list = new BackLabel('TYPE CONTRAT');
            $this->addLabel($list);

            $list = new BackLabel('CLIENT');
            $this->addLabel($list);

            $list = new BackLabel('POINTS');
            $this->addLabel($list);

            $list = new BackLabel('TYPE');
            $this->addLabel($list);

            $list = new BackLabel('DATE', 'date');
            $list->setVar('option', array("%d/%m/%Y - %H\h%i"));
            $this->addLabel($list);
            //-- LABELS

            return $this->displayList($formvars['type']);

        }
    }

}