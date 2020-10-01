<?php
class bProduit_note extends BackModule {

	function bProduit_note($formvars = array()) {
		parent::BackModule($formvars);
	}
	
	function create($formvars = array())
	{
		return parent::create($formvars);
	}		

	function Form($formvars = array()) { 

		if(empty($formvars['id']))
			$this->path[] = "<span> &raquo; <a href='".$this->link_to('addForm')."'>Ajout d'un nouvel avis</a></span>";
		else
		{
			$sql = "SELECT CONCAT('# ', produit_note_id ,'') as data FROM produit_note WHERE produit_note_id = '".$formvars['id']."'";
			$this->sql->query($sql,SQL_ALL,SQL_ASSOC);	
			$data = $this->sql->record[0];
			
			$this->path[] = "<span> &raquo; <a href='".$this->link_to('modForm', array('id' => $formvars['id']))."'>Edition de l'avis : <strong>".$data['data']."</strong></a></span>";
		}

		// SQL
		if(!empty($formvars['id']))
		{
			$sql="SELECT produit_note_id, produit_id, client_id, notation_id, produit_note_titre, produit_note_texte";
			$sql.=", produit_note_statut_id, langue_id";
			$sql.=" FROM (produit_note)";
			$sql.=" WHERE produit_note_id='".$formvars["id"]."'";

			$this->request=$sql;
		}
		//-- SQL

		
		// Champs du formulaire
		//GROUP
		$form = new BackForm("Informations","group");			
		$this->addForm($form);		
		//--GROUP
		
		
		$form = new BackForm("Produit ID","hidden","produit_id");
		$this->addForm($form);

		/*		
		$form =& $this->addNew('BackForm',array("Produit","select","produit_id"));
		$form->addAttr('class','required');		
		$form->addOptionSQL(array("SELECT p.produit_id, pn.produit_nom_nom FROM produit p, produit_nom pn WHERE p.produit_id = pn.produit_id AND pn.langue_id = '".LANGUE."' ORDER BY produit_id"));
		$this->addForm($form);
		*/
		
		$form = new BackForm("Client","select","client_id");
		$form->addAttr('class','required');		
		$form->addOptionSQL(array("SELECT c.client_id, c.client_nom FROM client c ORDER BY client_id"));
		$this->addForm($form);		

		$form = new BackForm("Notation","select","notation_id");
		$form->addAttr('class','required');		
		$form->addOptionSQL(array("SELECT n.notation_id, nn.notation_nom_nom FROM notation n, notation_nom nn WHERE n.notation_id = nn.notation_id AND nn.langue_id = '".LANGUE."' ORDER BY langue_id"));
		$this->addForm($form);

		$form = new BackForm("Titre Avis","text","produit_note_titre");
		$form->addAttr('class','required');			
		$this->addForm($form);		

		$form = new BackForm("Note Avis","textarea","produit_note_texte");
		$form->addAttr('class','required');			
		$this->addForm($form);		

		$form = new BackForm("Notation","select","produit_note_statut_id");
		$form->addAttr('class','required');		
		$form->addOptionSQL(array("SELECT pns.produit_note_statut_id, produit_note_statut_nom FROM produit_note_statut pns ORDER BY pns.produit_note_statut_id"));
		$this->addForm($form);		
		//-- Champs du formulaire
		
		if(!isset($formvars['id'])) $formvars['id']=0;
		return $this->displayForm($formvars['id'],$formvars['div']);
	}
	
	function Listing($formvars = array()) { 
		
		// RECHERCHE
		$form = new BackForm("Produit","select","pnote.produit_id");
		$form->addOption("","---");
		$form->addOptionSQL(array("SELECT produit_id, produit_nom_nom FROM produit_nom WHERE langue_id = '".LANGUE."' ORDER BY produit_nom_nom"));
		$this->addForm($form);
		//-- RECHERCHE
		
		// SQL
		$sql="SELECT produit_note_id, produit_note_id, pnote.client_id, CONCAT(notation_nom_nom,' (',n.notation_valeur,')')";
		$sql.=", DATE_FORMAT(produit_note_date_ajout,'%d-%m-%Y %H:%i:%s')";
		$sql.=", CONCAT('<b>',pnote.produit_note_titre,'</b><br/>',pnote.produit_note_texte)";
		$sql.=", pns.produit_note_statut_id";
		$sql.=", l.langue_img";
		$sql.= " FROM (produit_note pnote)";
		$sql.= " LEFT JOIN produit_nom pnom ON (pnom.produit_id=pnote.produit_id)";
		$sql.= " LEFT JOIN notation n ON (n.notation_id=pnote.notation_id)";
		$sql.= " LEFT JOIN notation_nom nnom ON (nnom.notation_id=pnote.notation_id)";
		$sql.= " LEFT JOIN langue l ON (l.langue_id=pnote.langue_id)";
		$sql.= " LEFT JOIN produit_note_statut pns ON (pns.produit_note_statut_id=pnote.produit_note_statut_id)";
		$sql.= " WHERE 1";	
		
		if(!empty($formvars['produit'])) $sql.=" AND pnote.produit_id = '".$formvars['produit']."'";	

//echo $sql;

			
		$this->request=$sql;
		//-- SQL
		
		// LABELS
		$label = new BackLabel("ID");
		$this->addLabel($label);
		
		$label = new BackLabel("CLIENT ID");
		$this->addLabel($label);
		
		$label = new BackLabel("VALEUR");
		$this->addLabel($label);

		$label = new BackLabel("DATE");
		$this->addLabel($label);
		
		$label = new BackLabel("AVIS");
		$this->addLabel($label);

		$label = new BackLabel("STATUT","statut");
		$this->addLabel($label);		
		//-- LABELS
		
		return $this->displayList($formvars['type']);
	}

//--
}
?>