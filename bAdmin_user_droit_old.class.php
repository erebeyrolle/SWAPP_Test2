<?php

class bAdmin_user_droit extends BackModule {

    function bAdmin_user_droit($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array()) {
        $return = parent::update($formvars);
        return $return;
    }

    function create($formvars = array()) {
        $return = parent::create($formvars);
        return $return;
    }

    function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT * FROM (admin_user_droit aud)";

            $sql.= " WHERE 1";
            $sql.= " AND aud.admin_user_droit_id = '" . $formvars['id'] . "'";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire

        $form = new BackForm('Admin user', 'hidden', 'admin_user_id');
        $this->addForm($form);

        $form = new BackForm('Module', 'select', 'admin_module_id');
        $this->addForm($form);

        $form = new BackForm('Read', 'text', '_read');
        $this->addForm($form);

        $form = new BackForm('Add', 'text', '_add');
        $this->addForm($form);

        $form = new BackForm('Del', 'text', '_del');
        $this->addForm($form);

        $form = new BackForm('Mod', 'text', '_mod');
        $this->addForm($form);

        $form = new BackForm('Imp', 'text', '_imp');
        $this->addForm($form);

        $form = new BackForm('Exp', 'text', '_exp');
        $this->addForm($form);

        $form = new BackForm('Clo', 'text', '_clo');
        $this->addForm($form);

        $form = new BackForm('Inf', 'text', '_inf');
        $this->addForm($form);

        $form = new BackForm('Admin user droit su', 'text', 'admin_user_droit_su');
        $this->addForm($form);



        // $form->addAttr('class', 'required');
        // $form->addOption("", "---");
        // $form->addOptionSQL(array("SELECT aud.admin_user_droit_id, aud.admin_user_droit_nom_nom FROM admin_user_droit aud"));
        // $form->addOptionSQL(array("(SELECT '2', 'Non') UNION (SELECT '1', 'Oui')"));

        //  $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm('# ID', 'text', 'aud.admin_user_droit_id');
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);


        $sql = "SELECT aud.admin_user_droit_id, aud.admin_user_droit_id";
        $sql.= ", aud.admin_user_id";
        $sql.= ", aud.admin_module_id";
        $sql.= ", aud._read";
        $sql.= ", aud._add";
        $sql.= ", aud._del";
        $sql.= ", aud._mod";
        $sql.= ", aud._imp";
        $sql.= ", aud._exp";
        $sql.= ", aud._clo";
        $sql.= ", aud._inf";
        $sql.= ", aud.admin_user_droit_su";

        $sql.= " FROM (admin_user_droit aud)";

        $sql.= " WHERE 1";
        if (!empty($formvars['boutique']))
            $sql.=" AND aud.boutique_id = '" . $formvars['boutique'] . "'";
        $this->request = $sql;


        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('ADMIN USER');
        $this->addLabel($list);

        $list = new BackLabel('ADMIN MODULE');
        $this->addLabel($list);

        $list = new BackLabel('READ');
        $this->addLabel($list);

        $list = new BackLabel('ADD');
        $this->addLabel($list);

        $list = new BackLabel('DEL');
        $this->addLabel($list);

        $list = new BackLabel('MOD');
        $this->addLabel($list);

        $list = new BackLabel('IMP');
        $this->addLabel($list);

        $list = new BackLabel('EXP');
        $this->addLabel($list);

        $list = new BackLabel('CLO');
        $this->addLabel($list);

        $list = new BackLabel('INF');
        $this->addLabel($list);

        $list = new BackLabel('ADMIN USER DROIT SU');
        $this->addLabel($list);



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}