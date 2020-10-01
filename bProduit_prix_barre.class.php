<?php

class bProduit_prix_barre extends BackModule {

    function bProduit_prix_barre($formvars = array()) {
        parent::BackModule($formvars);
    }
    
    
    function create($formvars = array(), $table = null, $langue = true) {

        // on recupere la taxe
        // on recup la taxe meme en creation
      	$sql = "SELECT t.taxe_id";
        $sql.=" FROM (produit p, taxe t)";
        $sql.=" WHERE p.produit_id = '" . $formvars['produit_id'] . "'";
        $sql.=" AND t.taxe_id = p.taxe_id	";
        $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
        $formvars['taxe_id'] = $this->sql->record['taxe_id'];
          
        $taxe = new Taxe;
        
				$formvars['produit_prix_barre_prix'] = str_replace(',','.',$formvars['produit_prix_barre_prix']);
				
        $formvars['produit_prix_barre_prix'] = $taxe->getPrixHt($formvars['produit_prix_barre_prix'], $formvars['taxe_id']);
        
        $return = parent::create($formvars, $table, $langue);

				// on met a jour les pourcentages
        $prixBarre = new Prix_Barre;
        $prixBarre->updateValeur($return);

        return $return;
    }
    
    function update($formvars = array(), $table = null, $langue = true) {

          
        $taxe = new Taxe;
				$formvars['produit_prix_barre_prix'] = str_replace(',','.',$formvars['produit_prix_barre_prix']);
        $formvars['produit_prix_barre_prix'] = $taxe->getPrixHt($formvars['produit_prix_barre_prix'], $formvars['taxe_id']);
        
        $return = parent::update($formvars, $table, $langue);
        
        // on met a jour les pourcentages
        $prixBarre = new Prix_Barre;
        $prixBarre->updateValeur($formvars['back_id']);

        return $return;
    }

