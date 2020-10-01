<?php

/**
 * Created by PhpStorm.
 * User: Yannick
 * Date: 22/07/2015
 * Time: 14:28
 */
class bCommande_statut_boutique extends BackModule
{

    public function __construct($formvars = array())
    {
        parent::BackModule($formvars);
    }

    public function update($formvars = array(), $table = null, $langue = true)
    {

        return parent::update_multi($formvars, $table, $langue);
    }

    public function create($formvars = array(), $table = null, $langue = true)
    {

        $return = parent::create_multi($formvars, $table, $langue);


        return $return;
    }

    public function delete($formvars = array(), $table = null, $langue = true)
    {
        parent::delete_multi($formvars, $table, $langue);
    }


    public function Form($formvars = array())
    {

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *";
            $sql .= " FROM (commande_statut_boutique csb, commande_statut p)";
            $sql .= " LEFT JOIN commande_statut_boutique_nom csbn ON (csb.commande_statut_boutique_id = csbn.commande_statut_boutique_id AND csbn.langue_id='" . LANGUE . "')";

            $sql .= " WHERE csb.commande_statut_boutique_id = '" . $formvars['id'] . "' AND csb.commande_statut_id = p.commande_statut_id GROUP BY csb.commande_statut_boutique_id";
            $this->request = $sql;
        }
        //-- SQL

        // Champs du formulaire
        $form = new BackForm("Commande statut ID", "hidden", "commande_statut_id");
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT b.boutique_id, b.boutique_nom FROM (boutique b) WHERE 1 GROUP BY b.boutique_id ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("Sujet", "text", "commande_statut_boutique_nom_sujet");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Email TXT", "textarea", "commande_statut_boutique_nom_email");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Email HTML", "tinymce", "commande_statut_boutique_nom_email_html");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        if (!isset($formvars['id'])) $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    public function Listing($formvars = array())
    {
        // SQL
        $sql = "SELECT 
                    csb.commande_statut_boutique_id,
                    csb.commande_statut_boutique_id,
                    CONCAT('#', b.boutique_id, ' - ', b.boutique_nom)
                FROM 
                    commande_statut_boutique csb
                LEFT JOIN 
                    boutique b ON b.boutique_id = csb.boutique_id 
                LEFT JOIN 
                    commande_statut cs ON cs.commande_statut_id = csb.commande_statut_id
                INNER JOIN 
                    commande_statut_boutique_nom csbn ON csb.commande_statut_boutique_id = csbn.commande_statut_boutique_id
                WHERE 
                    csb.boutique_id=b.boutique_id AND csb.commande_statut_id = cs.commande_statut_id";
        if (!empty($formvars['commande_statut'])) {
            $sql .= " AND csb.commande_statut_id = '" . $formvars['commande_statut'] . "'";
        }

        $this->request = $sql;
        //-- SQL

        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);


        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);


        return $this->displayList($formvars['type']);
    }


}