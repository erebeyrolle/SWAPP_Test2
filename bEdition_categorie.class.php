<?php

class bEdition_categorie extends BackModule {
	
	function bEdition_categorie($formvars = array()) {
		parent::BackModule($formvars);
	}
	
	function Form($formvars = array()) { 

		if(empty($formvars['id']))
			$this->path[] = "<span> &raquo; <a href='".$this->link_to('addForm')."'>Ajout d'une nouvelle cat&eacute;gorie de texte</a></span>";
		else
		{
			$sql = "SELECT edition_categorie_nom as data FROM edition_categorie WHERE edition_categorie_id = '".$formvars['id']."'";
			$this->sql->query($sql,SQL_ALL,SQL_ASSOC);			
			$data = $this->sql->record[0];
			$this->path[] = "<span> &raquo; <a href='".$this->link_to('modForm', array('id' => $formvars['id']))."'>Edition de la cat&eacute;gorie de texte  : <strong>".$data['data']."</strong></a></span>";
		}

		// SQL
		if(!empty($formvars['id'])) {
			$this->request=getTable("edition_categorie",$formvars['id']);
		}
		//-- SQL
		
		// Champs du formulaire
		$form = new BackForm("Nom","text","edition_categorie_nom");
		$form->addAttr('class','required');			
		$this->addForm($form);
		//-- Champs du formulaire

		return $this->displayForm(@$formvars['id']);
	}
	
	function Listing($formvars = array()) { 
		
		// SQL

		$sql="SELECT ec.edition_categorie_id";
		$sql.=", ec.edition_categorie_id";
		$sql.=", ec.edition_categorie_nom";
		$sql.=", COUNT(DISTINCT e.edition_id )";
		$sql.=" FROM (edition_categorie ec)";
		$sql.=" LEFT JOIN edition e ON (e.edition_categorie_id = ec.edition_categorie_id)";
		$sql.=" WHERE 1";

		$this->request=$sql;
		//-- SQL
		
		// LABELS
		$label = new BackLabel("ID");
		$this->addLabel($label);

		$label = new BackLabel("NOM",'<a href="'.BACK_URL.'html/edition/list/?e*edition_categorie_id=%ID%">%1%</a>');				
		$this->addLabel($label);
		
		$label = new BackLabel("TEXTES DANS LA CATEGORIE",'<a href="'.BACK_URL.'html/edition/list/?e*edition_categorie_id=%ID%">%1%</a>');		
		$this->addLabel($label);		
		//-- LABELS
		
		return $this->displayList($formvars['type']);
	}

//--
}
?>