<?php

// PENDING : a revoir

class BackDroit {

    public $sql;

    function BackDroit() {
        $this->sql = Site_SQL::getInstance();
    }

    function get($module_page) {

        $var = array();
        $var['READ'] = $this->checkAuth(READ, $module_page);
        $var['ADD'] = $this->checkAuth(ADD, $module_page);
        $var['DEL'] = $this->checkAuth(DEL, $module_page);
        $var['MOD'] = $this->checkAuth(MOD, $module_page);
        $var['TRAD'] = $this->checkAuth(TRAD, $module_page);
        $var['EXP'] = $this->checkAuth(EXP, $module_page);
        $var['EXPERT'] = $this->checkAuth(EXPERT, $module_page);
        $var['DUP'] = true;

        if ($var['ADD'] || $var['MOD'] || $var['DEL'])
            $var['CONTEXT'] = true;
        else
            $var['CONTEXT'] = false;

        return $var;
    }

    function checkAuth($value, $module_page) {
        if (!isset($_SESSION['droit'][$module_page]['groupe_droit_somme']))
            return false;
        if ((int) ($_SESSION['droit'][$module_page]['groupe_droit_somme']) & (int) ($value))
            return true; //il a le droit de faire l'action $value
        else
            return false; // il a po le droit. toc'
    }

    function isAdministrateur($formvars=array()) {

        Tools::escapeDataToSql($formvars);

        $sql = "SELECT administrateur_id, administrateur_email";
        $sql.=" FROM back_administrateur";
        $sql.=" WHERE 1";
        $sql.=" AND administrateur_email = '" . $formvars['administrateur_email'] . "'";
        $sql.=" AND administrateur_password ='" . $formvars['administrateur_password'] . "'";
        $sql.=" LIMIT 1";

        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        return $this->sql->record;
    }

