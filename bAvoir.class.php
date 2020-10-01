<?php

class bAvoir extends BackModule {

    function bAvoir($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(),$table = null, $langue = true) {
        $formvars['avoir_montant'] = $formvars['avoir_montant'] / (1 + $formvars['taxe_taux']);

        parent::update($formvars,$table , $langue );
    }

    function create($formvars = array(),$table = null, $langue = true) {

        $formvars['avoir_montant'] = $formvars['avoir_montant'] / (1 + $formvars['taxe_taux']);

        $commande = new Commande;
        $arrCommande = $commande->get(array('commande_id' => $formvars['commande_id']));
        if (empty($arrCommande))
            return false;
        $formvars['client_id'] = $arrCommande[0]['client_id'];

        return parent::create($formvars,$table , $langue );
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouvel avoir</a></span>";
        else {
            $sql = "SELECT CONCAT('# ', avoir_id ,'') as data FROM avoir WHERE avoir_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de l'avoir : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *, ROUND(avoir_montant*(1+taxe_taux),2) as avoir_montant";
            $sql.=" FROM (avoir)";
            $sql.=" WHERE avoir_id = '" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Identification", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Commande", "text", "commande_id");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Date", "date", "avoir_date");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Montant TTC", "text", "avoir_montant");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Taxe", "select", "taxe_taux");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_taux, taxe_nom FROM taxe ORDER BY taxe_rang"));
        $this->addForm($form);

        $form = new BackForm("Informations", "group");
        $this->addForm($form);

        $form = new BackForm("Raison", "textarea", "avoir_raison");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Titre de l'avoir (apparaitra sur la facture)");
        $this->addForm($form);

        $form = new BackForm("Mode de paiement", "text", "avoir_paiement");
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "avoir_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT avoir_statut_id, avoir_statut_nom FROM avoir_statut ORDER BY avoir_statut_id DESC"));
        $this->addForm($form);

        $form = new BackForm("Back Office", "group");
        $this->addForm($form);

        $form = new BackForm("Commentaire", "textarea", "avoir_commentaire");
        $form->setVar('comment', "Ce <b>commentaire</b> restera strictement priv�.<br/>Seuls les Administrateurs pourront le voir et/ou le modifier.");
        $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Commande", "text", "a.commande_id");
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT a.avoir_id, a.avoir_id";
        $sql.=", CONCAT(c.client_prenom,' ',c.client_nom,' (#',c.client_id,')<br/><i>',c.client_commentaire,'</i>')";
        $sql.=", avs.avoir_statut_nom";
        $sql.=", co.commande_id";
        $sql.=", ROUND((a.avoir_montant*(1+a.taxe_taux)),2)";
        $sql.=", avoir_raison";
        $sql.=", avoir_commentaire";
        // TODO : faire la facture d'avoir
        $sql.=", CONCAT('<a target=\"_blank\" href=\"/avoir.php?avoir_id=',a.avoir_id,'&amp;commande_id=',a.commande_id,'\"><img src=\"/back/styles/images/pdf.gif\" alt=\"Bon de commande\" border=\"0\" />')";
        $sql.=" FROM (avoir a, avoir_statut avs, commande co, client c)";
        $sql.=" WHERE a.avoir_statut_id = avs.avoir_statut_id";
        $sql.=" AND a.commande_id = co.commande_id";
        $sql.=" AND c.client_id = a.client_id";
        if (!empty($formvars['client']))
            $sql.=" AND a.client_id = '" . $formvars['client'] . "'";
        if (!empty($formvars['annonceur']))
            $sql.=" AND a.client_id = '" . $formvars['annonceur'] . "'";
        if (!empty($formvars['commande']))
            $sql.=" AND a.commande_id = '" . $formvars['commande'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("CLIENT");
        $this->addLabel($label);

        $label = new BackLabel("STATUT");
        $this->addLabel($label);

        $label = new BackLabel("COMMANDE");
        $this->addLabel($label);

        $label = new BackLabel("MONTANT TTC", "devise");
        $this->addLabel($label);

        $label = new BackLabel("RAISON");
        $this->addLabel($label);

        $label = new BackLabel("COMMENTAIRE");
        $this->addLabel($label);

        $label = new BackLabel("Avoir", "pdf", "AV");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}
?>