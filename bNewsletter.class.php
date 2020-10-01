<?php

class bNewsletter extends BackModule {

    function bNewsletter($formvars = array()) {
        parent::BackModule($formvars);

   
    }

   

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau mail &agrave; la newsletter</a></span>";
        else {
            $sql = "SELECT newsletter_email as data FROM newsletter WHERE newsletter_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du mail : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('newsletter', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire

        $form = new BackForm("Email", "mail", "newsletter_email");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL

        $form = new BackForm("Type", "select", "c.type");
        $form->addOption("", "---");
        $form->addOption("MAIL", "MAIL");
        $form->addOption("MEMBRE", "MEMBRE");
        $form->addOption("BON PLAN", "BON PLAN");
        $this->addForm($form);


        $sql = "(";
        // inscrit newsletter
        $sql.="SELECT newsletter_id, newsletter_id as id, 'MAIL' as type, newsletter_email, newsletter_date_ajout";
        $sql.=" FROM (newsletter n)";
        $sql.= ")";
        $sql.=" UNION ";
        $sql.="(";
        // client newsletter
        $sql.="SELECT 'no_context', c.client_id as id, 'MEMBRE' as type, c.client_email, c.client_date_modif";
        $sql.=" FROM (client c)";
        $sql.=" LEFT JOIN adresse a ON (a.client_id = c.client_id AND a.adresse_default = 1)";
        $sql.=" WHERE client_newsletter_id = 1";
        $sql.= ")";
        $sql.=" UNION ";
        $sql.="(";
        // client bon plan
        $sql.="SELECT 'no_context', c.client_id as id, 'BON PLAN' as type, c.client_email, c.client_date_modif";
        $sql.=" FROM (client c)";
        $sql.=" LEFT JOIN adresse a ON (a.client_id = c.client_id AND a.adresse_default = 1)";
        $sql.=" WHERE client_bon_plan_id = 1";
        $sql.= ")";

        $sql = "SELECT * FROM (" . $sql . ") c WHERE 1";
        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("TYPE");
        $this->addLabel($label);

        $label = new BackLabel("E-MAIL", "mail");
        $this->addLabel($label);

        $label = new BackLabel("DATE D'INSCRIPTION", "date");
        $label->setVar('option', array("%d/%m/%Y - %H\h%i"));
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>