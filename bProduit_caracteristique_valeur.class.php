<?php

class bProduit_caracteristique_valeur extends BackModule {

    function bProduit_caracteristique_valeur($formvars = array()) {
        parent::BackModule($formvars);
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {
            $this->request = getTable('produit_caracteristique_valeur', $formvars['id']);
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);
				
				$form = new BackForm("Caracteristique", "select", "caracteristique_valeur_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(
                array(
                    array("SELECT c.caracteristique_id, cn.caracteristique_nom_nom FROM (caracteristique c,caracteristique_nom cn) WHERE cn.langue_id='" . LANGUE . "' AND c.caracteristique_id=cn.caracteristique_id ORDER BY caracteristique_rang, caracteristique_nom_nom", false),
                    array("SELECT cv.caracteristique_valeur_id, cvn.caracteristique_valeur_nom_nom FROM caracteristique_valeur cv,caracteristique_valeur_nom cvn WHERE cv.caracteristique_id = '%ID%' AND cv.caracteristique_valeur_id = cvn.caracteristique_valeur_id AND cvn.langue_id ='" . LANGUE . "' ORDER BY caracteristique_valeur_rang, caracteristique_valeur_nom_nom", true),
                )
        );
        $this->addForm($form);
				
        // $this->addForm($form);
        //-- Champs du formulaire

        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT pcv.produit_caracteristique_valeur_id";
        $sql.=", cn.caracteristique_nom_nom";
        $sql.=", cvn.caracteristique_valeur_nom_nom";
        $sql.=" FROM (produit_caracteristique_valeur pcv, caracteristique_valeur cv)";
        $sql.=" LEFT JOIN caracteristique_valeur_nom cvn ON(pcv.caracteristique_valeur_id = cvn.caracteristique_valeur_id AND cvn.langue_id = '".LANGUE."')";
        $sql.=" LEFT JOIN caracteristique_nom cn ON(cv.caracteristique_id = cn.caracteristique_id AND cn.langue_id = '".LANGUE."')";
        $sql.=" WHERE pcv.caracteristique_valeur_id=cv.caracteristique_valeur_id";
        if (!empty($formvars['produit']))
            $sql.=" AND pcv.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("VALEUR");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>