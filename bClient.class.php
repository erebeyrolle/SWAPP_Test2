<?php

class bClient extends BackModule {

    const CRYPT_PASSWORD = false;

    function bClient($formvars = array()) {
        parent::BackModule($formvars);
    }

    function update($formvars = array(), $table = null, $langue = true) {

        if(!empty($formvars['password'])) {
            $client = new Client();
            $formvars['client_password'] = $client->passwordEncrypt($formvars['password']);
        }

        $sql =     "UPDATE     client_beneficiaire
                    SET        client_beneficiaire_point = '" . addslashes($formvars['client_beneficiaire_point']) . "',
                               client_beneficiaire_identifiant = '" . addslashes($formvars['client_beneficiaire_identifiant']) . "'
                    WHERE      client_id                 =  " . addslashes($formvars['back_id']);
        $this->sql->query($sql);
        $return = parent::update($formvars, $table, $langue);

        return $return;
    }

    function create($formvars = array(), $table = null, $langue = true) {
        $client = new Recipient();
        $return = true;
        $isset = $client->get(['client_beneficiaire_identifiant' => $formvars['client_beneficiaire_identifiant']]);
        if (empty($isset)) {
            if (!empty($formvars['password'])) {
                $formvars['nocrypt_client_password'] = $formvars['password'];
                $formvars['client_password'] = $client->passwordEncrypt($formvars['password']);
            } else {
                $formvars['client_password'] = $client->passwordCreate(8);
            }

            $return = parent::create($formvars, $table, $langue);

            $sql = "INSERT INTO     client_beneficiaire
                    SET             client_beneficiaire_point = '" . addslashes($formvars['client_beneficiaire_point']) . "',
                                    client_beneficiaire_identifiant = '" . addslashes($formvars['client_beneficiaire_identifiant']) . "',
                                    client_id                 =  " . addslashes($return);
            if ($this->sql->query($sql)) {
                $email = new Email;
                $paramEmail['to'] = $formvars['client_email'];
                $paramEmail['texte'] = 'Email_Inscription';
                $paramEmail['layout'] = 'bienvenue';
                $formvars['client_password'] = $formvars['nocrypt_client_password'];
                $formvars['client_civilite'] = $client->getCivilite(['civilite_id' => $formvars['civilite_id']],
                    'civilite_nom_nom');
                $paramEmail['tags'] = $formvars;
                $email->sendFromTxt($paramEmail);
            }
        }

        return $return;
    }

    function update_commentaire($formvars = array()) {

        $sql = "UPDATE client SET client_commentaire = \"" . $formvars['var'] . "\", client_date_modif = NOW() WHERE client_id = '" . $formvars['id'] . "'";
        $this->sql->query($sql);
    }

    function Form($formvars = array()) {

        if (empty($formvars['id']))
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'un nouveau client</a></span>";
        else {
            $sql = "SELECT CONCAT(client_nom,' ',client_prenom,' - #', client_id ,'') as data FROM client WHERE client_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $data = $this->sql->record[0];

            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition du client : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql="SELECT *, c.client_id, c.client_point as old_point, ";
            $sql .= "
                                COALESCE(
                    (SELECT 
                       SUM(client_point_point) as client_point
                    FROM
                       client_point cp
                    INNER JOIN 
                       boutique_contrat bc ON (cp.boutique_contrat_id = bc.boutique_contrat_id AND bc.boutique_contrat_date_fin >= DATE(NOW()))
                    WHERE
                       cp.client_id = c.client_id
                    ), 0) 
                    +
                    COALESCE(
                    (SELECT 
                       SUM(client_point_point) as client_point
                    FROM
                       client_point cp
                    INNER JOIN 
                       client_point_commande cpc ON (cpc.client_point_id = cp.client_point_id)
                    WHERE
                       cp.client_id = c.client_id
                       AND cp.boutique_contrat_id = 0
                    ), 0) 
                    as client_beneficiaire_point
            ";
            $sql.=" FROM client c ";
            $sql.=" LEFT JOIN client_beneficiaire cb ON cb.client_id = c.client_id ";
            $sql.=" WHERE c.client_id = '".$formvars['id']."'";
//            $sql.=" AND c.client_type_id = '".Client::TYPE_COMMERCIAL."'";
            $this->request = $sql;
        }
        //-- SQL
        // Champs du formulaire
        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP


        // TODO : afficher les infos PLUS

        $form = new BackForm("Boutique", "select", "boutique_id");
        $form->addAttr('class', 'required');
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_id"));
        $this->addForm($form);

        $form = new BackForm("Type", "select", "client_type_id");
        $form->addAttr('class', 'required');
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT client_type_id, client_type_libelle FROM client_type WHERE client_type_id IN (" . Client::TYPE_INDEPENDANT . ", " . Client::TYPE_CLIENT . ", " . Client::TYPE_SALARIE . ") ORDER BY client_type_id"));
        $this->addForm($form);

        $form = new BackForm("Societe", "text", "client_societe");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Civ.", "select", "civilite_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT c.civilite_id, civilite_nom_nom FROM civilite c, civilite_nom cn WHERE cn.civilite_id=c.civilite_id AND cn.langue_id ='" . LANGUE . "' ORDER BY civilite_nom_nom"));
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "client_nom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Pr&eacute;nom", "text", "client_prenom");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Date de naissance", "date", "client_ddn");
        $this->addForm($form);

