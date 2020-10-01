<?php

class bPaiement extends BackModule {

    function bPaiement($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        return parent::update_multi($formvars, $table, $langue);
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $id = parent::create_multi($formvars, $table, $langue);
        Boutique::getInstance()->createStatut('paiement', $id);
        return $id;
    }

    function delete($formvars = array(), $table = null, $langue = true) {
        return parent::delete_multi($formvars, $table, $langue);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Mode de Paiement</a></span>";
        else {
            $sql = "SELECT paiement_nom_nom as data FROM paiement_nom WHERE paiement_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Mode de Paiement : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTableMutli('paiement', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Nom", "text", "paiement_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Image", "image", "paiement_image");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "actif_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT actif_id, actif_nom FROM actif ORDER BY actif_id"));
        $this->addForm($form);

        $form = new BackForm("Reglement Min.", "text", "paiement_total_debut");
        $form->addAttr('class', 'required');
        $this->addForm($form);
        $form = new BackForm("Reglement Max.", "text", "paiement_total_fin");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "paiement_rang");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Boutiques", "group");
        $form->addGroupOpts('class', 'paiement_boutique');
        $this->addForm($form);

        $form = new BackForm("Description du paiement", "group");
        $this->addForm($form);

        $form = new BackForm("Description", "tinymce", "paiement_nom_description");
        $form->setVar('comment', "Ces Informations sont rajout&eacute;s automatiquement au moment du choix du mode de paiement");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Instructions Paiement", "tinymce", "paiement_nom_txt_template");
        $form->setVar('translate', true);
        $form->setVar('comment', "Ces Informations sont rajout�s automatiquement dans la derniere etape du panuier");
        $this->addForm($form);

        $form = new BackForm("Instructions Email", "tinymce", "paiement_nom_txt_email");
        $form->setVar('translate', true);
        $form->setVar('comment', "Ces Informations sont rajout�s automatiquement dans les mails envoy�s au client lors d'une commande");
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT p.paiement_id, p.paiement_id, pn.paiement_nom_nom , p.paiement_total_debut,paiement_total_fin, p.paiement_rang, p.actif_id";
        $sql.=" FROM (paiement p)";
        $sql.=" LEFT JOIN paiement_nom pn on (p.paiement_id = pn.paiement_id AND pn.langue_id = '" . LANGUE . "')";
        $sql.=" WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("MIN");
        $this->addLabel($label);
        $label = new BackLabel("MAX");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("STATUT", "actif");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}