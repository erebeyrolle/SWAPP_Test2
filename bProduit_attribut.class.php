<?php

class bProduit_attribut extends BackModule {

    function bProduit_attribut($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        $produit = new Produit;
        $formvars['taxe_id'] = $produit->get(array('produit_id' => $formvars['produit_id'] ), 'taxe_id');

        if(empty($formvars["taxe_id"]))
            $formvars["taxe_id"] = Taxe::TAXE_NULL;

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['produit_attribut_prix'] = $taxe->getPrixHt($formvars['produit_attribut_prix'], $formvars['taxe_id']);

        // pour eviter la mise a jour de la quantite info
        unset($formvars['produit_attribut_quantite']);
				
        $return = parent::update($formvars, $table, $langue);

        // mise a jour des stocks a faire si le stock bouge
        $qte = $formvars['produit_attribut_quantite_p_new'] - $formvars['produit_attribut_quantite_p'];
        if ($qte != 0) {
            $stock = new Stock;
            $stock->mouvement($formvars['produit_id'], $formvars['back_id'], '', '', $qte, $formvars['produit_attribut_achat'], 'in', 'in', 'Mouvement de stock');
        }

        // on met a jour le produit
        $stock = new Stock;
        $stock->updateProdWithAtt($formvars['produit_id']);

        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {

        $produit = new Produit;
        $formvars['taxe_id'] = $produit->get(array('produit_id' => $formvars['produit_id'] ), 'taxe_id');

        if(empty($formvars["taxe_id"]))
            $formvars["taxe_id"] = Taxe::TAXE_NULL;

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['produit_attribut_prix'] = $taxe->getPrixHt($formvars['produit_attribut_prix'], $formvars['taxe_id']);

        $return = parent::create($formvars, $table, $langue);

        // mise a jour des stocks a faire dans tous les cas pour init le stock
        $stock = new Stock;
        $qte = $formvars['produit_attribut_quantite_p_new'] - $formvars['produit_attribut_quantite_p'];
        $stock->mouvement($formvars['produit_id'], $return, '', '', $qte, $formvars['produit_attribut_achat'], 'in', 'in', 'Stock Initial');

        // on met a jour le produit
        $stock->updateProdWithAtt($formvars['produit_id']);

        return $return;
    }

    function delete($formvars = array(), $table = null, $langue = true) {

        $produit = new Produit;
        $produitId = $produit->getIdProduit($formvars['id']);

        $return = parent::delete($formvars, $table, $langue);

        // on check si le produit a encore un att
        $withAtt = $produit->withAtt($produitId);

        if (!$withAtt) {
            // mise a jour des stocks a faire dans tous les cas pour init le stock
            $stock = new Stock;
            $stock->updateProdWithAtt($produitId);
            $stock->mouvement($produitId, '', '', '', 0, 0, 'in', 'in', 'Stock Initial');
        } else {
            // on met a jour le produit si qte
            $stock = new Stock;
            $stock->updateProdWithAtt($produitId);
        }

        return $return;
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT pa.*";
            $sql.=", p.taxe_id";
            $sql.=", ROUND(produit_attribut_prix * (1 + taxe_taux),2) as produit_attribut_prix";
            $sql.=", ROUND(produit_attribut_prix_ecotaxe * (1 + taxe_taux),2) as produit_attribut_prix_ecotaxe";
            $sql.=", ROUND(produit_attribut_prix_conseille * (1 + taxe_taux),2) as produit_prix_attribut_conseille";
            $sql.=", produit_attribut_quantite_p as produit_attribut_quantite_p_new";
            $sql.=" FROM (produit_attribut pa, taxe t, produit p)";
            $sql.=" WHERE produit_attribut_id='" . $formvars['id'] . "'";
            $sql.=" AND pa.produit_id = p.produit_id";
            $sql.=" AND t.taxe_id = p.taxe_id";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Identification de l'attribut", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);

        $form = new BackForm("Ref Interne", "text", "produit_attribut_ref");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Ref Fabricant", "text", "produit_attribut_ref_frs");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Option", "select", "produit_option_valeur_id");
        $form->addAttr('class', 'required');
        $form->addOption("NULL", "---");
        //$form->addOptionSQL(array("SELECT pov.produit_option_valeur_id, povn.produit_option_valeur_nom FROM produit_option_valeur pov, produit_option_valeur_nom povn WHERE pov.produit_option_id = '2' AND povn.produit_option_valeur_id = pov.produit_option_valeur_id AND povn.langue_id='" . LANGUE . "' ORDER BY povn.produit_option_valeur_nom"));
        $form->addOptionSQL(array("SELECT pov.produit_option_valeur_id, CONCAT(pon.produit_option_nom ,' > ',povn.produit_option_valeur_nom) FROM produit_option_valeur pov, produit_option_valeur_nom povn, produit_option_nom pon WHERE povn.produit_option_valeur_id = pov.produit_option_valeur_id AND pon.produit_option_id = pov.produit_option_id AND povn.langue_id='" . LANGUE . "' AND pon.langue_id='" . LANGUE . "' ORDER BY pon.produit_option_nom, povn.produit_option_valeur_nom"));
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "produit_attribut_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT produit_statut_id, produit_statut_nom FROM (produit_statut) WHERE produit_statut_id < 3 ORDER BY produit_statut_id"));
        $this->addForm($form);
        /*

        $form = new BackForm("Taxe ID", "hidden", "taxe_id");
        $this->addForm($form);

        $form = new BackForm("Ref Frs", "text", "produit_attribut_ref_frs");
        $form->addAttr('size', 20);
        $this->addForm($form);
        
        $form = new BackForm("EAN", "text", "produit_attribut_ean");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Taille", "select", "produit_option_valeur_id");
        $form->addOption("NULL", "---");
        //$form->addOptionSQL(array("SELECT pov.produit_option_valeur_id, povn.produit_option_valeur_nom FROM produit_option_valeur pov, produit_option_valeur_nom povn WHERE pov.produit_option_id = '2' AND povn.produit_option_valeur_id = pov.produit_option_valeur_id AND povn.langue_id='" . LANGUE . "' ORDER BY povn.produit_option_valeur_nom"));
        $form->addOptionSQL(array("SELECT pov.produit_option_valeur_id, CONCAT(pon.produit_option_nom ,' > ',povn.produit_option_valeur_nom) FROM produit_option_valeur pov, produit_option_valeur_nom povn, produit_option_nom pon WHERE povn.produit_option_valeur_id = pov.produit_option_valeur_id AND pon.produit_option_id = pov.produit_option_id AND povn.langue_id='" . LANGUE . "' AND pon.langue_id='" . LANGUE . "' ORDER BY pon.produit_option_nom, povn.produit_option_valeur_nom"));
        $this->addForm($form);

        $form = new BackForm("Couleur", "select", "produit_option_valeur_id_bis");
        $form->addOption("NULL", "---");
        //$form->addOptionSQL(array("SELECT pov.produit_option_valeur_id, povn.produit_option_valeur_nom FROM produit_option_valeur pov, produit_option_valeur_nom povn WHERE pov.produit_option_id = '1' AND povn.produit_option_valeur_id = pov.produit_option_valeur_id AND povn.langue_id='" . LANGUE . "' ORDER BY povn.produit_option_valeur_nom"));
        $form->addOptionSQL(array("SELECT pov.produit_option_valeur_id, CONCAT(pon.produit_option_nom ,' > ',povn.produit_option_valeur_nom) FROM produit_option_valeur pov, produit_option_valeur_nom povn, produit_option_nom pon WHERE povn.produit_option_valeur_id = pov.produit_option_valeur_id AND pon.produit_option_id = pov.produit_option_id AND povn.langue_id='" . LANGUE . "' AND pon.langue_id='" . LANGUE . "' ORDER BY pon.produit_option_nom, povn.produit_option_valeur_nom"));
        $this->addForm($form);

        $form = new BackForm("Desc", "text", "produit_option_valeur_desc");
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "produit_attribut_rang");
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Tarifs de l'attribut", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Complement TTC", "text", "produit_attribut_prix");
        $form->setVar('comment', 'Attention : le complement TTC inclus le EcoTaxe');
        $this->addForm($form);

        $form = new BackForm("dont EcoTaxe TTC", "text", "produit_attribut_prix_ecotaxe");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Prix Conseillé TTC", "text", "produit_attribut_prix_conseille");
        $form->addAttr('size', 20);
        $this->addForm($form);
*/
        //GROUP
        $form = new BackForm("Informations Stocks", "group");
        $this->addForm($form);
        //--GROUP
