<?php

class bFournisseur extends BackModule {

    function bFournisseur($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {

        $return = parent::update_multi($formvars);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {

        $id = parent::create_multi($formvars);
        Boutique::getInstance()->createStatut('fournisseur', $id);
        return $id;
    }

    function delete($formvars = array()) {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Fournisseur</a></span>";
        else {
            $sql = "SELECT fournisseur_nom as data FROM fournisseur WHERE fournisseur_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Fournisseur : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('fournisseur', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Nom", "text", "fournisseur_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Email Contact", "text", "fournisseur_contact");
        $this->addForm($form);

        $form = new BackForm("Référence", "text", "fournisseur_ref");
        $form->addAttr('class', 'required');
        $this->addForm($form);



        $form = new BackForm("Delai Expedition (en jours)", "text", "fournisseur_livraison");
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "fournisseur_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT fournisseur_statut_id, fournisseur_statut_nom FROM fournisseur_statut ORDER BY fournisseur_statut_id"));
        $this->addForm($form);

        $form = new BackForm("Adresse Fournisseur", "group");
        $this->addForm($form);

        $form = new BackForm("Rue", "text", "fournisseur_rue");
        $this->addForm($form);
        $form = new BackForm("Code Postal", "text", "fournisseur_cp");
        $this->addForm($form);
        $form = new BackForm("Ville", "text", "fournisseur_ville");
        $this->addForm($form);
        $form = new BackForm("Pays", "text", "fournisseur_pays");
        $this->addForm($form);

        $form = new BackForm("Informations secondaires", "group");
        $this->addForm($form);

        $form = new BackForm("Logo", "image", "fournisseur_image");
        $this->addForm($form);

        $form = new BackForm("Description", "tinymce", "fournisseur_nom_desc");
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'fournisseur_boutique');
        $this->addForm($form);

        //GROUP
        $form = new BackForm("R&eacute;f&eacute;rencement", "group");
        $this->addForm($form);
        //--GROUP

        $form = new BackForm("Ref Titre", "text", "fournisseur_nom_meta_title");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Desc", "text", "fournisseur_nom_meta_desc");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Mots-Cl&eacute;s", "text", "fournisseur_nom_meta_kw");
        $form->addAttr('size', '100');
        $form->setVar('translate', true);
        $this->addForm($form);

        $form = new BackForm("Ref Autre", "tinymce", "fournisseur_nom_meta_other");
        $form->setVar('translate', true);
        $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("NOM", "text", "f.fournisseur_nom");
        $this->addForm($form);

        //-- RECHERCHE
        // SQL
        $sql = "SELECT f.fournisseur_id, f.fournisseur_id, f.fournisseur_nom, f.fournisseur_livraison, CAST(f.fournisseur_ref AS UNSIGNED), fournisseur_statut_id";
        $sql .= ", CONCAT(COUNT(p.produit_id), ' article(s)')";
        $sql.=" FROM (fournisseur f)";
        $sql .= " LEFT JOIN produit p ON(f.fournisseur_id = p.fournisseur_id)";
        $sql.=" WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS

        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("DELAI");
        $this->addLabel($label);

        $label = new BackLabel("REF");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);
        
        $label = new BackLabel("NB PRODUIT");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}