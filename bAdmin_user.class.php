<?php

class bAdmin_user extends BackModule {

    public function bAdmin_user($formvars = array()) {
        parent::BackModule($formvars);
        $this->droits['ADD'] = false;
    }

    public function update($formvars = array(), $table = null, $langue = true) {
        $formvars['client_type_id'] = Client::TYPE_ANNONCEUR;
        $formvars['client_date_modif'] = date('d/m/Y H:i:s');
        if (empty($formvars['client_password'])) unset($formvars['client_password']);
        else {
            $client = new Client;
            $formvars['client_password'] = $client->passwordEncrypt($formvars['client_password']);
        }

        $sql =     "INSERT INTO     client_beneficiaire (client_id, client_beneficiaire_identifiant)
                    VALUES (" . addslashes($formvars['back_id']) . ", '" . addslashes($formvars['client_beneficiaire_identifiant']) . "')"
            . " ON DUPLICATE KEY UPDATE client_beneficiaire_identifiant = VALUES(client_beneficiaire_identifiant)"
        ;
        $this->sql->query($sql);
        $return = parent::update($formvars,$table,$langue);
        return $return;
    }

    public function create($formvars = array(), $table = null, $langue = true) {
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

    public function Form($formvars = array()) {


        if (!empty($formvars['id'])) {
            $sql = "SELECT *, '' as client_password FROM (client c)";
            $sql .= " LEFT JOIN client_beneficiaire cb ON cb.client_id = c.client_id ";
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

        $form = new BackForm('Login', 'text', 'client_beneficiaire_identifiant');
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

    public function Listing($formvars = array()) {

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

    public function details($formvars = array()) {
        $sql="SELECT * FROM client WHERE client_id = '".$formvars['id']."'";
        $user = $this->sql->getInit($sql);

        if ($user['client_su'] == 1) {
            echo "Le super admin poss&egrave;de tous les droits sur tous les modules.";
            exit;
        }

        $rights = new Rights();

        if (isset($_GET['submitBTN']) && $_GET['submitBTN'] == "VALIDER") {
            $sql = " DELETE FROM module_client WHERE client_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql);
            foreach ($_GET['droit'] as $key => $value) {
                $rights->insert($formvars['id'], $key, $value);
            }
            echo '<p>Les droits ont été mis à jour</p>';
        }

        $arrModule = $rights->getModule();
        $arrDroit = $rights->getModuleDroit();

        echo "<br />
	<form onsubmit=\"checkForm(this);return false;\" action='" . $_SERVER['REQUEST_URI'] . "' method='post' name='miniForm'>
	<input type='hidden' name='submitBTN' value='' />
	<input type='hidden' name='groupe_id' id='groupe_id' value='" . $formvars['id'] . "' />
    <table class='records' cellpadding='0' cellspacing='0'>
        <thead>
            <tr>
                <td width=\"12%\">&nbsp;</td>";
        foreach ($arrDroit as $droit) {
            echo '<td width="11%" style="text-align: center;"><strong style="cursor:pointer;" class="column' . $droit['module_droit_id'] . '"></strong>' . $droit['module_droit_details'] . '</td>';
        }
        echo "
            </tr>
        </thead>
        <tbody>";
        foreach ($arrModule as $key => $module) {
            $sql = "SELECT * FROM module_client WHERE client_id = " . $formvars['id'] . " AND module_id = " . $module['module_id'];
            list($ModuleDroit) = $this->sql->getAll($sql);
            echo "<tr>";
            echo "<td width=\"12%\"><strong style=\"cursor:pointer;\" class=\"line" . $module['module_id'] . "\">" . $module['module_nom_nom'] . "</strong></td>";
            foreach($arrDroit as $droit){
                $checked = ($ModuleDroit[$droit['module_droit_nom']] == 1 || empty($ModuleDroit)) ? 'checked' : null;
                echo '<td width="11%" style="text-align: center;"><input type="hidden" style="cursor:pointer;" class="line' . $module['module_id'] . ' column' . $droit['module_droit_id'] . '" name="droit[' . $module['module_id'] . ']['.$droit['module_droit_nom'].']" value="0"><input ' . $checked . ' value="1" type="checkbox" name="droit[' . $module['module_id'] . ']['.$droit['module_droit_nom'].']" /></td>';
            }
            echo "</tr>";
        }
        echo "</tbody>
        </table>";
        echo "<br style='clear:both;'/><br /><table width='100%' cellpadding='0' cellspacing='0'>";
        echo"<tr>";
        echo "<td><input onclick=\"document.miniForm.submitBTN.value=this.value;\" type='submit' value='VALIDER' style='height:30px;width:100%'/></td>";
        echo"</tr>";
        echo"</table><br /></form>";

        echo "<script type=\"text/javascript\">
        jQuery('document').ready(function(){
            jQuery('strong[class^=\"column\"], strong[class^=\"line\"]').on('click', function(){
                var parent = jQuery(this);
                jQuery('.' + parent.attr('class')).each(function() {
                    var child = jQuery(this);
                    if(child.prop('checked')){
                        child.prop('checked', false);
                    } else {
                        child.prop('checked', true);
                    }
                });
            });
        });
        </script>";

    }

}