<?php

class bCommande_statut extends BackModule
{

    function bCommande_statut($formvars = array())
    {
        parent::BackModule($formvars);

        if(!Tools::isDediServicesIp()) {
            $this->droits['DEL'] = false;
            $this->droits['ADD'] = false;
        }


    }

    function update($formvars = array())
    {
        return parent::update_multi($formvars);
    }

    function create($formvars = array())
    {
        return parent::create_multi($formvars);
    }

    function delete($formvars = array())
    {
        return parent::delete_multi($formvars);
    }

    function Form($formvars = array())
    {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau Statut de Commande</a></span>";
        else {
            $sql = "SELECT commande_statut_nom_nom as data FROM commande_statut_nom WHERE commande_statut_id = '" . $formvars['id'] . "' AND langue_id = '" . LANGUE . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du Statut de Commande : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT cs.commande_statut_id, cs.commande_statut_rang, csn.commande_statut_nom_nom, csn.commande_statut_nom_sujet, csn.commande_statut_nom_email, csn.commande_statut_nom_email_html";
            $sql .= " FROM (commande_statut cs)";
            $sql .= " LEFT JOIN commande_statut_nom csn on (cs.commande_statut_id = csn.commande_statut_id AND csn.langue_id = '" . LANGUE . "')";
            $sql .= " WHERE cs.commande_statut_id='" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Nom", "text", "commande_statut_nom_nom");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $form->addAttr('size', 40);
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "commande_statut_rang");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Email", "group");
        $this->addForm($form);

        $form = new BackForm("Email SUJET", "text", "commande_statut_nom_sujet");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $form->addAttr('size', 60);
        $this->addForm($form);

        $form = new BackForm("Email v.TEXTE", "textarea", "commande_statut_nom_email");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Email v.HTML", "tinymce", "commande_statut_nom_email_html");
        $form->setVar('translate', true);
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Boutique", "group");
        $form->addGroupOpts('class', 'commande_statut_boutique');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array())
    {

        // SQL
        $sql = "SELECT cs.commande_statut_id, cs.commande_statut_id, csn.commande_statut_nom_nom, cs.commande_statut_rang";
        $sql .= " FROM (commande_statut cs)";
        $sql .= " LEFT JOIN commande_statut_nom csn on (cs.commande_statut_id = csn.commande_statut_id AND csn.langue_id = '" . LANGUE . "')";
        $sql .= " WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);

        $label = new BackLabel("TRADUCTIONS", 'translate');
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}

?>