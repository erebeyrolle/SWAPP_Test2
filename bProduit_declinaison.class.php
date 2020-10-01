<?php

class bProduit_declinaison extends BackModule {
			 			
		function bProduit_declinaison($formvars = array()) {
				parent::BackModule($formvars);
		}
		
	  function create($formvars = array(), $table = null, $langue = true) {
	  	
	  	$newid =  parent::create($formvars, $table, $langue);
	  	
			$produit = new Produit;
			
			$test = $produit->getDeclinaisonLite(array('produit_id' => $formvars['produit_d_id'], 'produit_d_id' => $formvars['produit_id']));
			if(empty($test[0])) {
				$produit->addDeclinaison($formvars['produit_d_id'],$formvars['produit_id']);
			}
			$arrAssociation = $produit->getDeclinaisonLite(array('produit_id' => $formvars['produit_id']));
			foreach($arrAssociation as $assoc) {
				if($formvars['produit_d_id'] != $assoc['produit_d_id']) {
					$test = $produit->getDeclinaisonLite(array('produit_id' => $formvars['produit_d_id'], 'produit_d_id' => $assoc['produit_d_id']));
					if(empty($test[0])) {
						$produit->addDeclinaison($formvars['produit_d_id'],$assoc['produit_d_id']);
					}
					
					$test = $produit->getDeclinaisonLite(array('produit_id' => $assoc['produit_d_id'], 'produit_d_id' => $formvars['produit_d_id']));
					if(empty($test[0])) {
						$produit->addDeclinaison($assoc['produit_d_id'],$formvars['produit_d_id']);
					}
				}
			}
	
			
	  	return $newid;
	  }	
	  
	  function update($formvars = array(), $table = null, $langue = true) {
	
	  }	
	
	  function delete($formvars = array(), $table = null, $langue = true) {
			$produit = new Produit;
	
			list($OldAssoc) = $produit->getDeclinaisonLite(array('produit_declinaison_id' => $formvars['id']));
			$produit->deleteDeclinaison($OldAssoc['produit_d_id']);
			
	  	return true;
	  }	
	
		function Form($formvars = array()) { 
	
			// SQL
			if(!empty($formvars['id']))
			{
				
				
				echo "Interdit (veuillez supprimer puis recr&eacute;er)";
				return false;
				/*
				$sql="SELECT pd.produit_declinaison_id";
				$sql.=", pd.produit_id";			
				$sql.=", pd.produit_d_id";
				$sql.=" FROM (produit_declinaison pd)";
				$sql.=" WHERE pd.produit_declinaison_id='".$formvars['id']."'";			
				
				$this->request=$sql;*/
			}
			//-- SQL
			
			// Champs du formulaire
			$form = new BackForm("Produit ID","hidden","produit_id");
			$this->addForm($form);
					
			$form = new BackForm("Produit","autocomplete","produit_d_id");
			$form->addAttr('class','required');			
			$form->addAttr('style','width:400px');		
			$form->setVar('method','/produit/custom/autocomplete/?produit_nom_nom=');
			$form->setVar('param','produit');
			$this->addForm($form);
			
	
			
			//-- Champs du formulaire
			if(!isset($formvars['id'])) $formvars['id']=0;
			return $this->displayForm($formvars['id']);
		}
		
		function Listing($formvars = array()) { 
			
			// SQL
			$sql="SELECT pd.produit_declinaison_id";
			$sql.=", p.produit_id";
			$sql.=", CONCAT(pn.produit_nom_nom, ' - ', p.produit_ref)";
			$sql.=", pn.produit_nom_couleur";
			$sql.=" FROM (produit_declinaison pd, produit p, produit_nom pn)";
			$sql.=" WHERE pd.produit_d_id = p.produit_id";
			$sql.=" AND pn.produit_id = p.produit_id";
			if(!empty($formvars['produit'])) $sql.=" AND pd.produit_id = '".$formvars['produit']."'";	
	
			$this->request=$sql;
			//-- SQL
			
			// LABELS
			$label = new BackLabel("ID");
			$this->addLabel($label);
			
			$label = new BackLabel("NOM");		
			$this->addLabel($label);		
	
			$label = new BackLabel("COULEUR INTERNE");		
			$this->addLabel($label);	
			
			//-- LABELS
			
			return $this->displayList($formvars['type']);
		}
	
	//--
}
?>