/*
        $form = new BackForm("PUMP HT", "text", "produit_attribut_achat");
        $form->setVar('comment', "Prix d'achat unitaire moyen pond&eactue;r&eactue; (pour le calcul de marge)");
        $this->addForm($form);

        $form = new BackForm("Prix Achat HT", "text", "produit_attribut_achat_der");
        $form->setVar('comment', "Dernier prix d'achat constat&eacute; chez le fournisseur");
        $this->addForm($form);

        // quantite info, on ne peut la modifier
        $form = new BackForm("Quantit&eacute; Informatique", "text", "produit_attribut_quantite");
        $form->addAttr('readonly', 'readonly');
        $form->addAttr('size', 20);
        $this->addForm($form);
*/
        // quantite en stock
        $form = new BackForm("Quantit&eacute; en Stock", "text", "produit_attribut_quantite_p_new");
        $form->addAttr('size', 20);
        $this->addForm($form);
        $form = new BackForm("Quantit&eacute; en Stock OLD", "hidden", "produit_attribut_quantite_p");
        $this->addForm($form);

        $form = new BackForm("Stock d'alerte", "text", "produit_attribut_alerte");
        $form->addAttr('size', 20);
        $this->addForm($form);


        $form = new BackForm("R&eacute;appro", "select", "produit_appro_id");
        $form->setVar('comment', 'D&eacute;finit si le produit est encore disponible sur le site tout en &eacute;tant hors stock');
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT produit_appro_id, produit_appro_nom FROM (produit_appro) ORDER BY produit_appro_id DESC"));
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Détails du produit", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Prix achat", "text", "produit_attribut_achat");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Prix vente", "text", "produit_attribut_prix");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Frais de port", "text", "produit_attribut_frais_port");
        $form->addAttr('size', 20);
        $this->addForm($form);


