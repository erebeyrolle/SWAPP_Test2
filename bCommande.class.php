<?php

class bCommande extends BackModule {

    function bCommande($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
    }

    function update_commentaire($formvars = array()) {

        $sql = "UPDATE commande SET commande_livraison_info = \"" . $formvars['var'] . "\", commande_date_modif = NOW() WHERE commande_id = '" . $formvars['id'] . "'";
        $this->sql->query($sql);
    }

    function update_delais($formvars = array()) {

        $sql = "UPDATE commande SET commande_livraison_delai = '" . $formvars['var'] . "', commande_date_modif = NOW() WHERE commande_id = '" . $formvars['id'] . "'";
        $this->sql->query($sql);
    }

    function update_produit_achat($formvars = array()) {

        $sql = "UPDATE commande_produit SET produit_achat = '" . $formvars['var'] . "' WHERE commande_id = '" . $formvars['id'] . "'";
        $this->sql->query($sql);
    }

    function update($formvars = array(), $table = null, $langue = true)
    {
        $commande = new Commande;

        $tags = array();

        if (empty($formvars['envoi'])) {
            $formvars['envoi'] = false;
            $tags['colis'] = @$formvars['commande_colis'];
        } else {
            $formvars['envoi'] = true;
            $tags['motif'] = @$formvars['commande_motif'];
            $tags['colis'] = @$formvars['commande_colis'];
            $tags['bc'] = @$formvars['commande_bc'];
            $tags['fc'] = @$formvars['commande_fc'];
        }

        $commande->updateConfirm($formvars['commande_id'], $formvars['commande_confirme']);
        $commande->changeStatut($formvars['commande_id'], $formvars['commande_statut_id'], $formvars['envoi'], $tags);
    }

    function delete($formvars = array()) {

        // on gere les stocks en cas de suppression de la commande
        $commande = new Commande;
        $commande->changeStatut($formvars['back_id'], 0);

        $return = parent::delete($formvars);
        return $return;
    }

    function Listing($formvars = array()) {
        // TODO : penser a ajouter le numero de transaction dans le listing et la recherche
        // RECHERCHE
        $form = new BackForm("# Commande", "text", "c.commande_id");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("# Cmd Parent", "text", "cmd_par_id");
        $form->setVar("compare", "SPEC");
        $this->addForm($form);

        $form = new BackForm("# Confirmation d'achat", "text", "c.commande_facture_numero");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("# Client", "text", "c.client_id");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("Nom Client", "text", "c.client_nom");
        $this->addForm($form);

        $form = new BackForm("Statut Commande", "multi_statut", "c.commande_statut_id", array('1', '2', '3', '5', '97', '98', '102'));
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("Mode Paiement", "select", "c.paiement_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT paiement_id, paiement_nom_nom FROM paiement_nom WHERE langue_id = '" . LANGUE . "' ORDER BY paiement_nom_nom"));
        $this->addForm($form);

        $form = new BackForm("Date Commande", "between", "c.commande_date_ajout");
        $this->addForm($form);

        $form = new BackForm("Date Confirmation d'achat", "between", "c.commande_facture_date");
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "c.boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("# Commande Parent ID", "text", "c.commande_parent_id");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("Expedition", "select", "c.commande_expedition_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT commande_expedition_id, commande_expedition_nom FROM commande_expedition"));
        $this->addForm($form);

        $form = new BackForm("Nom Produit", "text", "cp.produit_nom");
        $this->addForm($form);

        $form = new BackForm("#ID CMD FRS", "text", "c.commande_fournisseur_id");
        $this->addForm($form);

