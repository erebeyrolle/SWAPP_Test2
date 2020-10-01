<?php

class BackModuleBoutique extends BackModule {

    protected $table;

    function __construct($formvars = array())
    {
        parent::BackModule($formvars);
    }

    function Form($formvars = array())
    {

        // SQL
        if(!empty($formvars['id'])) {
            $sql="SELECT *";
            $sql.=" FROM ".$this->table."_boutique";
            $sql.=" WHERE ".$this->table."_boutique_id = '".$formvars['id']."'";

            $this->request=$sql;
        }
        //-- SQL

        // Champs du formulaire
        $form = new BackForm($this->table . " ID","hidden", $this->table . "_id");
        $this->addForm($form);

        $form =  new BackForm("Boutique","select","boutique_id");
        $form->addAttr('class','required');
        $form->addOptionSQL(array("SELECT b.boutique_id, b.boutique_nom FROM (boutique b) ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("Statut","select", $this->table . "_boutique_statut_id");
        $form->addAttr('class','required');
        $form->addOptionSQL(array("SELECT boutique_statut_id, boutique_statut_nom FROM (boutique_statut) ORDER BY boutique_statut_rang"));
        $this->addForm($form);

        if ($this->table == 'cms_page') {
            $form = new BackForm("Nom", "text", $this->table . "_boutique_nom");
            $form->setVar('translate', true);
            $form->addAttr('class', 'required');
            $this->addForm($form);

            $form = new BackForm("Contenu", "tinymce", $this->table . "_boutique_contenu");
            $form->addAttr('class', 'required');
            $form->setVar('translate', true);
            $this->addForm($form);
        }

        if(!isset($formvars['id'])) $formvars['id']=0;
        return $this->displayForm($formvars['id'],$formvars['div']);
    }

    function Listing($formvars = array())
    {

        // SQL
        $sql = "SELECT ab.".$this->table."_boutique_id";
        $sql.= ", ab.".$this->table."_boutique_id";
        $sql.= ", b.boutique_nom";
        $sql.= ", bs.boutique_statut_image";
        $sql.= " FROM (".$this->table."_boutique ab)";
        $sql.= " INNER JOIN " . $this->table . " a ON(a.".$this->table."_id = ab.".$this->table."_id)";
        $sql.= " INNER JOIN boutique b ON(ab.boutique_id = b.boutique_id)";
        $sql.= " INNER JOIN boutique_statut bs ON(ab.".$this->table."_boutique_statut_id = bs.boutique_statut_id)";
        $sql.= " WHERE 1";
        if(!empty($formvars[$this->table])) $sql.=" AND ab.".$this->table."_id = '".$formvars[$this->table]."'";

        $this->request = $sql;
        //-- SQL

        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("STATUT","statut_boutique");
        $this->addLabel($label);

        return $this->displayList($formvars['type']);
    }
}