<?php

class bCheque_cadeau extends BackModule {

    function bCheque_cadeau($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['cheque_cadeau_prix_ht'] = $taxe->getPrixHt($formvars['cheque_cadeau_prix_ht'], $formvars['taxe_id']);
        $formvars['cheque_cadeau_prix_min'] = $taxe->getPrixHt($formvars['cheque_cadeau_prix_min'], $formvars['taxe_id']);

        return parent::update($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['cheque_cadeau_prix_ht'] = $taxe->getPrixHt($formvars['cheque_cadeau_prix_ht'], $formvars['taxe_id']);
        $formvars['cheque_cadeau_prix_min'] = $taxe->getPrixHt($formvars['cheque_cadeau_prix_min'], $formvars['taxe_id']);

        return parent::create($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=", ROUND((cc.cheque_cadeau_prix_ht * (1 + taxe_taux)),2) as cheque_cadeau_prix_ht";
            $sql.=", ROUND((cc.cheque_cadeau_prix_min * (1 + taxe_taux)),2) as cheque_cadeau_prix_min";
            $sql.=" FROM (cheque_cadeau cc, taxe t)";
            $sql.=" WHERE cheque_cadeau_id='" . $formvars['id'] . "'";
            $sql.=" AND t.taxe_id = cc.taxe_id";
            
            $this->request = $sql;
        } else {
            $_GET['cheque_cadeau_date_fin'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1));
            $_GET['cheque_cadeau_date_debut'] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));

        }
        //-- SQL
        // Champs du formulaire

        $form = new BackForm("Nom", "text", "cheque_cadeau_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Code", "text", "cheque_cadeau_code");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Valeur TTC", "text", "cheque_cadeau_prix_ht");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Valeur min panier TTC", "text", "cheque_cadeau_prix_min");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe) ORDER BY taxe_rang"));
        $this->addForm($form);

        $form = new BackForm("Début de validité", "date", "cheque_cadeau_date_debut");
        $this->addForm($form);

        $form = new BackForm("Fin de validité", "date", "cheque_cadeau_date_fin");
        $this->addForm($form);

        $form = new BackForm("Etat", "select", "cheque_cadeau_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT cheque_cadeau_statut_id, cheque_cadeau_statut_nom FROM cheque_cadeau_statut"));
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT cc.cheque_cadeau_id";
        $sql.=", cc.cheque_cadeau_id";
        $sql.=", cc.cheque_cadeau_nom";
        $sql.=", ROUND((cc.cheque_cadeau_prix_ht * (1 + taxe_taux)),2) as cheque_cadeau_prix_ht";
        $sql.=", ROUND((cc.cheque_cadeau_prix_min * (1 + taxe_taux)),2) as bon_achat_prix_min ";
        $sql.=", cc.cheque_cadeau_date_debut";
        $sql.=", cc.cheque_cadeau_date_fin";
        $sql.=", ccs.cheque_cadeau_statut_nom";
        $sql.=", CONCAT(\"<a href='" . SITE_URL . "cc.php?cc_id=\",cc.cheque_cadeau_id,\"'><img src='/back/styles/images/pdf.gif' alt='Bon de commande' border='0' /></a>\")";
        $sql.=" FROM (cheque_cadeau cc, cheque_cadeau_statut ccs, taxe t)";
        $sql.=" WHERE cc.cheque_cadeau_statut_id = ccs.cheque_cadeau_statut_id";
        $sql.=" AND t.taxe_id = cc.taxe_id";



        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("TITRE");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR TTC", "devise");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR MIN PANIER", "devise");
        $this->addLabel($label);

        $label = new BackLabel("DEBUT DE VALIDITE",'date');
        $label->setVar('option', array("%d/%m/%Y"));
        $this->addLabel($label);

        $label = new BackLabel("FIN DE VALIDITE",'date');
        $label->setVar('option', array("%d/%m/%Y"));
        $this->addLabel($label);

        $label = new BackLabel("ETAT");
        $this->addLabel($label);

        $label = new BackLabel("Cheque Cadeau", "pdf", "CC");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }
}
?>