        $form = new BackForm("e-Mail", "mail", "client_email");
        $form->addAttr('class', 'required');
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Identifiant", "text", "client_beneficiaire_identifiant");
        $form->setVar('comment', 'Si vide l\'email servira d\'identifiant');
        $form->addAttr('size', 50);
        $this->addForm($form);


        if (!self::CRYPT_PASSWORD) {
            $form = new BackForm("Mot de passe", "text", "password");
            $form->addAttr('size', 50);
            if (empty($formvars['id'])) {
                $form->addAttr('class', 'required');
            }
            $this->addForm($form);
        }

        $form = new BackForm("T&eacute;l&eacute;phone", "text", "client_telephone");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Points", "text", "client_beneficiaire_point");
        $this->addForm($form);

        $form = new BackForm("Historique des points", "group");
        $form->addGroupOpts('class', 'client_point');
        $this->addForm($form);


        /*
                $form = new BackForm("Client confirm&eacute; ?", "select", "client_confirme");
                $form->addAttr('class', 'required');
                $form->setVar('comment', "Si le client a fournit un justificatif d'identit&eacute;");
                $form->addOptionSQL(array("(SELECT '0', 'Non') UNION (SELECT '1', 'Oui')"));
                $this->addForm($form);
              */
        $form = new BackForm("Fidelisation", "group");
        $this->addForm($form);

