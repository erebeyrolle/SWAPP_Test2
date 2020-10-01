<?php

class bCommandeFournisseur extends BackModule {

    function bCommandeFournisseur($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
        //$this->droits['DEL'] = false;
    }


    function delete($formvars = array()) {

        $commandeFrs = new CommandeFournisseur();
        $return = $commandeFrs->delete($formvars['back_id']);

        return $return;

    }

    function update($formvars = array(), $table = null, $langue = true) {

        if($formvars['commande_fournisseur_statut_id'] == 1) {
            $sql="SELECT SUM(qte_recu) as qte_recu_total";
            $sql.=" FROM commande_fournisseur_produit";
            $sql.=" WHERE commande_fournisseur_id = '".$formvars['back_id']."'";
            error_log($sql);
            $cmd = $this->sql->getOne($sql);
            if($cmd['qte_recu_total'] > 0) {
                return false;
            }
        }


        $cmdFrs = new CommandeFournisseur();
        $cmdFrs->changeStatut($formvars['back_id'],$formvars['commande_fournisseur_statut_id']);


        $return = parent::update($formvars, $table, $langue);
        return $return;

    }

    function update_commentaire($formvars = array()) {

        $sql = "UPDATE commande_fournisseur SET";
        $sql.=" commande_fournisseur_commentaire = '" . addslashes($formvars['var']) . "'";
        $sql.=", commande_fournisseur_date_modif = NOW()";
        $sql.=" WHERE commande_fournisseur_id = '" . $formvars['id'] . "'";
        $this->sql->query($sql);

    }


    function Listing($formvars = array()) {

        $form = new BackForm("#ID CMD FRS", "text", "cf.commande_fournisseur_id");
        $this->addForm($form);

        $form = new BackForm("Fournisseur", "select", "cf.fournisseur_id");
        $form->addOption('','---');
        $form->addOptionSQL(array("SELECT fournisseur_id, fournisseur_nom FROM (fournisseur) ORDER BY fournisseur_nom"));
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "cf.commande_fournisseur_statut_id");
        $form->addOption('','---');
        $form->addOptionSQL(array("SELECT commande_fournisseur_statut_id, commande_fournisseur_statut_nom FROM (commande_fournisseur_statut) ORDER BY commande_fournisseur_statut_rang"));
        $this->addForm($form);



        //-- RECHERCHE
        // SQL
        $sql = "SELECT cf.commande_fournisseur_id";
        $sql.= ", cf.commande_fournisseur_id";
        $sql.= ", f.fournisseur_nom";
        $sql.=", cf.commande_fournisseur_date_ajout";
        $sql.=", cfs.commande_fournisseur_statut_nom";
        $sql.=", cf.commande_fournisseur_date_transmis";
        $sql.=", CONCAT(cf.livraison_nom,'<br/>',cf.livraison_cp,'<br/>',cf.livraison_ville)";
        $sql.=", cf.commande_fournisseur_commentaire";
        $sql.=", SUM(cfp.qte)";
        $sql.=", SUM(cfp.qte_recu)";
        $sql.=", GROUP_CONCAT(CONCAT(produit_nom,' (x',qte,')') SEPARATOR '<br/>')";
        $sql.=",CONCAT('<a href=\"/back/html/commandeFournisseurProduit/list/?commande_fournisseur_id=',cf.commande_fournisseur_id,'\"><img src=\"/back/styles/images/post.gif\" /></a>')";
        $sql.=", cf.total_ht";
        $sql.=", IF(cf.commande_fournisseur_statut_id = 2,CONCAT(\"<a href='" . SITE_URL . "bon_commande_fournisseur.php?commande_fournisseur_id=\",cf.commande_fournisseur_id,\"&fournisseur_id=\",cf.fournisseur_id,\"'><img src='/back/styles/images/pdf.gif' alt='Bon de commande' border='0' /></a>\"),'')";

        $sql.=" FROM (commande_fournisseur cf)";
        $sql.=" LEFT JOIN commande_fournisseur_produit cfp ON (cfp.commande_fournisseur_id = cf.commande_fournisseur_id)";
        $sql.=" LEFT JOIN commande_fournisseur_statut cfs ON (cfs.commande_fournisseur_statut_id = cf.commande_fournisseur_statut_id)";
        $sql.=" LEFT JOIN fournisseur f ON (f.fournisseur_id = cf.fournisseur_id)";
        $sql.=" WHERE 1";

        if(empty($_GET['cf*commande_fournisseur_statut_id'])) {

            $sql.=" AND cf.commande_fournisseur_statut_id IN (1,2)";

        }


        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("FRS");
        $this->addLabel($label);

        $label = new BackLabel("DATE CREATION", 'date');
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);

        $label = new BackLabel("STATUT");
        $this->addLabel($label);

        $label = new BackLabel("DATE TRANSMIS", 'date');
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);

        $label = new BackLabel("LIVRAISON");
        $this->addLabel($label);

        $label = new BackLabel("COMMENTAIRE", "eip");
        $label->setVar('option', array("update_commentaire"));
        $this->addLabel($label);

        $label = new BackLabel("QTE CMD");
        $this->addLabel($label);
        $label = new BackLabel("QTE RECU");
        $this->addLabel($label);

        $label = new BackLabel("PROD");
        $this->addLabel($label);

        $label = new BackLabel("EDIT");
        $this->addLabel($label);

        $label = new BackLabel("TOTAL HT");
        $this->addLabel($label);

        $label = new BackLabel("BCF");
        $this->addLabel($label);


        //-- LABELS

        return $this->displayList($formvars['type']);
    }


    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle commandes fournisseurs</a></span>";
        else {
            $sql = "SELECT commande_fournisseur_id as data FROM commande_fournisseur WHERE commande_fournisseur_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la commande fournisseur : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT cf.*, f.fournisseur_nom";
            $sql.=" FROM (commande_fournisseur cf)";
            $sql.=" LEFT JOIN fournisseur f ON (f.fournisseur_id = cf.fournisseur_id)";
            $sql.=" WHERE commande_fournisseur_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("# COMMANDE FOURNISSEUR", "text", "commande_fournisseur_id");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("FOURNISSEUR", "text", "fournisseur_nom");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "commande_fournisseur_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT cfs.commande_fournisseur_statut_id, cfs.commande_fournisseur_statut_nom FROM (commande_fournisseur_statut cfs) WHERE 1 ORDER BY cfs.commande_fournisseur_statut_rang"));

        $form->setVar('param', "");
        $this->addForm($form);

        if (!isset($formvars['id']))
            $formvars['id'] = 0;

        return $this->displayForm($formvars['id']);
    }


}