        //-- RECHERCHE
        // SQL
        $sql = "SELECT c.commande_id";
        $sql.=", c.commande_id";
        $sql.=", c.commande_parent_id";
        $sql.=", c.commande_date_ajout";
        $sql.=", btq.boutique_nom";
        $sql.=", CONCAT(c.client_nom,' (#',c.client_id,')<br/><i>',cl.client_commentaire,'</i>', cb.client_beneficiaire_ref)";
        //$sql.=", CONCAT('<p style=\"text-align:center\"><img src=\"/back/styles/images/', IF(c.commande_confirme = 0, 'rouge.png', 'vert.png'), '\" /></p>')";
        $sql.=", csn.commande_statut_nom_nom";
        //$sql.=", CONCAT(commande_statut_nom_nom,'<br/>',(SELECT IF(COUNT(commande_paiement_id)>1,GROUP_CONCAT('<span class=\"paiement',commande_paiement_statut_id,'\">',commande_paiement_nom,' - ',ROUND(commande_paiement_montant,2),'&euro;</span>' SEPARATOR '<br/>'),'') FROM commande_paiement WHERE commande_id = c.commande_id))";
        //$sql.=", IF(p.paiement_id = 1 AND cref.tpe_reference_id,CONCAT(paiement_image,'|',UNCOMPRESS(cref.tpe_reference_retour_info)),paiement_image)";

//        $sql.=", p.paiement_image";
//        $sql.=", CONCAT(c.commande_livraison_nom,'<br/>',c.livraison_pays,' (',c.livraison_pays_code,')')";
        $sql.=", c.commande_livraison_nom";
        $sql.=", commande_livraison_delai";
        $sql.=", commande_livraison_colis";
        $sql.=", commande_livraison_info";
        $sql.=", c.commande_expedition_id";
        $sql.=", c.commande_fournisseur_id";

        $sql.=", CONCAT(cp.produit_nom,'<br/><br/>Qte : ',SUM(cp.produit_quantite))";
        $sql.=", CONCAT(cp.produit_ref_frs,'<br/><br/>',f.fournisseur_nom)";

        $sql.=", cp.produit_achat";
        $sql.=", pro.produit_frais_port";
        $sql.=", c.commande_total_HT";
        //$sql.=", IF(cp.produit_achat > 0, ROUND((((pro.produit_frais_port + cp.produit_prix) - cp.produit_achat) / cp.produit_achat) *100 ,2),'N/A')";
        $sql.=", IF(cp.produit_achat > 0, ROUND((((cp.produit_prix - pro.produit_frais_port) - cp.produit_achat) / (cp.produit_prix - pro.produit_frais_port)) *100 ,2),'N/A')";
        $sql.=",''";
        $sql.=", CONCAT(\"<a href='" . SITE_URL . "bon_commande.php?commande_id=\",c.commande_id,\"&client_id=\",c.client_id,\"'><img src='/back/styles/images/pdf.gif' alt='Bon de commande' border='0' /></a>\")";
        //$sql.=", CONCAT(\"<a href='" . SITE_URL . "bon_livraison.php?commande_id=\",c.commande_id,\"&client_id=\",c.client_id,\"'><img src='/back/styles/images/pdf.gif' alt='Bon de commande' border='0' /></a>\")";
        $sql.=", CONCAT(\"<a href='" . SITE_URL . "/back/html/commande/export/bon_commande/?commande_id=\",c.commande_id,\"'><img src='/back/styles/images/pdf.gif' alt='Bon de commande' border='0' /></a>\")";
        $sql.=" FROM (commande c)";
        $sql.=" LEFT JOIN client cl ON cl.client_id = c.client_id";
        $sql.=" LEFT JOIN client_beneficiaire cb ON cb.client_id = cl.client_id";
        $sql.=" LEFT JOIN commande_expedition ce ON ce.commande_expedition_id = c.commande_expedition_id";
        $sql.=" LEFT JOIN commande_produit cp ON (cp.commande_id = c.commande_id)";
        $sql.=" LEFT JOIN produit pro ON (pro.produit_id = cp.produit_id)";
        $sql.=" LEFT JOIN fournisseur f ON (f.fournisseur_id = pro.fournisseur_id)";
        $sql.=" LEFT JOIN commande_statut cs ON (cs.commande_statut_id = c.commande_statut_id)";
        $sql.=" LEFT JOIN paiement p ON (p.paiement_id = c.paiement_id)";
        $sql.=" LEFT JOIN tpe_reference cref ON (cref.commande_id = c.commande_id)";
        $sql.=" LEFT JOIN commande_statut_nom csn ON (csn.commande_statut_id = cs.commande_statut_id AND csn.langue_id='1')";
        $sql.=" LEFT JOIN boutique btq ON (c.boutique_id = btq.boutique_id)";

        $sql.=" WHERE c.client_id = cl.client_id";
        $sql.=" AND (c.commande_parent_id IS NOT NULL OR DATE(c.commande_date_ajout) < '2019-02-12')";
        if (!empty($formvars['client']))
            $sql.=" AND c.client_id = '" . $formvars['client'] . "'";