    function Form($formvars = array()) {

        // SQL
        if (!empty($formvars['id'])) {

            // on regarde si le produit a des attributs
            $produit = new Produit;
            $withAtt = $produit->withAtt($formvars['id']);

            $sql = "SELECT *";
            $sql.=", ROUND(produit_prix_barre_prix * (1 + taxe_taux),2) as produit_prix_barre_prix";
            $sql.=" FROM (produit_prix_barre ppb, produit p, taxe t)";
            $sql.=" WHERE ppb.produit_prix_barre_id = '" . $formvars['id'] . "'";
            $sql.=" AND ppb.produit_id = p.produit_id	";
            $sql.=" AND t.taxe_id = p.taxe_id	";

            $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
            $formvars['produit_id'] = $this->sql->record['produit_id'];

            $this->request = $sql;
        }
        
        //-- SQL

        // Champs du formulaire
        $form = new BackForm("Produit ID", "hidden", "produit_id");
        $this->addForm($form);
        $form = new BackForm("Taxe ID", "hidden", "taxe_id");
        $this->addForm($form);
        
        $form = new BackForm("Attribut", "select", "produit_attribut_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("
        	SELECT pa.produit_attribut_id, IF(povn2.produit_option_valeur_nom != '' && povn.produit_option_valeur_nom != '', CONCAT(povn.produit_option_valeur_nom,' & ',povn2.produit_option_valeur_nom), IF(povn2.produit_option_valeur_nom != '', povn2.produit_option_valeur_nom, povn.produit_option_valeur_nom))
         	FROM (produit_attribut pa)
        	LEFT JOIN produit_option_valeur pov ON ( pov.produit_option_valeur_id = pa.produit_option_valeur_id )
        	LEFT JOIN produit_option_valeur_nom povn ON ( pov.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . LANGUE . "')
        	LEFT JOIN produit_option_valeur pov2 ON ( pov2.produit_option_valeur_id = pa.produit_option_valeur_id_bis )
        	LEFT JOIN produit_option_valeur_nom povn2 ON ( pov2.produit_option_valeur_id = povn2.produit_option_valeur_id AND povn2.langue_id='" . LANGUE . "')
        	WHERE pa.produit_id = '".$formvars['produit_id']."'
         "));
        $this->addForm($form);

       	$form = new BackForm("Prix Promo TTC", "text", "produit_prix_barre_prix");
        $this->addForm($form);
       	
       	$form = new BackForm("Date Debut", "datetime", "produit_prix_barre_date_debut");
        $this->addForm($form);
        
        $form = new BackForm("Date Fin", "datetime", "produit_prix_barre_date_fin");
        $this->addForm($form);
        
        $form = new BackForm("Etiquette", "select", "etiquette_id");
        $form->addOption("NULL", "---");
        $form->addOptionSQL(array("SELECT en.etiquette_id, en.etiquette_nom_nom FROM (etiquette_nom en) WHERE en.langue_id='" . LANGUE . "' ORDER BY en.etiquette_nom_nom"));
        $this->addForm($form);
       
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT ppb.produit_prix_barre_id ";
        $sql.=", ppb.produit_prix_barre_id";
        $sql.= ", IF(povn2.produit_option_valeur_nom != '' && povn.produit_option_valeur_nom != '', CONCAT(povn.produit_option_valeur_nom,' & ',povn2.produit_option_valeur_nom), IF(povn2.produit_option_valeur_nom != '', povn2.produit_option_valeur_nom, povn.produit_option_valeur_nom)) as produit_option_valeur_nom";
        $sql.=", ROUND(produit_prix_barre_prix * (1 + taxe_taux),2) as produit_prix_barre_prix";
        $sql.=", produit_prix_barre_percent";
        $sql.=", ROUND(produit_prix_barre_valeur * (1 + taxe_taux),2) as produit_prix_barre_valeur";
        $sql.=", ppb.produit_prix_barre_date_debut";
        $sql.=", ppb.produit_prix_barre_date_fin";
        $sql.=", en.etiquette_nom_nom";
        $sql.=" FROM (produit_prix_barre ppb, produit p, taxe t)";
        $sql.=" LEFT JOIN produit_attribut pa ON (pa.produit_id = p.produit_id AND ppb.produit_attribut_id = pa.produit_attribut_id)";
        $sql.=" LEFT JOIN etiquette_nom en ON (ppb.etiquette_id = en.etiquette_id AND en.langue_id = '".LANGUE."')";
        
        // opt 1
        $sql.=" LEFT JOIN produit_option_valeur pov ON ( pov.produit_option_valeur_id = pa.produit_option_valeur_id )";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn ON ( pov.produit_option_valeur_id = povn.produit_option_valeur_id AND povn.langue_id='" . LANGUE . "')";
        // opt 2
        $sql.=" LEFT JOIN produit_option_valeur pov2 ON ( pov2.produit_option_valeur_id = pa.produit_option_valeur_id_bis )";
        $sql.=" LEFT JOIN produit_option_valeur_nom povn2 ON ( pov2.produit_option_valeur_id = povn2.produit_option_valeur_id AND povn2.langue_id='" . LANGUE . "')";
        
        
        $sql.=" WHERE ppb.produit_id = p.produit_id";
        $sql.=" AND p.taxe_id = t.taxe_id";
        $sql.=" AND ppb.prix_barre_id IS NULL";
        if (!empty($formvars['produit']))
            $sql.=" AND ppb.produit_id = '" . $formvars['produit'] . "'";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);
				
				$label = new BackLabel("ATTRIBUT");
        $this->addLabel($label);
        
        $label = new BackLabel("PRIX PROMO TTC");
        $this->addLabel($label);
        
        $label = new BackLabel("POURCENTAGE");
        $this->addLabel($label);
        
        $label = new BackLabel("VALEUR");
        $this->addLabel($label);
				
        $label = new BackLabel("DATE DEBUT",'date');
        $label->setVar('option', array('%d/%m/%Y %H:%i'));
        $this->addLabel($label);
        $label = new BackLabel("DATE FIN",'date');
        $label->setVar('option', array('%d/%m/%Y %H:%i'));
        $this->addLabel($label);
        
        $label = new BackLabel("ETIQUETTE");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}
?>