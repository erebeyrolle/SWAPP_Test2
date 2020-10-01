<?php

class bAdmin_user extends BackModule {

    function bAdmin_user($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {
        $formvars['client_type_id'] = Client::TYPE_ANNONCEUR;
        $formvars['client_date_modif'] = date('d/m/Y H:i:s');
        if (empty($formvars['client_password'])) unset($formvars['client_password']);
        else {
            $client = new Client;
            $formvars['client_password'] = $client->passwordEncrypt($formvars['client_password']);
        }
        $return = parent::update($formvars,$table,$langue);
        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $client = new Client;
        $formvars['client_type_id'] = Client::TYPE_ANNONCEUR;
        $formvars['client_password'] = $client->passwordEncrypt($formvars['client_password']);
        $formvars['client_date_ajout'] = date('d/m/Y H:i:s');
        $formvars['client_date_modif'] = date('d/m/Y H:i:s');

        if ($client->get(array('client_email' => $formvars['client_email']))) {
            return false;
        }
        $return = parent::create($formvars,$table,$langue);
        $params = array(
            'client_id' => $return,
            'civilite_id' => $formvars['civilite_id'],
            'pays_id' => Pays::FRANCE,
            'adresse_nom' => $formvars['client_nom'],
            'adresse_prenom' => $formvars['client_prenom'],
            'adresse_default' => 1,
            'adresse_libelle' => 'Adresse par defaut'
        );
        $this->sql->insert('adresse', $params);
        return $return;
    }

    function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT *, '' as client_password FROM (client c)";
            $sql.= " WHERE 1";
            $sql.= " AND c.client_id = '" . $formvars['id'] . "'";
            $this->request = $sql;
        }

        //-- SQL
        // Champs du formulaire
        $form = new BackForm("boutique ID", "hidden", "boutique_id");
        $this->addForm($form);


        $form = new BackForm("Civ.", "select", "civilite_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT c.civilite_id, civilite_nom_nom FROM civilite c, civilite_nom cn WHERE cn.civilite_id=c.civilite_id AND cn.langue_id ='" . LANGUE . "' ORDER BY civilite_nom_nom"));
        $this->addForm($form);

        $form = new BackForm('Pr&eacute;nom', 'text', 'client_prenom');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Nom', 'text', 'client_nom');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Login', 'text', 'client_pseudo');
        $this->addForm($form);

        $form = new BackForm('Email', 'text', 'client_email');
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm('Mot de passe', 'text', 'client_password');
        if (empty($formvars['id'])) {
            $form->addAttr('class', 'required');
        }
        $form->setVar('comment', 'Si ce champs est renseign&eacute;, le mot de passe du client sera remplac&eacute;');
        $this->addForm($form);

        $form = new BackForm('Super Admin', 'select', 'client_su');
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "client_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT client_statut_id, client_statut_nom FROM client_statut ORDER BY client_statut_id"));
        $this->addForm($form);

        // $form->addAttr('class', 'required');
        // $form->addOption("", "---");
        // $form->addOptionSQL(array("SELECT au.client_id, au.client_nom_nom FROM client au"));
        // $form->addOptionSQL(array("(SELECT '2', 'Non') UNION (SELECT '1', 'Oui')"));

        //  $this->addForm($form);


        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id'], $formvars['div']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm('# ID', 'text', 'au.client_id');
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);


        $sql = "SELECT c.client_id, c.client_id";
        $sql.= ", CONCAT(c.client_prenom, ' ', c.client_nom) as client_nom";
        $sql.= ", c.client_email";
        $sql.=", ''";
        $sql.= " FROM (client c)";
        $sql.= " WHERE 1";
        $sql.=" AND c.client_type_id = ".Client::TYPE_ANNONCEUR;
        if (!empty($formvars['boutique']))
            $sql.=" AND c.boutique_id = '" . $formvars['boutique'] . "'";
        $this->request = $sql;


        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $list = new BackLabel('NOM');
        $this->addLabel($list);

        $list = new BackLabel('EMAIL', 'mail');
        $this->addLabel($list);

        $label = new BackLabel("DROITS", 'custom');
        $label->setVar('option', array("details"));
        $this->addLabel($label);



        //-- LABELS

        return $this->displayList($formvars['type']);
    }

    function details($formvars = array()) {
        $sql="SELECT * FROM client WHERE client_id = '".$formvars['id']."'";
        $user = $this->sql->getInit($sql);

        if ($user['client_su'] == 1) {
            echo "Le super admin poss&egrave;de tous les droits sur tous les modules.";
            exit;
        }

        function insertdroit($module, $somme, $zeId) {
            $db = Site_SQL::getInstance();
            $sql = " INSERT INTO back_groupe_droit (groupe_id, module_id, groupe_droit_somme) VALUES ('" . $zeId . "','" . $module . "','" . $somme . "') ";
            $db->query($sql);
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

        $sql1 = "SELECT mp.admin_module_id as parent_id, mp.admin_module_nom as parent_nom, m.admin_module_id as base_id, m.admin_module_nom as base_nom";
        $sql1.=" FROM admin_module mp";
        $sql1.=" LEFT JOIN admin_module m ON (m.parent_id = mp.admin_module_id)";
        $sql1.=" WHERE mp.parent_id IS NULL";
        $sql1.=" ORDER BY mp.admin_module_rang, m.admin_module_rang";
        $arrModule = $this->sql->getAll($sql1);
        echo $sql1;
        //<form action='index.php' method='post' name='form'>
        echo "<br />
	<form onsubmit=\"checkForm(this);return false;\" action='" . $_SERVER['REDIRECT_URL'] . "' method='post' name='miniForm'>
	<input type='hidden' name='submitBTN' value='' />
	<input type='hidden' name='groupe_id' id='groupe_id' value='" . $formvars['id'] . "' />
	<table class='records' cellpadding='0' cellspacing='0'>";
        echo"<tr>";

        Tools::dediLog($this->sql->record);
        echo "<td width='200'>&nbsp;</td>";
        foreach ($this->sql->record as $data0) {
            echo "<th width='50' class='menu_item_head' height='16' style='text-align:center;'>" . $data0['droit_nom'] . "</th>";
        }
        echo"</tr>";

        $bool = 1;
        $curParentID = 0;
        $first = true;
        foreach ($arrModule as $data1) {

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

}