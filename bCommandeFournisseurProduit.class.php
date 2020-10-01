<?php

class bCommandeFournisseurProduit extends BackModule
{

    function bCommandeFournisseurProduit($formvars = array())
    {

        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;

    }

    function Listing($formvars = array()) {


        if(empty($_GET['commande_fournisseur_id'])) {
            exit;
        }

        // gestion du titre
        $cmdFrs = new CommandeFournisseur();
        $arrCmd = $cmdFrs->get(array('commande_fournisseur_id'=>$_GET['commande_fournisseur_id']));
        $this->name = 'Gestion de la commande fournisseur #'.$_GET['commande_fournisseur_id'].' ('.$arrCmd[0]['fournisseur_nom'].')';

        //-- RECHERCHE
        // SQL
        $sql = "SELECT cfp.commande_fournisseur_produit_id";
        $sql.= ", cfp.commande_fournisseur_produit_id";
        $sql.= ", cfp.produit_ref";
        $sql.= ", cfp.produit_ref_frs";
        $sql.= ", cfp.produit_nom";
        $sql.= ", SUM(IF(cpa.produit_attribut_id IS NULL,cp.produit_quantite,cpa.produit_option_valeur_quantite))";
        $sql.= ", cfp.qte";
        $sql.= ", cfp.qte_recu";

        $sql.=" FROM (commande_fournisseur_produit cfp)";
        $sql.=" LEFT JOIN commande c ON (cfp.commande_fournisseur_id = c.commande_fournisseur_id";
        if($arrCmd[0]['commande_fournisseur_statut_id'] == 1) {
            $sql.=" AND c.commande_statut_id IN (3,5)";
        }
        elseif($arrCmd[0]['commande_fournisseur_statut_id'] == 2) {
            //$sql.=" AND c.commande_statut_id IN (3,5)";
        }
        else {

        }
        $sql.=")";
        $sql.=" LEFT JOIN commande_produit cp ON (cp.commande_id = c.commande_id AND cfp.produit_id = cp.produit_id)";
        $sql.=" LEFT JOIN commande_produit_attribut cpa ON (cpa.commande_produit_id = cp.commande_produit_id AND cfp.produit_attribut_id = cpa.produit_attribut_id)";
        $sql.=" WHERE cfp.commande_fournisseur_id = '".$_GET['commande_fournisseur_id']."'";




        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("REF");
        $this->addLabel($label);

        $label = new BackLabel("REF FRS");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("QTE AFF");
        $this->addLabel($label);

        if($arrCmd[0]['commande_fournisseur_statut_id']==1) {
            $label = new BackLabel("QTE CMD", 'cmdfrs_qte_cmd');
            $this->addLabel($label);
        }
        else {
            $label = new BackLabel("QTE CMD");
            $this->addLabel($label);
        }


        if($arrCmd[0]['commande_fournisseur_statut_id']==2) {
            $label = new BackLabel("QTE RECU", 'cmdfrs_qte_recu');
            $this->addLabel($label);
        }
        if($arrCmd[0]['commande_fournisseur_statut_id']==3) {
            $label = new BackLabel("QTE RECU");
            $this->addLabel($label);
        }



        //-- LABELS

        return $this->displayList($formvars['type']);
    }



}