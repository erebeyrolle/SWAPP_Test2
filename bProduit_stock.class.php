<?php

class bProduit_stock extends BackModule {

    function bProduit_stock($formvars = array()) {
        parent::BackModule($formvars);

        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Prod ID", "hidden", "p.produit_id");
        $form->setVar("compare", "SPEC");
        $this->addForm($form);
        $form = new BackForm("Att ID", "hidden", "p.produit_attribut_id");
        $form->setVar("compare", "SPEC");
        $this->addForm($form);

        if(!isset($_GET['ps*produit_stock_date_ajout'])) {
            $_GET['ps*produit_stock_date_ajout'][]=date("d/m/Y", mktime(0, 0, 0, date('m'), date('d') - 30, date('Y')));
            $_GET['ps*produit_stock_date_ajout'][]=date("d/m/Y", mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        }
        $form = new BackForm("Date Mouvement", "between", "ps.produit_stock_date_ajout");
        $this->addForm($form);

        //-- RECHERCHE
        // SQL
        $sql = "SELECT ps.produit_stock_id";

        $sql.=", IF(pa.produit_attribut_id IS NOT NULL,pa.produit_attribut_id, p.produit_id)";
        $sql.=", IF(pa.produit_attribut_id IS NOT NULL, produit_attribut_ref, produit_ref)";
        $sql.=", IF(pa.produit_attribut_id IS NOT NULL, produit_attribut_ref_frs, produit_ref_frs)";
        $sql.=", pn.produit_nom_nom";
        $sql.=", IF(povnbis.produit_option_valeur_nom != '' && povn.produit_option_valeur_nom != '', CONCAT(povn.produit_option_valeur_nom,' & ',povnbis.produit_option_valeur_nom), IF(povnbis.produit_option_valeur_nom != '', povnbis.produit_option_valeur_nom, povn.produit_option_valeur_nom)) as produit_option_valeur_nom";


        $sql.=" , ps.produit_stock_date_ajout";
        $sql.=" , ps.produit_stock_libelle";
        $sql.=" , ps.produit_stock_quantite";
        $sql.=" , ps.produit_stock_prix_ht";
        $sql.=" , (ps.produit_stock_prix_ht * ps.produit_stock_quantite)";

        $sql.=" FROM produit_stock ps";
        $sql.=" LEFT JOIN produit p ON (p.produit_id = ps.produit_id)";
        $sql.=" LEFT JOIN produit_nom pn ON (pn.produit_id = p.produit_id AND pn.langue_id = '" . LANGUE . "')";
        // ATT
        $sql.=" LEFT JOIN produit_attribut pa ON (ps.produit_attribut_id = pa.produit_attribut_id)";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn ON (pa.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . $_SESSION['langue_id'] . "')";
        $sql.=" LEFT JOIN produit_option_valeur_nom povnbis ON (pa.produit_option_valeur_id_bis = povnbis.produit_option_valeur_id AND povnbis.langue_id='" . $_SESSION['langue_id'] . "')";


        $sql.=" WHERE 1";

        if (!empty($_GET['p*produit_id']))
            $sql.=" AND p.produit_id = '" . $_GET['p*produit_id'] . "' AND pa.produit_attribut_id IS NULL";

        if (!empty($_GET['p*produit_attribut_id']))
            $sql.=" AND ps.produit_attribut_id = '" . $_GET['p*produit_attribut_id'] . "'";

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

        $label = new BackLabel("ATTRIBUT");
        $this->addLabel($label);

        $label = new BackLabel("DATE", 'date');
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);

        $label = new BackLabel("LIBELLE");
        $this->addLabel($label);

        $label = new BackLabel("MVT QTE");
        $this->addLabel($label);

        $label = new BackLabel("PUMP HT", 'devise');
        $this->addLabel($label);

        $label = new BackLabel("VALEUR HT", 'devise');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}
?>