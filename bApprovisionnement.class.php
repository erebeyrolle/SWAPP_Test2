<?php
class bApprovisionnement extends BackModule {

    function bApprovisionnement($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;
    }


    function Listing($formvars = array()) {

        $form = new BackForm("Fournisseur", "select", "f.fournisseur_id");
        $form->addOption('','---');
        $form->addOptionSQL(array("SELECT fournisseur_id, fournisseur_nom FROM (fournisseur) ORDER BY fournisseur_nom"));
        $this->addForm($form);


        $sql = "SELECT cp.produit_id";
        $sql.=", IF(cpa.produit_attribut_id IS NULL,cp.produit_id,CONCAT(cp.produit_id,'ATT',cpa.produit_attribut_id))";
        $sql.=", IF(cpa.produit_attribut_id IS NULL,cp.produit_ref,cpa.produit_attribut_ref)";
        $sql.=", IF(cpa.produit_attribut_id IS NULL,cp.produit_ref_frs,cpa.produit_attribut_ref_frs)";
        $sql.=", f.fournisseur_nom";
        $sql.=", cp.produit_nom";
        $sql.=", cpa.produit_option_valeur_nom";
        $sql.=", IF(cpa.produit_attribut_id IS NULL,SUM(cp.produit_quantite),SUM(cpa.produit_option_valeur_quantite))";
        $sql.=", DATE(MIN(c.commande_date_ajout))";
        $sql.=" FROM (commande_produit cp)";
        $sql.=" LEFT JOIN commande_produit_attribut cpa ON (cp.commande_produit_id = cpa.commande_produit_id)";
        $sql.=" LEFT JOIN commande c ON (cp.commande_id = c.commande_id)";
        $sql.=" LEFT JOIN produit p ON (cp.produit_id = p.produit_id)";
        $sql.=" LEFT JOIN fournisseur f ON (f.fournisseur_id = p.fournisseur_id)";
        $sql.=" WHERE c.commande_parent_id IS NOT NULL";
        $sql.=" AND (c.commande_expedition_id IS NULL OR c.commande_expedition_id IN (2,3))";
        $sql.=" AND c.commande_statut_id IN (2,3,5)";
        $sql.=" AND c.commande_fournisseur_id IS NULL";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("REF");
        $this->addLabel($label);

        $label = new BackLabel("REF FRS");
        $this->addLabel($label);

        $label = new BackLabel("FRS");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("ATTRIBUT");
        $this->addLabel($label);

        $label = new BackLabel("QTE");
        $this->addLabel($label);

        $label = new BackLabel("DATE CMD MIN", 'date');
        $label->setVar('option', array("%d/%m/%Y"));
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}