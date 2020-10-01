<?php
class bCouleur extends BackModule {
	
	function bCouleur($formvars = array()) {
		parent::BackModule($formvars);
	}

	function update($formvars = array(),$table = null,$langue = true)
	{
		
  	$cara = new Caracteristique;
  	$frmcara = array();
  	$frmcara['caracteristique_valeur_visible_filtre'] = 1;
  	$frmcara['caracteristique_id'] = 19;
  	$frmcara['caracteristique_code'] = "col".$formvars['back_id'];
  	$frmcara['caracteristique_valeur_rang'] = $formvars['couleur_rang'];
  	$frmcara['caracteristique_valeur_nom_nom'] = $formvars['couleur_nom_nom'];
  	$frmcara['caracteristique_valeur_nom_image'] = $formvars['couleur_valeur'];
		$frmcara['caracteristique_valeur_nom_desc'] = $formvars['couleur_image'];
  	$frmcara['langue_id'] = LANGUE;
  	$cara->addBack($frmcara);        // MAJ
		
		return parent::update_multi($formvars,$table,$langue);
	}
	
	function create($formvars = array(),$table = null,$langue = true)
	{
		$return = parent::create_multi($formvars,$table,$langue);
		
		$cara = new Caracteristique;
		$frmcara = array();
		$frmcara['caracteristique_valeur_visible_filtre'] = 1;
		$frmcara['caracteristique_id'] = 19;
		$frmcara['caracteristique_valeur_rang'] = $formvars['couleur_rang'];
		$frmcara['caracteristique_valeur_nom_nom'] = $formvars['couleur_nom_nom'];
		$frmcara['langue_id'] = LANGUE;
		$frmcara['caracteristique_valeur_nom_image'] = $formvars['couleur_valeur'];
		$frmcara['caracteristique_valeur_nom_desc'] = $formvars['couleur_image'];
		$frmcara['caracteristique_code'] = "col".$return;
		$cara->addBack($frmcara);
		
		return $return;
	}	
	
	function delete($formvars = array(),$table = null,$langue = true)
	{
		
		$cara = new Caracteristique;
		$frmcara = array();
		$frmcara['caracteristique_id'] = 19;
		$frmcara['langue_id'] = LANGUE;
		$frmcara['caracteristique_code'] = "col".$formvars['back_id'];
		$cara->delback($frmcara);		
		
		return parent::delete_multi($formvars,$table,$langue);
	}	
	
	function Form($formvars = array()) { 

		if(empty($formvars['id']))
			$this->path[] = "<span> &raquo; <a href='".$this->link_to('addForm')."'>Ajout d'une nouvelle Couleur</a></span>";
		else
		{
			$sql = "SELECT couleur_nom_nom as data FROM couleur_nom WHERE couleur_id = '".$formvars['id']."' AND langue_id = '".LANGUE."'";
			$this->sql->query($sql,SQL_ALL,SQL_ASSOC);			
			$data = $this->sql->record[0];
			$this->path[] = "<span> &raquo; <a href='".$this->link_to('modForm', array('id' => $formvars['id']))."'>Edition de la couleur  : <strong>".$data['data']."</strong></a></span>";
		}

		// SQL
		if(!empty($formvars['id']))
		{
			$sql="SELECT co.couleur_id, couleur_parent_id, co.couleur_image, co.couleur_valeur, co.couleur_rang, con.couleur_nom_nom ";
			$sql.=" FROM (couleur co, couleur_nom con)";
			$sql.=" WHERE co.couleur_id='".$formvars['id']."'";
			$sql.=" AND co.couleur_id = con.couleur_id";
			$sql.=" AND con.langue_id='".LANGUE."'";	
			
			$this->request=$sql;
		}
		//-- SQL

		// Champs du formulaire				
		$form = new BackForm("Nom","text","couleur_nom_nom");
		$form->addAttr('class','required');			
		$form->setVar('translate',true);			
		$this->addForm($form);
		
		$form = new BackForm("Parent", "select", "couleur_parent_id");
		$form->addOption("NULL", "---");
		$form->addOptionSQL(array("SELECT c.couleur_id, cn.couleur_nom_nom FROM couleur c, couleur_nom cn WHERE c.couleur_parent_id IS NULL AND c.couleur_id = cn.couleur_id ORDER BY couleur_nom_nom", false));
		$this->addForm($form);
    
		$form = new BackForm("Valeur (ex. #000000)","text","couleur_valeur");
		$this->addForm($form);		

    $form = new BackForm("Image", "image", "couleur_image");
    $form->addAttr('size', 20);
    $this->addForm($form);
    
		$form = new BackForm("Rang","text","couleur_rang");
		$form->addAttr('class','required');			
		$this->addForm($form);		

						
		//-- Champs du formulaire
		if(!isset($formvars['id'])) $formvars['id']=0;
		return $this->displayForm($formvars['id']);
	}
	
	function Listing($formvars = array()) { 

		// RECHERCHE
		$form = new BackForm("Nom","text","con.couleur_nom_nom");
		$this->addForm($form);
		
    $form = new BackForm("Couleur", "select", "co.couleur_parent_id");
    $form->addOption("", "---");
    $form->addOptionSQL(array("SELECT co.couleur_id, con.couleur_nom_nom FROM couleur co, couleur_nom con WHERE co.couleur_id = con.couleur_id AND co.couleur_parent_id IS NULL ORDER BY couleur_nom_nom"));
    $this->addForm($form);
		//-- RECHERCHE
		
		// SQL
		$sql="SELECT co.couleur_id, co.couleur_id, copn.couleur_nom_nom, con.couleur_nom_nom";
    $sql.=", IF(co.couleur_parent_id IS NULL,COUNT(DISTINCT cenf.couleur_id),'--')";
		$sql.=" FROM (couleur co, couleur_nom con)";
		$sql.=" LEFT JOIN couleur_nom copn ON co.couleur_parent_id = copn.couleur_id";
    $sql.=" LEFT JOIN couleur cenf ON (cenf.couleur_parent_id = co.couleur_id)";
		$sql.=" WHERE 1 AND co.couleur_id = con.couleur_id AND con.langue_id = '".LANGUE."'";
        if (!isset($_GET['co*couleur_parent_id']) || empty($_GET['co*couleur_parent_id']) || $_GET['co*couleur_parent_id'] == 'NULL')
            $sql.=" AND co.couleur_parent_id IS NULL";
		
		
		$this->request=$sql;
		//-- SQL
		
		// LABELS
		$label = new BackLabel("ID");
		$this->addLabel($label);

		$label = new BackLabel("PARENT");
		$this->addLabel($label);
		
		$label = new BackLabel("NOM");
		$this->addLabel($label);

		
    $label = new BackLabel("SOUS-CATEGORIES", "listselect");
    $label->setVar('option', array("module" => "<a href='../../couleur/list/?co*couleur_parent_id=%ID%'>%IMG%</a>"));
    $this->addLabel($label);
    //-- LABELS
        
		$label = new BackLabel("TRADUCTIONS",'translate');		
		$this->addLabel($label);			
		//-- LABELS
		
		return $this->displayList($formvars['type']);
	}
//--
}
?>