<?php

class bProduit extends BackModule
{

    function bProduit($formvars = array())
    {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    function update($formvars = array(), $table = null, $langue = true)
    {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['produit_ecotaxe'] = $taxe->getPrixHt($formvars['produit_ecotaxe'], $formvars['taxe_id']);
        $formvars['produit_prix_conseille'] = $taxe->getPrixHt($formvars['produit_prix_conseille'], $formvars['taxe_id']);

        // pour eviter la mise a jour de la quantite info
        unset($formvars['produit_quantite']);

        $return = parent::update_multi($formvars, $table, $langue);

        // on regarde si le produit a des attributs
        $produit = new Produit;
        $withAtt = $produit->withAtt($formvars['back_id']);

        if (!$withAtt) {
            // mise a jour des stocks a faire si le stock bouge
            $qte = $formvars['produit_quantite_p_new'] - $formvars['produit_quantite_p'];
            if ($qte != 0) {
                $stock = new Stock;
                $stock->mouvement($formvars['back_id'], '', '', '', $qte, $formvars['produit_achat'], 'in', 'in', 'Mouvement de stock');
            }
        } else {
            // on met a jour le produit si qte
            $stock = new Stock;
            $stock->updateProdWithAtt($formvars['back_id']);
        }


        return $return;
    }

    function duplicate($formvars = array(), $table = null, $langue = true)
    {

        $formvars['produit_quantite'] = 0;
        $formvars['produit_quantite_p'] = 0;

        $return = $this->create($formvars, $table, $langue);

        // Duplicate des categories
        $sql = "INSERT INTO produit_categorie (produit_id,categorie_id)";
        $sql .= " SELECT '" . $return . "',categorie_id FROM produit_categorie WHERE produit_id = '" . $formvars['back_id'] . "'";
        $this->sql->query($sql);


        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true)
    {

        // on calcule le prix ht
        $taxe = new Taxe;
        $formvars['produit_ecotaxe'] = $taxe->getPrixHt($formvars['produit_ecotaxe'], $formvars['taxe_id']);
        $formvars['produit_prix_conseille'] = $taxe->getPrixHt($formvars['produit_prix_conseille'], $formvars['taxe_id']);

        $return = parent::create_multi($formvars, $table, $langue);

        // mise a jour des stocks a faire dans tous les cas pour init le stock
        $stock = new Stock;
        $qte = $formvars['produit_quantite_p_new'] - $formvars['produit_quantite_p'];
        $stock->mouvement($return, '', '', '', $qte, $formvars['produit_achat'], 'in', 'in', 'Stock Initial');

        Boutique::getInstance()->createStatut('produit', $return);

        return $return;
    }

    function delete($formvars = array(), $table = null, $langue = true)
    {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array())
    {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau produit</a></span>";
        else {
            $sql = "SELECT CONCAT(pn.produit_nom_nom,' - #', pn.produit_id ,'') as data FROM produit_nom pn WHERE produit_id = '" . $formvars['id'] . "' AND pn.langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du produit : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {

            // on regarde si le produit a des attributs
            $produit = new Produit;
            $withAtt = $produit->withAtt($formvars['id']);

            $sql = "SELECT *";
            $sql .= ", p.produit_prix";
            $sql .= ", ROUND(produit_ecotaxe * (1 + taxe_taux),2) as produit_ecotaxe";
            $sql .= ", ROUND(produit_prix_conseille * (1 + taxe_taux),2) as produit_prix_conseille";
            $sql .= ", produit_quantite_p as produit_quantite_p_new";
            $sql .= " FROM (produit p, taxe t)";
            $sql .= " LEFT JOIN produit_nom pn ON (pn.produit_id = p.produit_id)";
            $sql .= " WHERE p.produit_id = '" . $formvars['id'] . "'";
            $sql .= " AND t.taxe_id = p.taxe_id	";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Identification du produit", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Ref Interne", "text", "produit_ref");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Ref Fabricant", "text", "produit_ref_frs");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Référence Fournisseur", "text", "produit_fournisseur_ref");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("EAN", "text", "produit_ean");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "produit_nom_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Fournisseur", "select", "fournisseur_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT fournisseur_id, fournisseur_nom FROM (fournisseur) ORDER BY fournisseur_nom"));
        $this->addForm($form);

        $form = new BackForm("Marque", "select", "marque_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT marque_id, marque_nom FROM (marque) ORDER BY marque_nom"));
        $this->addForm($form);

        /*$form = new BackForm("Couleur interne", "text", "produit_nom_couleur");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Couleur", "select", "couleur_id");
        $form->addAttr('class', 'required');
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT couleur_id, couleur_nom_nom FROM (couleur_nom) ORDER BY couleur_nom_nom"));
        $this->addForm($form);*/

        $form = new BackForm("Rang", "text", "produit_rang");
        $form->setVar('comment', 'Obsolete');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Type du produit", "select", "produit_type_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT produit_type_id, produit_type_libelle FROM (produit_type) ORDER BY produit_type_id"));
        $this->addForm($form);

        /*$form = new BackForm("Nécéssite un justificatif", "select", "produit_justificatif");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0','Non') UNION (SELECT '1','Oui')"));
        $this->addForm($form);*/

        $form = new BackForm("Statut", "select", "produit_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT produit_statut_id, produit_statut_nom FROM (produit_statut) ORDER BY produit_statut_id"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'produit_boutique');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Tarifs du produit", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Prix de Vente HT", "text", "produit_prix");
        $form->addAttr('size', 20);
        $form->setVar('comment', 'Attention : le prix de vente TTC inclus le EcoTaxe');
        $this->addForm($form);

        $form = new BackForm("dont frais de port HT", "text", "produit_frais_port");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("dont EcoTaxe TTC", "text", "produit_ecotaxe");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("TVA appliqu&eacute;e", "select", "taxe_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT taxe_id, taxe_nom FROM (taxe) ORDER BY taxe_rang"));
        $this->addForm($form);

        $form = new BackForm("Prix Conseillé TTC", "text", "produit_prix_conseille");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Conditionnement", "text", "produit_unite");
        $form->setVar('comment', 'Definit le nombre d\'unit&eacute; inclus dans ce produit, permet le calcul du prix &agrave; l\'unit&eacute;');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Promotions", "group");
        $form->addGroupOpts('class', 'produit_prix_barre');
        $this->addForm($form);

        $form = new BackForm("Attributs", "group");
        $form->addGroupOpts('class', 'produit_attribut');
        $this->addForm($form);

        /*$form = new BackForm("Declinaison couleur", "group");
        $form->addGroupOpts('class', 'produit_declinaison');
        $this->addForm($form);*/

        //GROUP
        $form = new BackForm("Informations Stocks", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("PUMP HT", "text", "produit_achat");
        $form->addAttr('size', 20);
        $form->setVar('comment', "Prix d'achat unitaire moyen pond&eactue;r&eactue; (pour le calcul de marge)");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Prix Achat HT", "text", "produit_achat_der");
        $form->addAttr('size', 20);
        $form->setVar('comment', "Dernier prix d'achat constat&eacute; chez le fournisseur");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        // quantite info, on ne peut la modifier
        $form = new BackForm("Quantit&eacute; Informatique", "text", "produit_quantite");
        $form->addAttr('readonly', 'readonly');
        $form->addAttr('size', 20);
        $this->addForm($form);

        // quantite en stock
        $form = new BackForm("Quantit&eacute; en Stock", "text", "produit_quantite_p_new");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        $form->addAttr('size', 20);
        $this->addForm($form);
        $form = new BackForm("Quantit&eacute; en Stock OLD", "hidden", "produit_quantite_p");
        $this->addForm($form);

        $form = new BackForm("Stock d'alerte", "text", "produit_alerte");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("R&eacute;appro possible", "select", "produit_appro_id");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        else
            $form->addAttr('class', 'required');
        $form->setVar('comment', 'D&eacute;finit si le produit est encore disponible sur le site tout en &eacute;tant hors stock');
        $form->addOptionSQL(array("SELECT produit_appro_id, produit_appro_nom FROM (produit_appro) ORDER BY produit_appro_id DESC"));
        $this->addForm($form);

        $form = new BackForm("Delai Expedition (en jours)", "text", "produit_livraison");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Delai Pr&eacute;commande", "text", "produit_date_precmd");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Poids (g)", "text", "produit_poids");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Min Achat Qte", "text", "produit_achat_qte_min");
        if (isset($withAtt) && $withAtt == true)
            $form->addAttr('readonly', 'readonly');
        $form->addAttr('size', 20);
        $this->addForm($form);

        //GROUP
        $form = new BackForm("Descriptifs Produits", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Description d&eacute;taill&eacute;", "tinymce", "produit_nom_desc");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Informations compl&eacute;mentaires", "tinymce", "produit_nom_desc2");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Images", "group");
        $form->addGroupOpts('class', 'produit_image');
        $this->addForm($form);

        $form = new BackForm("Categories", "group");
        $form->addGroupOpts('class', 'produit_categorie');
        $this->addForm($form);

        /*$form = new BackForm("Caracteristique", "group");
        $form->addGroupOpts('class', 'produit_caracteristique_valeur');
        $this->addForm($form);*/

        $form = new BackForm("Gammes", "group");
        $form->addGroupOpts('class', 'produit_gamme');
        $this->addForm($form);

        /*$form = new BackForm("Avis", "group");
        $form->addGroupOpts('class', 'produit_note');
        $this->addForm($form);*/

        $form = new BackForm("Produits Associes", "group");
        $form->addGroupOpts('class', 'produit_assoc');
        $this->addForm($form);

        /*$form = new BackForm("Tags", "group");
        $form->addGroupOpts('class', 'produit_tag');
        $this->addForm($form);*/

        //GROUP
        $form = new BackForm("R&eacute;f&eacute;rencement", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Ref Titre", "text", "produit_nom_meta_title");
        $form->addAttr('size', 50);
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Desc", "text", "produit_nom_meta_desc");
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Ref Mots-Cl&eacute;s", "text", "produit_nom_meta_kw");
        $form->addAttr('size', 50);
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Autre", "tinymce", "produit_nom_meta_other");
        $form->setVar('translate', true);
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array())
    {

        // RECHERCHE
        $form = new BackForm("# PRODUIT", "text", "p.produit_id");
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);

        $form = new BackForm("Ref. Interne", "text", "p.produit_ref");
        $form->setVar("compare", "LIKE");
        $this->addForm($form);

        $form = new BackForm("Ref. Fabricant", "text", "p.produit_fournisseur_ref");
        $form->setVar("compare", "LIKE");
        $this->addForm($form);

        $form = new BackForm("Ean", "text", "p.produit_ean");
        $form->setVar("compare", "LIKE");
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

        $form = new BackForm("Type du produit", "select", "p.produit_type_id");
        $form->setVar("compare", "EQUAL");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT pt.produit_type_id, pt.produit_type_libelle FROM (produit_type pt) ORDER BY produit_type_id"));
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "pb.boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_id"));
        $this->addForm($form);

        $form = new BackForm("Statut Produit", "statut", "p.produit_statut_id", array('1', '2'));
        $form->setVar("compare", "EQUAL");
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT p.produit_id";
        $sql .= ", p.produit_id";
        $sql .= ", produit_ref";
        $sql .= ", p.produit_fournisseur_ref";
        $sql .= ", f.fournisseur_nom";
        $sql .= ", pn.produit_nom_nom";
        $sql .= ", produit_quantite_p";
        $sql .= ", round(p.produit_achat_der,2)";


        if (!empty($formvars['type']) && $formvars['type'] == "csv") {
            $sql .= ", GROUP_CONCAT(DISTINCT(cn.categorie_nom_nom) SEPARATOR ' | ') AS categories ";
            $sql .= ", f.fournisseur_nom  ";
            $sql .= ", f.fournisseur_ref ";
            $sql .= ", f.fournisseur_id ";
            $sql .= ", m.marque_nom";
            $sql .= ", m.marque_id";
            $sql .= ", pn.produit_nom_desc";
            $sql .= ", p.produit_achat_der";
            $sql .= ", p.produit_prix";
            $sql .= ", p.produit_frais_port";
            $sql .= ", pi.produit_image";
        }

        $sql .= ", ROUND(p.produit_prix,2)";
        $sql .= ", ROUND(p.produit_frais_port,2)";

        $sql .= ", ROUND(p.produit_frais_port + produit_prix ,2)";

        $sql .= ", IF(p.produit_achat_der > 0, ROUND((((produit_prix) - p.produit_achat_der) / (produit_prix)) *100 ,2),'N/A')";

        $sql .= ", COUNT(DISTINCT produit_categorie_id) AS nb_categories";
        $sql .= ", COUNT(DISTINCT produit_gamme_id) AS nb_gammes";
        $sql .= ", COUNT(DISTINCT pi.produit_image_id) AS nb_images";
        $sql .= ", ps.produit_statut_image";

        $sql .= " FROM (produit p, taxe t)";
        $sql .= " LEFT JOIN produit_gamme pg ON (pg.produit_id = p.produit_id)";
        $sql .= " LEFT JOIN fournisseur f ON (f.fournisseur_id = p.fournisseur_id)";
        $sql .= " LEFT JOIN produit_categorie pc ON (pc.produit_id = p.produit_id)";
        $sql .= " LEFT JOIN categorie c ON (pc.categorie_id = c.categorie_id)";
        $sql .= " LEFT JOIN produit_attribut patt ON (patt.produit_id = p.produit_id)";
        $sql .= " LEFT JOIN produit_image pi ON (pi.produit_id = p.produit_id)";
        $sql .= " LEFT JOIN produit_statut ps ON (ps.produit_statut_id = p.produit_statut_id)";
        $sql .= " LEFT JOIN produit_nom pn ON (pn.produit_id = p.produit_id AND pn.langue_id='" . LANGUE . "')";
        $sql .= " LEFT JOIN produit_prix_barre ppb ON (p.produit_id = ppb.produit_id AND ppb.produit_attribut_id IS NULL AND ppb.produit_prix_barre_date_debut <= NOW() AND ppb.produit_prix_barre_date_fin >= NOW())";
        $sql .= " LEFT JOIN produit_prix_barre ppb_att ON (p.produit_id = ppb_att.produit_id AND ppb_att.produit_attribut_id IS NOT NULL AND ppb_att.produit_prix_barre_date_debut <= NOW() AND ppb_att.produit_prix_barre_date_fin >= NOW())";

        $sql .= " LEFT JOIN produit_boutique pb ON (pb.produit_id = p.produit_id)";

        if (!empty($formvars['type']) && $formvars['type'] == "csv") {
            $sql .= " LEFT JOIN categorie_nom as cn ON (cn.categorie_id = c.categorie_id AND cn.langue_id = '" . LANGUE . "') ";

            $sql .= " LEFT JOIN marque as m ON m.marque_id = p.marque_id ";
        }

        $sql .= " WHERE 1";
        $sql .= " AND t.taxe_id = p.taxe_id";

        if (!empty($_GET['pc*categorie_id'])) {
            $sql .= " AND (c.categorie_id = '" . $_GET['pc*categorie_id'] . "' OR c.parent_id = '" . $_GET['pc*categorie_id'] . "')";
        }

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

        $label = new BackLabel("Fournisseur");
        $this->addLabel($label);

        $label = new BackLabel("NOM", "left");
        $this->addLabel($label);

        $label = new BackLabel("Quantit&eacute; en stock");
        $label->setVar('abr', "STOCK");
        $this->addLabel($label);

        $label = new BackLabel("PRIX ACHAT HT");
        $this->addLabel($label);


        if (!empty($formvars['type']) && $formvars['type'] == "csv") {
            $label = new BackLabel("CATEGORIE");
            $this->addLabel($label);
            $label = new BackLabel("FOURNISSEUR");
            $this->addLabel($label);
            $label = new BackLabel("FOURNISSEUR REF");
            $this->addLabel($label);
            $label = new BackLabel("FOURNISSEUR ID");
            $this->addLabel($label);
            $label = new BackLabel("MARQUE");
            $this->addLabel($label);
            $label = new BackLabel("MARQUE ID");
            $this->addLabel($label);
            $label = new BackLabel("DESCRIPTION");
            $this->addLabel($label);
            $label = new BackLabel("Prix achat HT");
            $this->addLabel($label);
            $label = new BackLabel("Prix vente HT");
            $this->addLabel($label);
            $label = new BackLabel("Port HT");
            $this->addLabel($label);
            $label = new BackLabel("Produit image");
            $this->addLabel($label);
        }


        /*
                $label = new BackLabel("Poids");
                $this->addLabel($label);
        */
        $label = new BackLabel("PRIX DE VENTE");
        $this->addLabel($label);

        $label = new BackLabel("PRIX PORT");
        $this->addLabel($label);

        $label = new BackLabel("PRIX TOTAL");
        $this->addLabel($label);

        $label = new BackLabel("% MARGE");
        $this->addLabel($label);

        /*$label = new BackLabel("NOMBRE D'ATTRIBUTS", "listselect", "ATTRIBUT");
        $label->setVar('option', array("group" => "produit_attribut"));
        $this->addLabel($label);*/

        $label = new BackLabel("NOMBRE DE CATEGORIES", "listselect", "CATEGORIES");
        $label->setVar('option', array("group" => "produit_categorie"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE GAMMES", "listselect", "GAMMES");
        $label->setVar('option', array("group" => "produit_gamme"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE D'IMAGES", "listselect", "PHOTO");
        $label->setVar('option', array("group" => "produit_image"));
        $this->addLabel($label);


        $label = new BackLabel("STATUT", "statut_v2");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

    function autocomplete($formvars = array())
    {
        $out = "";
        if (isset($formvars['produit_nom_nom']) && !empty($formvars['produit_nom_nom']) && strlen($formvars['produit_nom_nom']) > 0) {
            $produit = new Produit();
            $arrArt = $produit->getAutoComplete(array('search' => $formvars['produit_nom_nom']));
            $i = 1;
            foreach ($arrArt as $arti) {
                $out .= "<input style='display:none;' id='input_produit_" . $arti['produit_id'] . "' value='" . $arti['produit_nom_nom'] . "'/>";
                $value = $arti['produit_id'] . " / " . $arti['produit_ref'] . " / " . $arti['produit_ref_frs'] . " / " . $arti['produit_nom_nom'];
                $value = mb_eregi_replace("(" . $formvars['produit_nom_nom'] . ")", "<strong>\\1</strong>", $value);
                $out .= "<div class='results " . (($i) ? 'highlight' : '') . "' id='produit_" . $arti['produit_id'] . "'>" . $value . "</div>";
                $i = abs($i - 1);
            }

            if (!$out)
                $out = "<div>Entrez le nom d'un produit</div>";
        } else
            $out = "<div>Entrez le nom d'un produit</div>";

        return $out;
    }

    function getAutocomplete($formvars = array())
    {
        $produit = new Produit();
        list($arrArt) = $produit->getAutoComplete(array('id' => $formvars['id']));
        return htmlentities($arrArt['produit_nom_nom'], ENT_QUOTES, 'UTF-8') . "|" . $arrArt['produit_id'];
    }

//--
}