        if (!empty($formvars['annonceur']))
            $sql.=" AND c.client_id = '" . $formvars['annonceur'] . "'";

        if (!empty($_GET['cmd_par_id'])) {
            $sql.=" AND c.commande_parent_id = '" . $_GET['cmd_par_id'] . "'";
        }



        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);
        $label = new BackLabel("ID ORI");
        $this->addLabel($label);

        $label = new BackLabel("DATE", 'date');
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("CLIENT");
        $this->addLabel($label);

//        $label = new BackLabel("CLIENT EMAIL", "mail");
//        $this->addLabel($label);
//
//        $label = new BackLabel("CLIENT TEL");
//        $this->addLabel($label);

        /*$label = new BackLabel("CONFIRMEE");
        $this->addLabel($label);*/
        
        $label = new BackLabel("STATUT");
        $this->addLabel($label);

//        $label = new BackLabel("Mode de paiement", 'image');
//        $this->addLabel($label);

        $label = new BackLabel("LIVRAISON", "livraison");
        $label->setVar('option', '%ID%');
        $this->addLabel($label);

        $label = new BackLabel("DELAIS (en jours)", "eip");
        $label->setVar('option', array("update_delais"));
        $this->addLabel($label);

        $label = new BackLabel("NUM COLIS");
        $this->addLabel($label);

        $label = new BackLabel("COMMENTAIRE", "eip");
        $label->setVar('option', array("update_commentaire"));
        $this->addLabel($label);

        $label = new BackLabel("EXPEDITION" , "expedition");
        $label->setVar('option', '%ID%');
        $this->addLabel($label);

        $label = new BackLabel("CMD FRS");
        $this->addLabel($label);

        $label = new BackLabel("PRODUIT COMMANDE");
        $this->addLabel($label);

        $label = new BackLabel("FOURNISSEUR");
        $this->addLabel($label);

        $label = new BackLabel("PRIX ACHAT HT", "eip");
        $label->setVar('option', array("update_produit_achat"));
        $this->addLabel($label);

        $label = new BackLabel("FDP HT", "devise");
        $this->addLabel($label);

        $label = new BackLabel("TOTAL HT", "devise");
        $this->addLabel($label);

        $label = new BackLabel("% MARGE");
        $this->addLabel($label);



        $label = new BackLabel("DETAILS", 'action');
        $label->setVar('url', "/back/html/commande_export/custom/details/");
        $label->setVar('texte', '<img src="/back/styles/images/comment.gif" alt="popupajax"/>');
        $this->addLabel($label);

        $label = new BackLabel("Bon de commande", "pdf", "BC");
        $this->addLabel($label);
        
        $label = new BackLabel("Bon de livraison", "pdf", "BL/BP");
        $this->addLabel($label);

//        $label = new BackLabel("Confirmation d'achat", "pdf", "CA");
//        $this->addLabel($label);
//
//        $label = new BackLabel("REF.");
//        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle commande</a></span>";
        else {
            $sql = "SELECT CONCAT('#', commande_id ,'') as data FROM commande WHERE commande_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la commande : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql.=" FROM (commande)";
            $sql.=" WHERE commande_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Client ID", "hidden", "client_id");
        $this->addForm($form);



        $form = new BackForm("# COMMANDE", "text", "commande_id");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);
        $form = new BackForm("Envoi", "checkbox", "envoi");
        $form->setVar('comment', "Cochez cette case pour effectuer un envoi d'email au client");
        $this->addForm($form);
/*
        $form = new BackForm("Confirm&eacute;e ?", "select", "commande_confirme");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Non confirm&eacute;e si la commande contient des produits necessitant un justificatif d'identit&eacute; et que le client ne l'a pas fournit");
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);
*/
        $form = new BackForm("Statut", "spe_statut_option", "commande_statut_id");
        $form->addAttr('class', 'required');
        $form->setVar('param', "SELECT cs.commande_statut_id, csn.commande_statut_nom_nom, csn.commande_statut_nom_email_html FROM (commande_statut cs, commande_statut_nom csn) WHERE cs.commande_statut_id = csn.commande_statut_id AND csn.langue_id='" . LANGUE . "' ORDER BY cs.commande_statut_rang");
        $this->addForm($form);



        //-- Champs du formulaire

        return $this->displayForm($formvars['id']);
    }

}
