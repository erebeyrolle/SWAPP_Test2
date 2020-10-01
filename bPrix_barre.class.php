<?php

class bPrix_barre extends BackModule {

    function bPrix_barre($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update($formvars);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $id = parent::create($formvars);
        Boutique::getInstance()->createStatut('prix_barre', $id);
        return $id;
    }

    function delete($formvars = array()) {
        // on supprime les prix barre de la base (meme si le cascade est en place)
        $prixBarre = new Prix_Barre;
        $prixBarre->deapply($formvars['id']);
        return parent::delete($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau prix barr&eacute;s</a></span>";
        else {
            $sql = "SELECT prix_barre_nom as data FROM prix_barre WHERE prix_barre_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du prix barr&eacute;s  : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = " SELECT *";
            $sql.=" FROM (prix_barre pb)";
            $sql.=" WHERE prix_barre_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Nom", "text", "prix_barre_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Valeur (HT ou %)", "text", "prix_barre_prix");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Type", "select", "prix_barre_prefix_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT prix_barre_prefix_id, prix_barre_prefix_nom FROM prix_barre_prefix WHERE prix_barre_prefix_id < 3"));
        $this->addForm($form);
/*
        $form = new BackForm("Etiquette", "select", "etiquette_id");
        $form->addAttr('class', 'required');
        $form->addOption("NULL","---");
        $form->addOptionSQL(array("SELECT etiquette_id, etiquette_libelle FROM etiquette"));
        $this->addForm($form);
*/

        $form = new BackForm("Date de d&eacute;but", "datetime", "prix_barre_date_debut");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Date de fin", "datetime", "prix_barre_date_fin");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Tous les produits ?", "select", "prix_barre_all");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "prix_barre_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT prix_barre_statut_id, prix_barre_statut_nom FROM prix_barre_statut"));
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'prix_barre_boutique');
        $this->addForm($form);

        $form = new BackForm("Categories", "group");
        $form->addGroupOpts('class', 'prix_barre_categorie');
        $this->addForm($form);

        $form = new BackForm("Fournisseurs", "group");
        $form->addGroupOpts('class', 'prix_barre_fournisseur');
        $this->addForm($form);

        $form = new BackForm("Gammes", "group");
        $form->addGroupOpts('class', 'prix_barre_gamme');
        $this->addForm($form);


        $form = new BackForm("Marques", "group");
        $form->addGroupOpts('class', 'prix_barre_marque');
        $this->addForm($form);

        $form = new BackForm("Produits", "group");
        $form->addGroupOpts('class', 'prix_barre_produit');
        $this->addForm($form);

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        //-- RECHERCHE
        // SQL
        $sql = "SELECT pb.prix_barre_id, pb.prix_barre_id, prix_barre_nom";
        $sql.=", CONCAT('- ', prix_barre_prix,' ',prix_barre_prefix_nom)";
        $sql.=" , prix_barre_date_debut";
        $sql.=" , prix_barre_date_fin";
        $sql.=" , IF(prix_barre_all=1,'Oui','Non')";
        $sql.=", COUNT(DISTINCT prix_barre_categorie_id) AS nb_cat";
        $sql.=", COUNT(DISTINCT prix_barre_fournisseur_id) AS nb_fourn";
        $sql.=", COUNT(DISTINCT prix_barre_gamme_id) AS nb_gamme";
        $sql.=", COUNT(DISTINCT prix_barre_marque_id) AS nb_marque";
        $sql.=", COUNT(DISTINCT prix_barre_produit_id) AS nb_produit";



        $sql.=" , prix_barre_statut_id";
        $sql.=" , ''";
        $sql.=" FROM (prix_barre pb, prix_barre_prefix pbp)";
        $sql.=" LEFT JOIN prix_barre_categorie pbc ON (pbc.prix_barre_id = pb.prix_barre_id)";
        $sql.=" LEFT JOIN prix_barre_fournisseur pbf ON (pbf.prix_barre_id = pb.prix_barre_id)";
        $sql.=" LEFT JOIN prix_barre_gamme pbg ON (pbg.prix_barre_id = pb.prix_barre_id)";
        $sql.=" LEFT JOIN prix_barre_marque pbm ON (pbm.prix_barre_id = pb.prix_barre_id)";
        $sql.=" LEFT JOIN prix_barre_produit pbpr ON (pbpr.prix_barre_id = pb.prix_barre_id)";



        $sql.=" WHERE pb.prix_barre_prefix_id = pbp.prix_barre_prefix_id";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("PROMO");
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
        $label->setVar('option', array("group" => "prix_barre_categorie"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE FOURNISSEURS", "listselect", "FOURNISSEURS");
        $label->setVar('option', array("group" => "prix_barre_fournisseur"));
        $this->addLabel($label);
        $label = new BackLabel("NOMBRE DE GAMMES", "listselect", "GAMMES");
        $label->setVar('option', array("group" => "prix_barre_gamme"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE MARQUES", "listselect", "MARQUES");
        $label->setVar('option', array("group" => "prix_barre_marque"));
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE PRODUITS", "listselect", "PRODUITS");
        $label->setVar('option', array("group" => "prix_barre_produit"));
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        $label = new BackLabel("ACTION", "action");
        $label->setVar('url', "/back/html/prix_barre/export/apply_prix/");
        $label->setVar('texte', "Mise &agrave; jour");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}