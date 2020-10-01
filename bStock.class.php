<?php

class bStock extends BackModule {

    function bStock($formvars = array()) {
        parent::BackModule($formvars);

        $this->droits['ADD'] = false;
        $this->droits['MOD'] = false;
        $this->droits['DEL'] = false;
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("Ref. Interne", "text", "p.produit_ref");
        $form->setVar("compare", "SPEC");
        $this->addForm($form);

        $form = new BackForm("Ref. FRS", "text", "p.produit_ref_frs");
        $form->setVar("compare", "SPEC");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "pn.produit_nom_nom");
        $form->setVar("compare", "LIKE");
        $this->addForm($form);
        
        $form = new BackForm("Cat&eacute;gorie", "select", "pc.categorie_id");
        $form->setVar("compare", "SPEC");
        $form->addOption("", "---");
        $form->addOptionSQL(
                array(
                    array("SELECT c.categorie_id, cn.categorie_nom_nom FROM (categorie c,categorie_nom cn) WHERE parent_id IS NULL AND cn.langue_id='" . LANGUE . "' AND c.categorie_id=cn.categorie_id ORDER BY categorie_rang, categorie_nom_nom", false),
                    array("SELECT c.categorie_id, cn.categorie_nom_nom FROM categorie c,categorie_nom cn WHERE parent_id = '%ID%' AND c.categorie_id = cn.categorie_id AND cn.langue_id ='" . LANGUE . "' ORDER BY categorie_rang, categorie_nom_nom", true),
                )
        );
        $this->addForm($form);

        $form = new BackForm("Gamme", "select", "pg.gamme_id");
        $form->setVar("compare", "EQUAL");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT g.gamme_id, gn.gamme_nom_nom FROM (gamme g, gamme_nom gn) WHERE g.gamme_id = gn.gamme_id AND gn.langue_id = '" . LANGUE . "' ORDER BY gamme_rang"));
        $this->addForm($form);

        $form = new BackForm("Marque", "select", "p.marque_id");
        $form->setVar("compare", "EQUAL");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT marque_id, marque_nom FROM (marque) ORDER BY marque_nom"));
        $this->addForm($form);

        $form = new BackForm("Fournisseur", "select", "p.fournisseur_id");
        $form->setVar("compare", "EQUAL");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT f.fournisseur_id, f.fournisseur_nom FROM (fournisseur f) ORDER BY fournisseur_nom"));
        $this->addForm($form);

        $form = new BackForm("Appro Possible", "select", "appro");
        $form->setVar("compare", "SPEC");
        $form->addOption("", "---");
        $form->addOption("0", "Non");
        $form->addOption("1", "Oui");
        $this->addForm($form);

        if (!isset($_GET['stock_alerte']))
            $_GET['stock_alerte'] = 1;
        $form = new BackForm("Afficher", "select", "stock_alerte");
        $form->setVar("compare", "SPEC");
        $form->addOption("", "Tous");
        $form->addOption("1", "Stock en alerte");
        $form->addOption("2", "Stock OK");
        $this->addForm($form);

        if (!isset($_GET['statut']))
            $_GET['statut'] = 1;
        $form = new BackForm("Statut Produit", "select", "statut");
        $form->setVar("compare", "SPEC");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT produit_statut_id, produit_statut_nom FROM (produit_statut) ORDER BY produit_statut_id"));
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT p.produit_id";
        $sql.=", IF(produit_attribut_id IS NOT NULL, produit_attribut_id, p.produit_id)";
        $sql.=", IF(produit_attribut_id IS NOT NULL, produit_attribut_ref, produit_ref)";
        $sql.=", IF(produit_attribut_id IS NOT NULL, produit_attribut_ref_frs, produit_ref_frs)";
        $sql.=", IF(produit_attribut_id IS NOT NULL, produit_attribut_ean, produit_ean)";
        // $sql.=", IF(produit_attribut_quantite IS NOT NULL, produit_attribut_ref, produit_ref)";
        $sql.=", pn.produit_nom_nom";
        $sql.=", IF(povnbis.produit_option_valeur_nom != '' && povn.produit_option_valeur_nom != '', CONCAT(povn.produit_option_valeur_nom,' & ',povnbis.produit_option_valeur_nom), IF(povnbis.produit_option_valeur_nom != '', povnbis.produit_option_valeur_nom, povn.produit_option_valeur_nom)) as produit_option_valeur_nom";
        $sql.=" , IF(produit_attribut_id IS NOT NULL, produit_attribut_quantite, produit_quantite) as stock_info";
        $sql.=" , IF(produit_attribut_id IS NOT NULL, produit_attribut_quantite_p, produit_quantite_p) as stock_physique";

        $sql.=" , IF(produit_attribut_id IS NOT NULL, produit_attribut_alerte, produit_alerte) as alerte";
        $sql.=" , IF(produit_attribut_id IS NOT NULL, IF(pa.produit_appro_id=0,'Non','Oui'),IF(p.produit_appro_id=0,'Non','Oui'))";