        $form = new BackForm("NewsLetter", "select", "client_newsletter_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT client_newsletter_id, client_newsletter_nom FROM client_newsletter ORDER BY client_newsletter_id"));
        $this->addForm($form);

        $form = new BackForm("Club Fid&eacute;lit&eacute;", "select", "client_club_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT client_club_id, client_club_nom FROM client_club ORDER BY client_club_id"));
        $this->addForm($form);

        $form = new BackForm("Bon Plan Partenaires", "select", "client_bon_plan_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT client_bon_plan_id, client_bon_plan_nom FROM client_bon_plan ORDER BY client_bon_plan_id"));
        $this->addForm($form);

        $form = new BackForm("Langue", "select", "langue_id");
        $form->addAttr('class', 'required');
        $form->setVar('multi', true);
        $form->addOptionSQL(array("SELECT langue_id, langue_nom FROM langue ORDER BY langue_nom"));
        $this->addForm($form);

        $form = new BackForm("Informations secondaires", "group");
        $this->addForm($form);

        $form = new BackForm("Fax", "text", "client_fax");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Commentaire", "textarea", "client_commentaire");
        $this->addForm($form);
        $form = new BackForm("Statut", "select", "client_statut_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT client_statut_id, client_statut_nom FROM client_statut ORDER BY client_statut_id"));
        $this->addForm($form);


        $form = new BackForm("Filleuls", "group");
        $form->addGroupOpts('class', 'filleul');
        $this->addForm($form);

        $form = new BackForm("Bons d'achat", "group");
        $form->addGroupOpts('class', 'bon_achat');
        $this->addForm($form);

        $form = new BackForm("Adresses", "group");
        $form->addGroupOpts('class', 'adresse');
        $this->addForm($form);

        $form = new BackForm("Commandes", "group");
        $form->addGroupOpts('class', 'commande');
        $this->addForm($form);

        $form = new BackForm("Avoirs", "group");
        $form->addGroupOpts('class', 'avoir');
        $this->addForm($form);

        //-- Champs du formulaire
        if (!isset($formvars['id']))
            $formvars['id'] = 0;
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array()) {

        // RECHERCHE
        $form = new BackForm("#ID", "text", "c.client_id");
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "c.client_nom");
        $form->addAttr('size', 20);
        $form->setVar('compare', 'EQUAL');
        $this->addForm($form);

        $form = new BackForm("Pr&eacute;nom", "text", "c.client_prenom");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("e-Mail", "text", "c.client_email");
        $form->addAttr('size', 50);
        $this->addForm($form);

        $form = new BackForm("Boutique", "select", "c.boutique_id");
        $form->addOption("", "---");
        $form->addOptionSQL(array("SELECT boutique_id, boutique_nom FROM boutique ORDER BY boutique_nom"));
        $this->addForm($form);

        $form = new BackForm("Ref API", "text", "c.client_code_api");
        $this->addForm($form);
        //-- RECHERCHE
        // SQL
        $sql = "SELECT c.client_id";
        $sql.=", c.client_id";
        $sql.=", btq.boutique_nom";
        $sql.=", c.client_nom, c.client_prenom, c.client_email";
        $sql.=", COUNT(DISTINCT a.adresse_id) as nb_adresses";
        // $sql.=", COUNT(DISTINCT f.client_id) as nb_fileuls";
        $sql.=", COUNT(DISTINCT b.bon_achat_id) as nb_bon_achats";
        /*
        $sql.=", GROUP_CONCAT( ";
            $sql.= " DISTINCT CONCAT(SUBSTR(cc.date_connexion, 9, 2),'/', SUBSTR(cc.date_connexion, 6, 2),'/',SUBSTR(cc.date_connexion, 1, 4),' ', SUBSTR(cc.date_connexion, 12, 8)) ORDER BY cc.date_connexion DESC SEPARATOR '<br />' ";
        $sql.= " ) as `historique_connexion`";
        */
        $sql.=", c.client_nb_connexion";
        $sql.=", COUNT(DISTINCT co.commande_id) as nb_commandes";
        //$sql.=", c.client_commentaire";
        //$sql.=", CONCAT('<p style=\"text-align:center\"><img src=\"/back/styles/images/', IF(c.client_confirme = 0, 'rouge.png', 'vert.png'), '\" /></p>')";
        $sql.=", c.client_statut_id";
        $sql.=", btq.boutique_nom";
        $sql.=", CONCAT('<a target=\"_blank\" href=\"http://',btq.boutique_domaine,'/auto_connexion.php?id=', MD5(CONCAT(c.client_id, '".Client::KEY_AUTOCONNECT."')), '\"><img src=\"".SITE_URL."back/styles/images/home.gif\" /></a>')";
        $sql.=" FROM (client c)";
        $sql.=" LEFT JOIN adresse a ON (c.client_id = a.client_id)";
        $sql.=" LEFT JOIN commande co ON (co.client_id = c.client_id)";
        $sql.=" LEFT JOIN langue l ON (l.langue_id = c.langue_id)";
        // $sql.=" LEFT JOIN client f ON (f.parrain_id = c.client_id)";
        $sql.=" LEFT JOIN bon_achat b ON (b.client_id = c.client_id)";
        $sql.=" LEFT JOIN boutique btq ON (c.boutique_id = btq.boutique_id)";
        // $sql.=" LEFT JOIN client_connexion cc ON (cc.client_id = c.client_id)";
        $sql.=" WHERE 1";
        $sql.=" AND c.client_type_id IN (".Client::TYPE_CLIENT.",".Client::TYPE_COMMERCIAL.",".Client::TYPE_SALARIE.", ".Client::TYPE_INDEPENDANT.")";
        $this->request = $sql;
        //-- SQL
        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("PRENOM");
        $this->addLabel($label);

        $label = new BackLabel("E-MAIL", "mail");
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE D'ADRESSES", "listselect", "ADR");
        $label->setVar('option', array("group" => "adresse"));
        $this->addLabel($label);

        /*$label = new BackLabel("NOMBRE DE FILLEULS", "listselect", "FIL");
        $label->setVar('option', array("group" => "filleul"));
        $this->addLabel($label);*/

        $label = new BackLabel("NOMBRE DE BONS D'ACHATS", "listselect", "BA");
        $label->setVar('option', array("group" => "bon_achat"));
        $this->addLabel($label);
/*
        $label = new BackLabel("HISTORIQUE CONNEXION");
        $this->addLabel($label);
*/
        $label = new BackLabel("NOMBRE DE CONNEXION");
        $label->setVar('abr', "CNX");
        $this->addLabel($label);

        $label = new BackLabel("NOMBRE DE COMMANDES", 'listselect', "CMD");
        $label->setVar('option', array("group" => "commande", "module" => "<a href='../../commande/list/?c*client_id=%ID%'>%IMG%</a>"));
        $this->addLabel($label);

        /*$label = new BackLabel("COMMENTAIRE", "eip");
        $label->setVar('option', array("update_commentaire"));
        $this->addLabel($label);*/

        /*$label = new BackLabel("CONFIRME");
        $this->addLabel($label);*/

        $label = new BackLabel("STATUT", "statut");
        $this->addLabel($label);

        $label = new BackLabel("BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("AUTO");
        $this->addLabel($label);

        //-- LABELS

        return $this->displayList($formvars['type']);
    }

}