/*
        $form = new BackForm("Delai Reappro (en jours)", "text", "produit_attribut_livraison");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Delai Pr&eacute;commande", "text", "produit_attribut_date_precmd");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Poids (g)", "text", "produit_attribut_poids");
        $this->addForm($form);

        $form = new BackForm("Min Achat Qte", "text", "produit_attribut_achat_qte_min");
        $form->addAttr('size', 20);
        $this->addForm($form);

        */
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT DISTINCT pa.produit_attribut_id";
        $sql.=", pa.produit_attribut_id";

        $sql.=", produit_attribut_ref";
        $sql.=", produit_attribut_ref_frs";
        $sql.=", povn.produit_option_valeur_nom";
        $sql.=", produit_attribut_statut_id";
        $sql.=", pa.produit_attribut_achat";
        $sql.=", pa.produit_attribut_prix";
        $sql.=", pa.produit_attribut_frais_port";

        $sql.=" FROM (produit_attribut pa, produit p)";
        // opt 1
        $sql.=" LEFT JOIN produit_option_valeur pov ON ( pov.produit_option_valeur_id = pa.produit_option_valeur_id )";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn ON ( pov.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . LANGUE . "')";

        $sql.=" WHERE 1";
        $sql.=" AND pa.produit_id = p.produit_id";

        if (!empty($formvars['produit']))
            $sql.=" AND pa.produit_id = '" . $formvars['produit'] . "'";

        //echo $sql;

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("Référence Interne");
        $label->setVar('abr', "REF");
        $this->addLabel($label);

        $label = new BackLabel("Référence Fabricant");
        $label->setVar('abr', "REF FBT");
        $this->addLabel($label);


        $label = new BackLabel("Option");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        $label = new BackLabel("Prix achat");
        $this->addLabel($label);

        $label = new BackLabel("Prix vente");
        $this->addLabel($label);

        $label = new BackLabel("Frais de port");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