        $sql.=" , IF(produit_attribut_id IS NOT NULL, produit_attribut_achat , produit_achat)";
        $sql.=" , IF(produit_attribut_id IS NOT NULL, produit_attribut_achat *  produit_attribut_quantite_p, produit_achat * produit_quantite_p)";
        $sql.=" , IF(produit_attribut_id IS NOT NULL, produit_attribut_achat_der , produit_achat_der)";
        $sql.=" , IF(produit_attribut_id IS NOT NULL,ps2.produit_statut_image,ps.produit_statut_image)";
        $sql.=" , CONCAT('<a href=\'../../produit_stock/list/?p*', IF(produit_attribut_id IS NOT NULL,CONCAT('produit_attribut_id=', produit_attribut_id), CONCAT('produit_id=', p.produit_id)),'\'>+ de detail')";

        $sql.=" FROM (produit p, produit_statut ps)";
        $sql.=" LEFT JOIN produit_nom pn ON (pn.produit_id = p.produit_id AND pn.langue_id = '" . LANGUE . "')";
        $sql.=" LEFT JOIN produit_categorie pc ON (pc.produit_id = p.produit_id)";
        $sql.=" LEFT JOIN categorie c ON (pc.categorie_id = c.categorie_id)";
         $sql.=" LEFT JOIN produit_gamme pg ON (pg.produit_id = p.produit_id)";
        // ATT
        $sql.=" LEFT JOIN produit_attribut pa ON (p.produit_id = pa.produit_id)";
        $sql.=" LEFT JOIN produit_statut ps2 ON (ps2.produit_statut_id = pa.produit_attribut_statut_id)";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn ON (pa.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . $_SESSION['langue_id'] . "')";
        $sql.=" LEFT JOIN produit_option_valeur_nom povnbis ON (pa.produit_option_valeur_id_bis = povnbis.produit_option_valeur_id AND povnbis.langue_id='" . $_SESSION['langue_id'] . "')";

       
        $sql.=" WHERE p.produit_statut_id = ps.produit_statut_id";
        if (!empty($_GET['stock_alerte']) && $_GET['stock_alerte'] == 1)
            $sql.=" AND ((pa.produit_attribut_id IS NOT NULL AND pa.produit_attribut_quantite <= pa.produit_attribut_alerte) OR (pa.produit_attribut_id IS NULL AND p.produit_quantite <= p.produit_alerte))";


        if (!empty($_GET['p*produit_ref'])) {
            $sql.=" AND (p.produit_ref LIKE '%" . $_GET['p*produit_ref'] . "%' OR pa.produit_attribut_ref LIKE '%" . $_GET['p*produit_ref'] . "%')";
        }
        if (!empty($_GET['p*produit_ref_frs'])) {
            $sql.=" AND (p.produit_ref_frs LIKE '%" . $_GET['p*produit_ref_frs'] . "%' OR pa.produit_attribut_ref_frs LIKE '%" . $_GET['p*produit_ref_frs'] . "%')";
        }

        if (!empty($_GET['pc*categorie_id'])) {
            $sql.=" AND (c.categorie_id = '" . $_GET['pc*categorie_id'] . "' OR c.parent_id = '" . $_GET['pc*categorie_id'] . "')";
        }

        if (isset($_GET['appro']) && $_GET['appro']!=='') {
            $sql.=" AND ((p.produit_appro_id = '" . $_GET['appro'] . "' AND pa.produit_attribut_id IS NULL) OR (pa.produit_appro_id = '" . $_GET['appro'] . "' AND pa.produit_appro_id IS NOT NULL))";
        }

        if (isset($_GET['statut']) && $_GET['statut']!=='') {
            $sql.=" AND ((p.produit_statut_id = '" . $_GET['statut'] . "' AND pa.produit_attribut_id IS NULL) OR (pa.produit_attribut_statut_id = '" . $_GET['statut'] . "' AND pa.produit_appro_id IS NOT NULL))";
        }

        if (!empty($_GET['stock_alerte'])) {
            if ($_GET['stock_alerte'] == 1) {
                $sql.=" AND (
                    (produit_attribut_id IS NOT NULL AND pa.produit_attribut_quantite<=pa.produit_attribut_alerte)
                    OR (produit_attribut_id IS NULL AND p.produit_quantite<=p.produit_alerte)
                    )";
            }
            if ($_GET['stock_alerte'] == 2) {
                $sql.=" AND (
                    (produit_attribut_id IS NOT NULL AND pa.produit_attribut_quantite>pa.produit_attribut_alerte)
                    OR (produit_attribut_id IS NULL AND p.produit_quantite>p.produit_alerte)
                    )";
            }
        }

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("REF");
        $this->addLabel($label);

        $label = new BackLabel("REF FRS");
        $this->addLabel($label);

        $label = new BackLabel("EAN");
        $this->addLabel($label);
        
        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("ATTRIBUT");
        $this->addLabel($label);

        $label = new BackLabel("Stock informatique");
        $label->setVar('abr', "STOCK I");
        $this->addLabel($label);

        $label = new BackLabel("Stock physique");
        $label->setVar('abr', "STOCK P");
        $this->addLabel($label);

        $label = new BackLabel("ALERTE");
        $this->addLabel($label);
        
        $label = new BackLabel("APPRO");
        $this->addLabel($label);


        $label = new BackLabel("PUMP HT", 'devise');
        $this->addLabel($label);

        $label = new BackLabel("STOCK HT", 'devise');
        $this->addLabel($label);


        $label = new BackLabel("PRIX ACHAT HT", 'devise');
        $this->addLabel($label);
        $label = new BackLabel("STATUT", 'image');
        $this->addLabel($label);


        $label = new BackLabel("DETAIL");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}
?>