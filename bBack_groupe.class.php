<?php

// PENDING : revoir la gestion des droits

class bBack_groupe extends BackModule {

    function bBack_groupe($formvars = array()) {
        parent::BackModule($formvars);
    }

    function delete($formvars = array()) {
        $sql = "DELETE FROM back_groupe WHERE groupe_id = '" . $formvars['back_id'] . "'";
        return $this->sql->query($sql);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouveau groupe</a></span>";
        else {
            $sql = "SELECT CONCAT(groupe_nom,' #', groupe_id) as data FROM back_groupe WHERE groupe_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du groupe : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT groupe_id";
            $sql.=", groupe_nom";
            $sql.=", groupe_code";
            $sql.=", groupe_rang";
            $sql.=" FROM (back_groupe bg)";
            $sql.=" WHERE groupe_id = '" . $formvars['id'] . "'";

            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        $form = new BackForm("Nom", "text", "groupe_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Code", "text", "groupe_code");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Rang", "text", "groupe_rang");
        $form->addAttr('class', 'required');
        $this->addForm($form);
        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // SQL
        $sql = "SELECT g.groupe_id";
        $sql.=", g.groupe_id";
        $sql.=", g.groupe_nom";
        $sql.=", g.groupe_code";
        $sql.=", IF(g.groupe_id = 1, 'Tous' , IF( COUNT(gd.groupe_droit_id) > 0, 'Voir' , 'Creer' ))";
        $sql.=", g.groupe_rang";
        $sql.=" FROM (back_groupe g)";
        $sql.=" LEFT JOIN back_groupe_droit gd ON (g.groupe_id = gd.groupe_id)";
        $sql.=" WHERE 1";

        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("CODE");
        $this->addLabel($label);

        $label = new BackLabel("DROITS", 'custom');
        $label->setVar('option', array("details"));
        $this->addLabel($label);

        $label = new BackLabel("RANG");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

    function details($formvars = array()) {
        if ($formvars['id'] == 1) {
            echo "Le groupe Administrateur poss&egrave;de tous les droits sur tous les modules.";
            exit;
        }

        function insertdroit($module, $somme, $zeId) {
			$db = Site_SQL::getInstance();
            //"SELECT groupe_droit_id FROM user_groupe_droit WHERE groupe_id = '".$groupe."' AND groupe_module_id = '".$module."'";
            //echo $sql."\n<br />";
            //$req = mysql_query($sql)or die ($sql.'Erreur '.mysql_errno().' : ' . mysql_error());

            $sql = " INSERT INTO back_groupe_droit (groupe_id, module_id, groupe_droit_somme) VALUES ('" . $zeId . "','" . $module . "','" . $somme . "') ";
            // mysql_query($sql) or die($sql . 'Erreur ' . mysql_errno() . ' : ' . mysql_error());
            $db->query($sql);
            //echo $sql1."\n<br />";
            //$req1 = mysql_query($sql1)or die ($sql1.'Erreur '.mysql_errno().' : ' . mysql_error());

            return $sql;
        }

        //print_r($_GET);
        if (isset($_GET['submitBTN']) && $_GET['submitBTN'] == "VALIDER") {
            //print_r($_POST);
            //exit;
            $sql = " DELETE FROM back_groupe_droit WHERE groupe_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql);
            // mysql_query($sql) or die($sql . 'Erreur ' . mysql_errno() . ' : ' . mysql_error());

            foreach ($_GET as $name => $value) {
                list($what, $module_id) = explode("_", $name);

                if ($what == "cb") {
                    $sumDroit = 0;
                    foreach ($value as $id => $droit_value) {
                        $sumDroit += $droit_value;
                    }

                    if ($sumDroit > READ && !($sumDroit & READ))
                        $sumDroit += READ;

                    insertdroit($module_id, $sumDroit, $formvars['id']);
                }
            }
            ?>
            <script type="text/javascript">
                alert('ok !')

            </script>
            <?php
            //	exit;
        }



        $sql0 = "SELECT d.droit_id, d.droit_nom, d.droit_code, d.droit_valeur FROM back_droits d ORDER BY d.droit_valeur";
        $this->sql->query($sql0, SQL_ALL, SQL_ASSOC);
        //<form action='index.php' method='post' name='form'>
        echo "<br />
	<form onsubmit=\"checkForm(this);return false;\" action='" . $_SERVER['REDIRECT_URL'] . "' method='post' name='miniForm'>
	<input type='hidden' name='submitBTN' value='' />
	<input type='hidden' name='groupe_id' id='groupe_id' value='" . $formvars['id'] . "' />
	<table class='records' cellpadding='0' cellspacing='0'>";
        echo"<tr>";

        echo "<td width='200'>&nbsp;</td>";
        foreach ($this->sql->record as $data0) {
            echo "<th width='50' class='menu_item_head' height='16' style='text-align:center;'>" . $data0['droit_nom'] . "</th>";
        }
        echo"</tr>";

        $sql1 = "
	SELECT mp.module_id as parent_id, mp.module_nom as parent_nom, m.module_id as base_id, m.module_nom as base_nom
	FROM back_module mp
	LEFT JOIN back_module m ON (m.module_parent_id = mp.module_id)
	WHERE mp.module_parent_id = '1'
	ORDER BY mp.module_rang, m.module_rang
	";
        //echo $sql1;
        // $req1 = mysql_query($sql1) or die($sql1 . 'Erreur ' . mysql_errno() . ' : ' . mysql_error());
        $this->sql->query($sql1, SQL_ALL, SQL_ASSOC);

        $bool = 1;
        $curParentID = 0;
        $first = true;
        foreach ($this->sql->record as $data1) {

            // On Boucle sur tous les MODULES
            // Si nouveaux
            if ($curParentID != $data1['parent_id']) {
                echo "</table>";
                if (!$first)
                    echo "<br/>";
                else
                    $first = false;

                //print_r($disable);
                $disable = array();
                //print_r($disable);

                echo "<table class='records' id='p" . $data1['parent_id'] . "' cellpadding='0' style='margin-top:10px;' cellspacing='0'>";
                if ($bool) {
                    echo "<tr class='unselect_enr0'>";
                    $bool = 0;
                } else {
                    echo "<tr class='unselect_enr1'>";
                    $bool = 1;
                }


                $curParentID = $data1['parent_id'];
                $sql2 = "
							SELECT groupe_droit_somme
							FROM `back_groupe_droit`
							WHERE groupe_id = '" . $formvars['id'] . "'
							AND module_id = '" . $data1['parent_id'] . "'
						";
                //<img src='".BACK_URL."/charte/add.gif' style='float:left;' onclick='switch_tr(\"".$curParentID."\")'>
                //echo "<td width='200'><b>".$data1['parent_nom'].$data1['groupe_droit_somme']."</b></td>";
                echo "<td width='200'><b>" . $data1['parent_nom'] . "</b></td>";
                // $req2 = mysql_query($sql2) or die($sql2 . 'Erreur ' . mysql_errno() . ' : ' . mysql_error());
                $this->sql->query($sql2, SQL_ALL, SQL_ASSOC);
                // $data2 = mysql_fetch_array($req2);
				
				$data2 = !empty($this->sql->record) ? $this->sql->record[0] : 0;
				

                $sql3 = "SELECT droit_id, droit_code, droit_nom, droit_valeur FROM `back_droits`";
                // $req3 = mysql_query($sql3) or die($sql3 . 'Erreur ' . mysql_errno() . ' : ' . mysql_error());
                $this->sql->query($sql3, SQL_ALL, SQL_ASSOC);
                // while ($data3 = mysql_fetch_array($req3)) {
                foreach ($this->sql->record as $data3) {
                    $disabled = "";
                    $checked = "";
                    //echo $data2['groupe_droit_somme'];
                    if ((int) ($data2['groupe_droit_somme']) & (int) ($data3['droit_valeur'])) {
                        $disabled = "";
                        $checked = "checked";
                        $disable[$data1['parent_id']][$data3['droit_id']] = true;
                        //echo 'lol';
                    }

                    echo "<td width='50' align=center style='text-align:center;'>";
                    ?>
                    <input onclick="toggleall('p<?= $data1['parent_id']
                    ?>_<?= $data3['droit_id'] ?>')" root="<?= $data3['droit_id'] ?>"  type="checkbox" name="cb_<?= $data1['parent_id'] ?>[]" value="<?= $data3['droit_valeur'] ?>" <?= $disabled ?> <?= $checked ?>>
                           <?
                           echo "</td>";
                       }

                       echo "</tr>";

                       if ($data1['base_id']) {

                           if ($bool) {
                               echo "<tr class='unselect_enr0 tr_" . $curParentID . "'>";
                               $bool = 0;
                           } else {
                               echo "<tr class='unselect_enr1 tr_" . $curParentID . "'>";
                               $bool = 1;
                           }

                           $sql2 = "SELECT groupe_droit_somme FROM `back_groupe_droit` WHERE groupe_id = '" . $formvars['id'] . "' AND module_id = '" . $data1['base_id'] . "'";

                           // $req2 = mysql_query($sql2) or die($sql2 . 'Erreur ' . mysql_errno() . ' : ' . mysql_error());
                           $this->sql->query($sql2, SQL_ALL, SQL_ASSOC);
                           // $data2 = mysql_fetch_array($req2);
						   $data2 = !empty($this->sql->record) ? $this->sql->record[0] : 0;
                           //$data2 = $this->sql->record[0];

                           echo "<td align='right'>" . $data1['base_nom'] . $data2['groupe_droit_somme'] . "</td>";

                           $sql3 = "SELECT droit_id, droit_code, droit_nom, droit_valeur FROM `back_droits` ";
                           $this->sql->query($sql3, SQL_ALL, SQL_ASSOC);

                           foreach ($this->sql->record as $data3) {
                               $disabled = "";
                               $checked = "";
                               if ((int) ($data2['groupe_droit_somme']) & (int) ($data3['droit_valeur'])) {
                                   $disabled = "";
                                   $checked = "checked";
                               }
                               if (!empty($disable[$data1['parent_id']][$data3['droit_id']])) {
                                   $disabled = "disabled";
                                   $checked = "";
                               }
                               echo "<td width='50' style='text-align:center;'>";
                               //echo $disable[$data1['parent_id']][$data3['droit_id']];
                               ?>
                        <input onclick="check('p<?= $data1['parent_id']
                               ?>_<?= $data3['droit_id'] ?>')" type="checkbox" parent="<?= $data1['parent_id'] ?>" base="<?= $data3['droit_id'] ?>" name="cb_<?= $data1['base_id'] ?>[]" value="<?= $data3['droit_valeur'] ?>" <?= $disabled ?> <?= $checked ?>>
                               <?
                               echo "</td>";
                           }


                           echo "</tr>";
                       }

                       /*
                         id="idcb_<?= $data1['parent_id'] ?>_<?= $data1['base_id'] ?>_<?= $data3['droit_id'] ?>"
                        */
                   } else {

                       $sql2 = "SELECT groupe_droit_somme FROM `back_groupe_droit` WHERE groupe_id = '" . $formvars['id'] . "'AND module_id = '" . $data1['base_id'] . "'";

                       $this->sql->query($sql2, SQL_ALL, SQL_ASSOC);
                       //$data2 = $this->sql->record[0];
					   $data2 = !empty($this->sql->record) ? $this->sql->record[0] : 0;


                       if ($bool) {
                           echo "<tr class='unselect_enr0 tr_" . $curParentID . "'>";
                           $bool = 0;
                       } else {
                           echo "<tr class='unselect_enr1 tr_" . $curParentID . "'>";
                           $bool = 1;
                       }
                       echo "<td align=right>" . $data1['base_nom'] . "</td>";

                       $sql3 = "SELECT droit_id, droit_code, droit_nom, droit_valeur FROM `back_droits` ";
                       $this->sql->query($sql3, SQL_ALL, SQL_ASSOC);

                       foreach ($this->sql->record as $data3) {
                           $disabled = "";
                           $checked = "";
                           if ((int) ($data2['groupe_droit_somme']) & (int) ($data3['droit_valeur'])) {
                               $disabled = "";
                               $checked = "checked";
                           }
                           if (!empty($disable[$data1['parent_id']][$data3['droit_id']])) {
                               $disabled = "disabled";
                               $checked = "";
                           }
                           echo "<td width='50' align='center' style='text-align:center;'>";
                           ?>
                    <input onclick="check('p<?= $data1['parent_id'] ?>_<?= $data3['droit_id'] ?>')" type="checkbox" parent="<?= $data1['parent_id'] ?>" base="<?= $data3['droit_id'] ?>" name="cb_<?= $data1['base_id'] ?>[]" value="<?= $data3['droit_valeur'] ?>" <?= $disabled ?> <?= $checked ?>>
                    <?
                    echo "</td>";
                }

                echo "</tr>";
            }

            /* $sql2 = "
              SELECT g.groupe_id, g.groupe_nom, IF(gd.groupe_droit_somme,gd.groupe_droit_somme,'0') as groupe_droit_somme, gd.groupe_droit_id
              FROM back_groupe g
              LEFT JOIN back_groupe_droit gd ON ( gd.groupe_id = g.groupe_id
              AND gd.module_id = '".$data1['module_id']."' )
              ORDER BY g.groupe_rang
              ";
              $req2 = mysql_query($sql2)or die ($sql2.'Erreur '.mysql_errno().' : ' . mysql_error());
             */ /*
              while ($data2=mysql_fetch_array($req2))
              {
              echo "<td class='menu_item' align='center'>";
              $sql3 ="SELECT droit_id, droit_code, droit_nom, droit_valeur
              FROM `droits` ";
              $req3 = mysql_query($sql3)or die ($sql3.'Erreur '.mysql_errno().' : ' . mysql_error());
              while ($data3=mysql_fetch_array($req3))
              {
              $disabled = "";
              $checked = "";

              if($data1['module_id'] == 1) {
              $disabled = "disabled";
              if ($data2['groupe_id'] == 5)
              $checked = "checked";
              }
              else {
              if ($data2['groupe_id'] == 5) {
              $disabled = "disabled";
              $checked = "";
              }
              else {
              if ((int)($data2['groupe_droit_somme']) & (int)($data3['droit_valeur']))
              {
              $disabled = "";
              $checked = "checked";
              }
              }
              }

              //if ($data2['groupe_somme_droit'] & $data3['droit_valeur']) $test= "1"; else $test="0";
              $onclick ="";


              ?>
              <input style="border:0" onmouseover="description('<?= $data3['droit_nom'] ?>')" onmouseout="destroy()" type="checkbox" name="cb_<?= $data2['groupe_id'] ?>_<?= $data1['module_id'] ?>_<? if($data2['groupe_droit_id']) echo $data2['groupe_droit_id']; else echo '0'; ?>_<?= $data3['droit_id'] ?>" id="idcb_<?= $data2['groupe_id'] ?>_<?= $data1['module_id'] ?>_<?= $data3['droit_id'] ?>" value="<?= $data3['droit_valeur'] ?>" <?= $disabled ?> <?= $checked ?>>
              <!--<input type="text" id="checkbox" size="1" style="width:200px" value="<? echo $data2['groupe_somme_droit']." & ".$data3['droit_valeur']." = ".$test; ?>" <?= $disabled ?>>-->
              <?
              //.$data2['groupe_somme_droit'].

              }
              echo "</td>";
              }
             */
            echo"</tr>";
        }
        echo "</table>";
        echo "<br style='clear:both;'/><br /><table width='100%' cellpadding='0' cellspacing='0'>";
        echo"<tr>";
        echo "<td><input onclick=\"document.miniForm.submitBTN.value=this.value;\" type='submit' value='VALIDER' style='height:30px;width:100%'/></td>";
        echo"</tr>";
        echo"</table><br /></form>";
        ?>
        <script type="text/javascript">
            function toggleall(what)
            {
                var tmp = what.replace(/(p)/, "");
                var data = tmp.split('_');
                var table = 'p'+data[0];
                var choix = document.getElementById(table).getElementsByTagName('input');
                for (var i=0;i<choix.length;i++)
                {
                    if (choix[i].getAttribute('parent') == data[0] && choix[i].getAttribute('base') == data[1]) {
                				
                        if (choix[i].disabled) {
                            choix[i].disabled = false;
                        }
                        else {
                            choix[i].checked = false;
                            choix[i].disabled = true;
                        }
                    }
                }
                		
                		
                //alert(choix.length);
                		
            }
                	
                	
            function switch_tr(where)
            {
                table = '#p'+where;
                tr = '.tr_'+where;
                		
                //alert($$(table+' '+tr).length);
                		
                $$(table+' '+tr).setStyles({display: 'none'});
                $$(table+' '+tr+' td').setStyles({display: 'none'});
            }
                	
            function check(what)
            {
                var tmp = what.replace(/(p)/, "");
                var data = tmp.split('_');
                var table = 'p'+data[0];
                var choix = document.getElementById(table).getElementsByTagName('input');
                var dumb = 0;
                var test = 0;
                for (var i=0;i<choix.length;i++)
                {
                    if (choix[i].getAttribute('root') == data[1])
                        tocheck = choix[i];
                    if (choix[i].getAttribute('parent') == data[0] && choix[i].getAttribute('base') == data[1]) {
                        dumb++;
                				
                        if (choix[i].checked)
                            test++;
                    }
                			
                }
                if (dumb==test) {
                    //alert('lol');
                    tocheck.checked = true;
                    toggleall(what)
                }
                		
                //alert(dumb);
                //alert(test);
            }
        </script>
        <!-- FIN DU SCRIPT -->

        <?php
    }

//--
}
?>