    function loadDroits() {
        $droits = array();
        $menu = array();
        // On r�cupere les groupes de l'utilisateur
        $sql = "SELECT g.groupe_id, g.groupe_nom";
        $sql .= " FROM back_groupe g, back_administrateur_groupe ag";
        $sql .= " WHERE ag.administrateur_id = '" . $_SESSION['connexion']['administrateur_id'] . "'";
        $sql .= " AND ag.groupe_id = g.groupe_id ";


        $sql = "SELECT g.groupe_id, g.groupe_nom FROM back_groupe g, back_administrateur_groupe ag WHERE ag.administrateur_id = '" . $_SESSION['connexion']['administrateur_id'] . "' AND ag.groupe_id = g.groupe_id ";
        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        $arrGTemp = $this->sql->record;
        
        $arrG = array();
        foreach($arrGTemp as $gTemp){
            $arrG[$gTemp['groupe_id']] = $gTemp['groupe_id'];
        }
        
        $i = 0;

        
        // On Compare avec les droits des GROUPES de l'utilisateur
        $sql = "
			(
				SELECT gd.groupe_id, gd.groupe_droit_somme, gd.groupe_id, ma.module_page as all_page, ma.module_titre as all_titre, ma.module_rang as all_rang, mp.module_page as parent_page, mp.module_titre as parent_titre, mp.module_rang as parent_rang, mp.module_icon as parent_icon, m.module_page as base_page, m.module_titre as base_titre, m.module_rang as base_rang, ma.actif_id as all_actif, mp.actif_id as parent_actif, m.actif_id as base_actif
				FROM (back_groupe_droit gd, back_module ma, back_module mp)
				LEFT JOIN back_module m ON ( m.module_parent_id = mp.module_id AND (m.module_parent_id IS NOT NULL OR m.module_parent_id <> '1'))
				WHERE gd.groupe_id IN (" . implode(',', $arrG) . ")
				AND mp.actif_id = '1'
				AND ma.module_id = gd.module_id
				AND mp.module_parent_id = ma.module_id			
				AND ma.module_parent_id IS NULL
	
			) UNION (
			
				SELECT gd.groupe_id, gd.groupe_droit_somme, gd.groupe_id, ma.module_page as all_page, ma.module_titre as all_titre, ma.module_rang as all_rang, mp.module_page as parent_page, mp.module_titre as parent_titre, mp.module_rang as parent_rang, mp.module_icon as parent_icon, m.module_page as base_page, m.module_titre as base_titre, m.module_rang as base_rang, ma.actif_id as all_actif, mp.actif_id as parent_actif, m.actif_id as base_actif
				FROM (back_groupe_droit gd, back_module ma, back_module mp)
				LEFT JOIN back_module m ON ( m.module_parent_id = mp.module_id AND (m.module_parent_id IS NOT NULL OR m.module_parent_id <> '1'))
				WHERE gd.groupe_id IN (" . implode(',', $arrG) . ")
				AND mp.actif_id = '1'				
				AND mp.module_id = gd.module_id
				AND mp.module_parent_id = ma.module_id
				AND ma.module_parent_id IS NULL
				
			) UNION (
			
				SELECT gd.groupe_id, gd.groupe_droit_somme, gd.groupe_id, ma.module_page as all_page, ma.module_titre as all_titre, ma.module_rang as all_rang, mp.module_page as parent_page, mp.module_titre as parent_titre, mp.module_rang as parent_rang, mp.module_icon as parent_icon, m.module_page as base_page, m.module_titre as base_titre, m.module_rang as base_rang, ma.actif_id as all_actif, mp.actif_id as parent_actif, m.actif_id as base_actif
				FROM (back_groupe_droit gd, back_module ma, back_module mp, back_module m)
				WHERE gd.groupe_id IN (" . implode(',', $arrG) . ")
				AND mp.actif_id = '1'				
				AND m.module_id = gd.module_id
				AND m.module_parent_id = mp.module_id		
				AND mp.module_parent_id = ma.module_id
				AND ma.module_parent_id IS NULL
				
			)
			ORDER BY all_rang, parent_rang, base_rang
			";
        /*
          UNION (

          SELECT gd.groupe_droit_somme, gd.groupe_id, ma.module_page as all_page, ma.module_titre as all_titre, ma.module_rang as all_rang, mp.module_page as parent_page, mp.module_titre as parent_titre, mp.module_rang as parent_rang, mm.module_page as base_page, mm.module_titre as base_titre, mm.module_rang as base_rang, ma.actif_id as all_actif, mp.actif_id as parent_actif, mm.actif_id as base_actif
          FROM (back_groupe_droit gd, back_module ma, back_module mp, back_module m, back_module mm)
          WHERE gd.groupe_id = '".$data['groupe_id']."'
          AND mm.module_id = gd.module_id
          AND mm.module_parent_id = m.module_id
          AND m.module_parent_id = mp.module_id
          AND mp.module_parent_id = ma.module_id
          AND ma.module_parent_id IS NULL

          )

         */

        // echo $sql;

        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        $req2 = $this->sql->record;

        // Fin --on boucle sur les groupes --
        
        foreach($req2 as $data2){
            // Gestion du menu

            if (!empty($menu[$data2['parent_titre']]) && !empty($data2['base_page'])) { // Menu deja present > sous menu :D
                $menu[$data2['parent_titre']]['smenu'][$data2['base_page']]['base_page'] = $data2['base_page'];
                $menu[$data2['parent_titre']]['smenu'][$data2['base_page']]['base_titre'] = $data2['base_titre'];
                $menu[$data2['parent_titre']]['smenu'][$data2['base_page']]['base_actif'] = $data2['base_actif'];
            } else { // Nouveau menu !!!
                $menu[$data2['parent_titre']]['parent_page'] = $data2['parent_page'];
                $menu[$data2['parent_titre']]['parent_titre'] = $data2['parent_titre'];
                $menu[$data2['parent_titre']]['parent_actif'] = $data2['parent_actif'];
                $menu[$data2['parent_titre']]['parent_icon'] = $data2['parent_icon'];
                if (!empty($data2['base_page'])) { // avec le sous module s'il existe !
                    $menu[$data2['parent_titre']]['smenu'][$data2['base_page']]['base_page'] = $data2['base_page'];
                    $menu[$data2['parent_titre']]['smenu'][$data2['base_page']]['base_titre'] = $data2['base_titre'];
                    $menu[$data2['parent_titre']]['smenu'][$data2['base_page']]['base_actif'] = $data2['base_actif'];
                }
            }

            // Gestion des droits
            if (!empty($droits[$data2['base_page']])) {  // Si le module existe deja
                //echo "Module Existant : ".$data2['base_page']."<br/>";
                //echo "Val existant : ".$droits[$data2['base_page']]['groupe_droit_somme']." Val nouveau : ".$data2['groupe_droit_somme']."<br/>";
                // on check et on sum() si besoin
                //echo $droits[$data2['base_page']]['groupe_droit_somme']." & ".$data2['groupe_droit_somme']." = ".((int)($droits[$data2['base_page']]['groupe_droit_somme']) & (int)($data2['groupe_droit_somme']))."<br>";
                if (!((int) ($droits[$data2['base_page']]['groupe_droit_somme']) & (int) ($data2['groupe_droit_somme']))) {
                    //echo "Droit limitant: on cumule <br>";
                    $droits[$data2['base_page']]['groupe_droit_somme'] += $data2['groupe_droit_somme'];
                } else {
                    $dumb = 0;
                    $base1 = (int) ($droits[$data2['base_page']]['groupe_droit_somme']);
                    $base2 = (int) ($data2['groupe_droit_somme']);
                    $idem = ($base1 & $base2);
                    //echo "AVANT ".$base1." ".$base2." ".$idem."dumb = ".$dumb."<br />";
                    $dumb += $idem;
                    while ($idem > 0) {
                        $base1 = $base1 - $idem;
                        $base2 = $base2 - $idem;
                        $idem = ($base1 & $base2);
                        $dumb += $idem;
                        //echo "PENDANT ".$base1." ".$base2." ".$idem."dumb = ".$dumb."<br />";
                    }
                    //echo "APRES ".$base1." ".$base2." ".$idem."dumb = ".$dumb."<br />";
                    $droits[$data2['base_page']]['groupe_droit_somme'] = $base1 + $base2 + $dumb;
                    //echo $data2['base_page'].$droits[$data2['base_page']]['groupe_droit_somme'];
                }
            } elseif (!empty($droits[$data2['parent_page']])) {  // Si le module existe deja
                //echo "Module Existant : ".$data2['parent_page']."<br/>";
                //echo "Val existant : ".$droits[$data2['module_page']]." Val nouveau : ".$data2['groupe_droit_somme']."<br/>";
                // on check et on sum() si besoin
                //echo $droits[$data2['module_page']]." & ".$data2['groupe_droit_somme']." = ".((int)($droits[$data2['module_page']]) & (int)($data2['groupe_droit_somme']))."<br>";
                if (!((int) ($droits[$data2['parent_page']]['groupe_droit_somme']) & (int) ($data2['groupe_droit_somme']))) {
                    //echo "Droit limitant: on cumule <br>";
                    $droits[$data2['parent_page']]['groupe_droit_somme'] += $data2['groupe_droit_somme'];
                } else {
                    $dumb = 0;
                    $base1 = (int) ($droits[$data2['parent_page']]['groupe_droit_somme']);
                    $base2 = (int) ($data2['groupe_droit_somme']);
                    $idem = ($base1 & $base2);
                    //echo "AVANT ".$base1." ".$base2." ".$idem."<br />";
                    $dumb += $idem;
                    while ($idem > 0) {
                        $base1 = $base1 - $idem;
                        $base2 = $base2 - $idem;
                        $idem = ($base1 & $base2);
                        $dumb += $idem;
                        //echo "PENDANT ".$base1." ".$base2." ".$idem."<br />";
                    }
                    //echo "APRES ".$base1." ".$base2." ".$idem."<br />";
                    $droits[$data2['parent_page']]['groupe_droit_somme'] = $base1 + $base2 + $dumb;
                }
            } else { // Si le module est nouveau
                //echo "Nouveau Module : ".$data2['module_page']."<br/>";
                // nouveau droit on insert dans le tab
                if (!empty($data2['base_page'])) {
                    $droits[$data2['base_page']]['groupe_droit_somme'] = $data2['groupe_droit_somme'];
                    $droits[$data2['base_page']]['module_page'] = $data2['base_page'];
                    $droits[$data2['base_page']]['module_titre'] = $data2['base_titre'];
                } else {
                    $droits[$data2['parent_page']]['groupe_droit_somme'] = $data2['groupe_droit_somme'];
                    $droits[$data2['parent_page']]['module_page'] = $data2['parent_page'];
                    $droits[$data2['parent_page']]['module_titre'] = $data2['parent_titre'];
                }
            }
        }

        // FIN --On Compare avec les droits des GROUPES de l'utilisateur--
        //}
        // Fin --on boucle sur les groupes --
        //print_r($menu);

        $_SESSION['droit'] = $droits;
        $_SESSION['menu'] = $menu;


        //echo "<pre>";
        //print_r($_SESSION);exit;
        //echo "</pre>";
    }

}