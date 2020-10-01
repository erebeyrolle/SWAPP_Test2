<?php

class bPromotion extends BackModule {

    function bPromotion($formvars = array()) {
        parent::BackModule($formvars);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $id = parent::create($formvars);
        Boutique::getInstance()->createStatut('promotion', $id);
        return $id;
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle promotion</a></span>";
        else {
            $sql = "SELECT promotion_nom as data FROM promotion WHERE promotion_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition d'une promotion : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('promotion', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Identification", "group");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "promotion_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Code Promo", "text", "promotion_code");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Valeur (HT ou %)", "text", "promotion_prix");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Type", "select", "promotion_prefix_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT promotion_prefix_id, promotion_prefix_nom FROM promotion_prefix WHERE promotion_prefix_id < 3"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'promotion_boutique');
        $this->addForm($form);

        $form = new BackForm("Validit&eacute;", "group");
        $this->addForm($form);

         $form = new BackForm("Nb utilisation", "text", "promotion_nombre_utilisation");
        $form->addAttr('class', 'required');
        $form->setVar('comment', "Nombre maximum d'utilisation de ce code (-1 = illimit&eactue;)");
        $this->addForm($form);

        $form = new BackForm("Date de d&eacute;but", "datetime", "promotion_date_debut");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Date de fin", "datetime", "promotion_date_fin");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "promotion_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT promotion_statut_id, promotion_statut_nom FROM promotion_statut"));
        $this->addForm($form);

        $form = new BackForm("Application", "group");
        $this->addForm($form);

        $form = new BackForm("Tous les produits ?", "select", "promotion_all");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);

        $form = new BackForm("Prix Barres ?", "select", "promotion_prix_barre");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);

        $form = new BackForm("Min Achat HT", "text", "promotion_min_achat");
        $this->addForm($form);
        $form = new BackForm("Min Qte", "text", "promotion_min_qte");
        $this->addForm($form);

        $form = new BackForm("Promotion sur ?", "select", "promotion_application");
        $form->addAttr('class', 'required');
        $form->addOption('0', 'sur tous les produits du panier');
        $form->addOption('1', 'sur le produit le plus cher');
        $form->addOption('2', 'sur le produit le moins cher');
        $this->addForm($form);

        
        //-- Champs du formulaire

        $form = new BackForm("Frais de ports gratuits", "group");
        $form->addGroupOpts('class', 'promotion_fdp');
        $this->addForm($form);

        $form = new BackForm("Categories", "group");
        $form->addGroupOpts('class', 'promotion_categorie');
        $this->addForm($form);

        $form = new BackForm("Fournisseurs", "group");
        $form->addGroupOpts('class', 'promotion_fournisseur');
        $this->addForm($form);

        $form = new BackForm("Gammes", "group");
        $form->addGroupOpts('class', 'promotion_gamme');
        $this->addForm($form);


        $form = new BackForm("Marques", "group");
        $form->addGroupOpts('class', 'promotion_marque');
        $this->addForm($form);

        $form = new BackForm("Produits", "group");
        $form->addGroupOpts('class', 'promotion_produit');
        $this->addForm($form);

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        //-- RECHERCHE
        // SQL
        $sql = "SELECT p.promotion_id, p.promotion_id, promotion_nom, promotion_code";
        $sql.=", CONCAT('- ', promotion_prix,' ',promotion_prefix_nom)";
        $sql.=" ,  promotion_nombre_utilisation";
        $sql.=" ,  promotion_date_debut";
        $sql.=" , promotion_date_fin";

        $sql.=" , IF(promotion_all=1,'Oui','Non')";
        $sql.=", COUNT(DISTINCT promotion_categorie_id) AS nb_cat";
        $sql.=", COUNT(DISTINCT promotion_fournisseur_id) AS nb_fourn";
        $sql.=", COUNT(DISTINCT promotion_gamme_id) AS nb_gamme";
        $sql.=", COUNT(DISTINCT promotion_marque_id) AS nb_marque";
        $sql.=", COUNT(DISTINCT promotion_produit_id) AS nb_produit";



        $sql.=" , promotion_statut_id";
        $sql.=" FROM (promotion p, promotion_prefix pp)";
        $sql.=" LEFT JOIN promotion_categorie pc ON (pc.promotion_id = p.promotion_id)";
        $sql.=" LEFT JOIN promotion_fournisseur pf ON (pf.promotion_id = p.promotion_id)";
        $sql.=" LEFT JOIN promotion_gamme pg ON (pg.promotion_id = p.promotion_id)";
        $sql.=" LEFT JOIN promotion_marque pm ON (pm.promotion_id = p.promotion_id)";
        $sql.=" LEFT JOIN promotion_produit ppr ON (ppr.promotion_id = p.promotion_id)";



        $sql.=" WHERE p.promotion_prefix_id = pp.promotion_prefix_id";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("CODE");
        $this->addLabel($label);


        $label = new BackLabel("PROMO");
        $this->addLabel($label);

        $label = new BackLabel("NB UTILISATION");
        $this->addLabel($label);

        $label = new BackLabel("DATE DE DEBUT", "date");
        $label->setVar('option', array('%d/%m/%Y %H:%i'));
        $this->addLabel($label);

        $label = new BackLabel("DATE DE FIN", "date");
        $label->setVar('option', array('%d/%m/%Y %H:%i'));
        $this->addLabel($label);



        $label = new BackLabel("Tous les produits");
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE CATEGORIES", "listselect", "CATEGORIES");
        $label->setVar('option', array("group" => "promotion_categorie"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE FOURNISSEURS", "listselect", "FOURNISSEURS");
        $label->setVar('option', array("group" => "promotion_fournisseur"));
        $this->addLabel($label);


        $label = new BackLabel("NOMBRE DE GAMMES", "listselect", "GAMMES");
        $label->setVar('option', array("group" => "promotion_gamme"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE MARQUES", "listselect", "MARQUES");
        $label->setVar('option', array("group" => "promotion_marque"));
        $this->addLabel($label);


        $label = new BackLabel("NOMBRE DE PRODUITS", "listselect", "PRODUITS");
        $label->setVar('option', array("group" => "promotion_produit"));
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}