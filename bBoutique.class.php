<?php

class bBoutique extends BackModule
{

    function __construct($formvars = array())
    {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
        $GLOBALS['displayMceEditor'] = true;
    }


    function update($formvars = array(), $table = null, $langue = true)
    {
        $this->sql->save('boutique_config', $formvars);
        $id = parent::update($formvars, $table, $langue);
        return $id;
    }

    function create($formvars = array(), $table = null, $langue = true)
    {
        $formvars['boutique_dossier'] = 'default';
        $formvars['boutique_id'] = parent::create($formvars, $table, $langue);
        $this->sql->save('boutique_config', $formvars);

        // Association de la boutique au paiement par point
        $q_ins_paiement_boutique = " INSERT INTO `paiement_boutique` SET  ";
        $q_ins_paiement_boutique.= " `paiement_id` = '".Paiement::PAIEMENT_POINT."', ";
        $q_ins_paiement_boutique.= " `boutique_id` = '".intval($formvars['boutique_id'])."', ";
        $q_ins_paiement_boutique.= " `paiement_boutique_statut_id` = 1 ";
        $this->sql->query($q_ins_paiement_boutique);

        // Association de la boutique aux produits et catégories
        if(!empty($formvars['boutique_assoc_datas'])){
            $q_produit = " SELECT `produit_id` FROM `produit` ";
            $row_produit = $this->sql->getAll($q_produit);
            $q_ins_produit_boutique = " INSERT INTO `produit_boutique` (`produit_id`, `boutique_id`) VALUES ";
            $i = 0;
            foreach($row_produit as $produit){
                $i++;
                $q_ins_produit_boutique.= ($i > 1?', ':'')." ('".intval($produit['produit_id'])."','".intval($formvars['boutique_id'])."') ";
            }
            $this->sql->query($q_ins_produit_boutique);

            $q_categorie = " SELECT `categorie_id` FROM `categorie` WHERE `categorie_assoc_auto` = 1 ";
            $row_categorie = $this->sql->getAll($q_categorie);
            $q_ins_categorie_boutique = " INSERT INTO `categorie_boutique` (`categorie_id`, `boutique_id`) VALUES ";
            $i = 0;
            foreach($row_categorie as $categorie){
                $i++;
                $q_ins_categorie_boutique.= ($i > 1?', ':'')." ('".intval($categorie['categorie_id'])."','".intval($formvars['boutique_id'])."') ";
            }
            $this->sql->query($q_ins_categorie_boutique);

            // insert marque_boutique
            $sql = "INSERT INTO marque_boutique SELECT NULL, marque_id, {$formvars['boutique_id']}, 1";
            $sql .= " FROM marque";
            $this->sql->query($sql);

            // insert gamme_boutique
            $sql = "INSERT INTO gamme_boutique SELECT NULL, gamme_id, {$formvars['boutique_id']}, 1";
            $sql .= " FROM gamme";
            $this->sql->query($sql);
        }

        Boutique::getInstance()->getOrCreateFictifContractId($formvars['boutique_id']);

        return $formvars['boutique_id'];
    }

    public function duplicate($formvars = array(), $table = null, $langue = true)
    {
        $formvars["boutique_config_id"] = 0;
        unset($formvars["boutique_config_id"]);
        $formvars['boutique_id']  = parent::duplicate($formvars, $table, $langue);

        // Association de la boutique au paiement par point
        $q_ins_paiement_boutique = " INSERT INTO `paiement_boutique` SET  ";
        $q_ins_paiement_boutique.= " `paiement_id` = '".Paiement::PAIEMENT_POINT."', ";
        $q_ins_paiement_boutique.= " `boutique_id` = '".intval($formvars['boutique_id'])."', ";
        $q_ins_paiement_boutique.= " `paiement_boutique_statut_id` = 1 ";
        $this->sql->query($q_ins_paiement_boutique);

        $q_produit = " SELECT `produit_id` FROM `produit` ";
        $row_produit = $this->sql->getAll($q_produit);
        $q_ins_produit_boutique = " INSERT INTO `produit_boutique` (`produit_id`, `boutique_id`) VALUES ";
        $i = 0;
        foreach($row_produit as $produit){
            $i++;
            $q_ins_produit_boutique.= ($i > 1?', ':'')." ('".intval($produit['produit_id'])."','".intval($formvars['boutique_id'])."') ";
        }
        $this->sql->query($q_ins_produit_boutique);

        $q_categorie = " SELECT `categorie_id` FROM `categorie` WHERE `categorie_assoc_auto` = 1 ";
        $row_categorie = $this->sql->getAll($q_categorie);
        $q_ins_categorie_boutique = " INSERT INTO `categorie_boutique` (`categorie_id`, `boutique_id`) VALUES ";
        $i = 0;
        foreach($row_categorie as $categorie){
            $i++;
            $q_ins_categorie_boutique.= ($i > 1?', ':'')." ('".intval($categorie['categorie_id'])."','".intval($formvars['boutique_id'])."') ";
        }
        $this->sql->query($q_ins_categorie_boutique);

        // insert marque_boutique
        $sql = "INSERT INTO marque_boutique SELECT NULL, marque_id, {$formvars['boutique_id']}, 1";
        $sql .= " FROM marque";
        $this->sql->query($sql);

        // insert gamme_boutique
        $sql = "INSERT INTO gamme_boutique SELECT NULL, gamme_id, {$formvars['boutique_id']}, 1";
        $sql .= " FROM gamme";
        $this->sql->query($sql);

        Boutique::getInstance()->getOrCreateFictifContractId($formvars['boutique_id']);

        return $formvars['boutique_id'];
    }

    function Form($formvars = array())
    {

        if (empty($formvars['id'])) {
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('addForm') . "'>Ajout d'une nouvelle Boutique</a></span>";
        } else {
            $sql = "SELECT boutique_nom as data FROM boutique WHERE boutique_id = '" . $formvars['id'] . "'";
            $this->sql->query($sql, Site_SQL::SQL_ALL, Site_SQL::SQL_ASSOC);
            $data = $this->sql->record[0];
            $this->path[] = "<span> &raquo; <a href='" . $this->link_to('modForm', array('id' => $formvars['id'])) . "'>Edition de la Boutique : <strong>" . $data['data'] . "</strong></a></span>";
        }

        // SQL
        if (!empty($formvars['id'])) {
            $sql = "SELECT *, 
                           b.boutique_id, 
                           ( COALESCE(
                             ( SELECT SUM(bc.boutique_contrat_point) 
                               FROM boutique_contrat bc 
                               WHERE bc.boutique_contrat_date_fin > NOW() AND bc.boutique_contrat_annule = 0
                               AND bc.boutique_id = '" . $formvars['id'] . "'
                             )
                             , 0)
                           ) 
                           - 
                           ( COALESCE(
                             ( SELECT SUM(cp.client_point_point)
                               FROM client_point cp
                               LEFT JOIN boutique_contrat bc ON bc.boutique_contrat_id = cp.boutique_contrat_id
                               INNER JOIN boutique b ON (bc.boutique_id = b.boutique_id) AND b.boutique_id = '" . $formvars['id'] . "'
                             )
                             , 0)
                           ) as boutique_point
                    FROM boutique b
                    LEFT JOIN boutique_config bc ON (b.boutique_id = bc.boutique_id)
                    LEFT JOIN commercial c ON (c.commercial_id = b.commercial_id)
                    LEFT JOIN liste_prix lp ON (lp.liste_prix_id = b.liste_prix_id)
                    LEFT JOIN condition_paiement cp ON (cp.condition_paiement_id = b.condition_paiement_id)
                    LEFT JOIN groupe_client gc ON (gc.groupe_client_id = b.groupe_client_id)
                    WHERE b.boutique_id='" . $formvars['id'] . "'";
            $this->request = $sql;
        }
        //-- SQL

        // Champs du formulaire

        //GROUP
        $form = new BackForm("Informations principales", "group");
        $this->addForm($form);
        //--GROUP

        if (!empty($formvars['id'])) {
            $form = new BackForm("Boutique Config ID", "hidden", "boutique_config_id");
            $this->addForm($form);
        }

        $form = new BackForm("Nom", "text", "boutique_nom");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Domaine", "text", "boutique_domaine");
        $form->addAttr('class', 'required');
        $this->addForm($form);

//        $form = new BackForm("Dossier","text","boutique_dossier");
//        $form->addAttr('class','required');
//        $this->addForm($form);

        $form = new BackForm("Admin email", "text", "boutique_admin_email");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Contact email", "text", "boutique_contact_email");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Commercial", "text", "commercial_nom");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Liste de prix", "text", "liste_prix_nom");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Groupe", "text", "groupe_client_nom");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Cond. paiement", "text", "condition_paiement_nom");
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Parametrage", "group");
        $this->addForm($form);

        $form = new BackForm("Devise", "select", "devise_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL("SELECT devise_id, CONCAT(devise_nom, ' (', devise_code, ')') FROM devise ORDER BY devise_nom");
        $this->addForm($form);

        $form = new BackForm("Langue", "select", "langue_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL("SELECT langue_id, langue_nom FROM langue ORDER BY langue_nom");
        $this->addForm($form);

        $form = new BackForm("Type de prix", "select", "boutique_prix_type_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT boutique_prix_type_id, boutique_prix_type_libelle FROM boutique_prix_type"));
        $this->addForm($form);

        $form = new BackForm("Type de boutique", "select", "boutique_type_id");
        $form->addAttr('class', 'required');
        $form->addOptionSQL(array("SELECT boutique_type_id, boutique_type_libelle FROM boutique_type"));
        $this->addForm($form);

        $form = new BackForm("Achat de point bénéficiaire", "select", "boutique_config_can_buy_points");
        $form->addAttr('comment', "Est-ce que les bénéficiaire de cette boutique peuvent acheter des points directement à saona");
        $form->addOption(0, 'NON');
        $form->addOption(1, 'OUI');
        $this->addForm($form);

        // TODO : sous module tracking

        $form = new BackForm("Index CRM INES", "text", "boutique_crm_id");
        $this->addForm($form);

        $form = new BackForm("Statut", "select", "actif_id");
        $form->addOptionSQL("SELECT actif_id, actif_nom FROM actif ORDER BY actif_id");
        $form->addAttr('class', 'required');
        $this->addForm($form);

        $form = new BackForm("Administrateurs", "group");
        $form->addGroupOpts('class', 'admin_user');
        $this->addForm($form);

        $form = new BackForm("Points", "group");
        $this->addForm($form);

        $form = new BackForm("Stock de point", "text", "boutique_point");
//        $form->addAttr('class','required');
        $form->addAttr('readonly', 'readonly');
        $this->addForm($form);

        $form = new BackForm("Contrat", "group");
        $form->addGroupOpts('class', 'boutique_contrat');
        $this->addForm($form);

        $form = new BackForm("E-mails", "group");
        $form->addGroupOpts('class', 'boutique_email');
        $this->addForm($form);

        $form = new BackForm("Couleurs", "group");
        $this->addForm($form);

        $form = new BackForm("Titres page connexion", "text", "boutique_login_title_color");
        $this->addForm($form);

        $form = new BackForm("Lien page connexion", "text", "boutique_login_link_color");
        $this->addForm($form);

        $form = new BackForm("Texte des titres", "text", "boutique_title_color");
        $this->addForm($form);

        $form = new BackForm("Points dans le panier", "text", "boutique_link_color");
        $this->addForm($form);

        $form = new BackForm("Texte du menu", "text", "boutique_menu_color");
        $this->addForm($form);

        $form = new BackForm("Fond du menu", "text", "boutique_menu_background");
        $this->addForm($form);

        $form = new BackForm("Fond de menu secondaires", "text", "boutique_menu_other_background");
        $form->setVar("comment", "Couleur de fond des menus Mon compte et Mon panier de l'entête");
        $this->addForm($form);

        $form = new BackForm("Couleur de texte des menus secondaires", "text", "boutique_menu_other_color");
        $form->setVar("comment", "Couleur des textes des menus Mon compte et Mon panier de l'entête");
        $this->addForm($form);

        $form = new BackForm("Fond de l'entête", "text", "boutique_header_background");
        $this->addForm($form);

        $form = new BackForm("Texte de l'entête", "text", "boutique_header_texte");
        $this->addForm($form);

        $form = new BackForm("Texte du pied de page", "text", "boutique_footer_color");
        $this->addForm($form);

        $form = new BackForm("Fond du pieds de page", "text", "boutique_footer_background");
        $this->addForm($form);

        $form = new BackForm("Texte des boutons", "text", "boutique_button_color");
        $this->addForm($form);

        $form = new BackForm("Fond des boutons", "text", "boutique_button_background");
        $this->addForm($form);

        $form = new BackForm("Fond du contenu central", "text", "boutique_center_background");
        $this->addForm($form);

        $form = new BackForm("Icônes de l'entête", "text", "boutique_icon_color");
        $this->addForm($form);

        $form = new BackForm("Fond du formulaire de login", "text", "boutique_login_color_background");
        $this->addForm($form);

        $form = new BackForm("Menu colonnes du pied de page", "text", "boutique_footer_menu_colonnes_color");
        $this->addForm($form);

        $form = new BackForm("Fond slider des marques", "text", "boutique_marque_color_background");
        $this->addForm($form);

        $form = new BackForm("Sous catégorie", "text", "boutique_lien_sous_categorie");
        $this->addForm($form);

        $form = new BackForm("Sous catégorie au survol", "text", "boutique_lien_sous_categorie_hover");
        $this->addForm($form);

        $form = new BackForm("Sous sous catégorie", "text", "boutique_lien_sous_sous_categorie");
        $this->addForm($form);

        $form = new BackForm("Sous sous catégorie au survol", "text", "boutique_lien_sous_sous_categorie_hover");
        $this->addForm($form);



        $form = new BackForm("Images", "group");
        $this->addForm($form);

        $form = new BackForm("Logo entête", "image", "boutique_header_logo");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 200x100px');
        $this->addForm($form);

        $form = new BackForm("Banniere Home", "image", "boutique_home_banner");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 220x300px');
        $this->addForm($form);

        $form = new BackForm("Background Login", "image", "boutique_login_background");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 1920x1080px');
        $this->addForm($form);
/*
        $form = new BackForm("Background principal", "image", "boutique_main_background");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 1920x1080px');
        $this->addForm($form);
*/
        $form = new BackForm("Logo pied de page", "image", "boutique_footer_logo");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 200x100px');
        $this->addForm($form);

        $form = new BackForm("Logo pied de page 2", "image", "boutique_footer_logo_2");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 200x100px');
        $this->addForm($form);

        $form = new BackForm("Logo séparateur du pied de page", "image", "boutique_footer_separator");
        $form->setVar('comment', '<strong>Taille de l\'image</strong> : 1920x20px');
        $this->addForm($form);

        $form = new BackForm("Logo mail/BC de l'entête", "image", "boutique_logo_mail_header");
        $this->addForm($form);

        $form = new BackForm("Logo mail du pied de page", "image", "boutique_logo_mail_footer");
        $this->addForm($form);

        $form = new BackForm("Configuration", "group");
        $this->addForm($form);

        $form = new BackForm("Affichage des marques", "select", "boutique_display_brand");
        $form->addOption('1', 'oui');
        $form->addOption('0', 'non');
        $this->addForm($form);

        $form = new BackForm("Connexion externe", "select", "boutique_external_connect");
        $form->setVar('comment', 'Déconnexion et modification de mot de passe impossible à partir de la boutique');
        $form->addOption('0', 'non');
        $form->addOption('1', 'oui');
        $this->addForm($form);

        $form = new BackForm("Cacher la barre de recherche ?", "select", "boutique_config_has_search");
        $form->addOption('1', 'oui');
        $form->addOption('0', 'non');
        $this->addForm($form);

        $form = new BackForm("Peut s'inscrire", "select", "boutique_config_inscription_lien");
        $form->setVar('comment', "L'utilisateur verra le bouton \"J'ai une carte, je m'inscris\" sur la page de login");
        $form->addOption('0', 'non');
        $form->addOption('1', 'oui');
        $this->addForm($form);

        $form = new BackForm("Texte Comme CGV", "select", "boutique_config_cgv_edition_id");
        $form->addOption("NULL", '---');
        $form->addOptionSQL('SELECT edition_id, edition_nom FROM edition');
        $this->addForm($form);

        $form = new BackForm("Téléphone", "text", "boutique_telephone");
        $this->addForm($form);
/*
        $form = new BackForm("Mention en pied de page", "text", "boutique_mention_footer");
        $this->addForm($form);
*/
        $form = new BackForm("Adresse", "text", "boutique_adresse_rue");
        $this->addForm($form);

        $form = new BackForm("Code postal", "text", "boutique_adresse_cp");
        $this->addForm($form);

        $form = new BackForm("Ville", "text", "boutique_adresse_ville");
        $this->addForm($form);

        $form = new BackForm("Pays", "select", "boutique_pays_id");
        $form->addOptionSQL(array('SELECT pn.pays_id, pn.pays_nom_nom FROM pays_nom pn WHERE pn.langue_id = "' . LANGUE . '" ORDER BY pn.pays_nom_nom'));
        $this->addForm($form);

        $form = new BackForm("1 € = xx points", "text", "boutique_euro_point");
        $this->addForm($form);

        $form = new BackForm("PDF pied de page (ligne 1)", "text", "mention_footer_pdf_1");
        $form->setVar('comment', 'Ligne 1 des mentions en pied de page des pdf générés');
        $this->addForm($form);

        $form = new BackForm("PDF pied de page (ligne 2)", "text", "mention_footer_pdf_2");
        $form->setVar('comment', 'Ligne 2 des mentions en pied de page des pdf générés');
        $this->addForm($form);

        $form = new BackForm("PDF pied de page (ligne 3)", "text", "mention_footer_pdf_3");
        $form->setVar('comment', 'Ligne 3 des mentions en pied de page des pdf générés');
        $this->addForm($form);

        $form = new BackForm("PDF pied de page (ligne 4)", "text", "mention_footer_pdf_4");
        $form->setVar('comment', 'Ligne 4 des mentions en pied de page des pdf générés');
        $this->addForm($form);

        if (empty($formvars['id'])) {
            $form = new BackForm("Association auto", "select", "boutique_assoc_datas");
            $form->setVar('comment', 'Associer tous les produits et toutes les catégories à cette nouvelle boutique');
            $form->addOption('1', 'oui');
            $form->addOption('0', 'non');
            $this->addForm($form);
        }

        $form = new BackForm("Forcer le HTTPS", 'select', "boutique_https");
        $form->addOption("0", 'non');
        $form->addOption("1", 'oui');
        $this->addForm($form);

        $form = new BackForm("SMS", "group");
        $this->addForm($form);

        $form = new BackForm("Activer l'envoi de sms", 'select', "boutique_sms");
        $form->addOption("0", 'non');
        $form->addOption("1", 'oui');
        $this->addForm($form);

        $form = new BackForm("Nom boutique SMS", 'text', "boutique_nom_sms");
        $form->setVar('comment', 'Ce nom ne doit pas dépasser 10 caractères');
        $this->addForm($form);

        $form = new BackForm("Message d'inscription", 'textarea', "boutique_sms_contenu_inscription");
        $form->setVar('comment', 'Si vide, le message présent dans le module Texte sera utilisé');
        $this->addForm($form);

        $form = new BackForm("Message d'ajout de point", 'textarea', "boutique_sms_contenu_add_point");
        $form->setVar('comment', 'Si vide, le message présent dans le module Texte sera utilisé');
        $this->addForm($form);

        $form = new BackForm("Message d'ajout commande", 'textarea', "boutique_sms_contenu_save_commande");
        $form->setVar('comment', 'Si vide, le message présent dans le module Texte sera utilisé');
        $this->addForm($form);

        $form = new BackForm("Message d'expédition commande", 'textarea', "boutique_sms_contenu_expe_commande");
        $form->setVar('comment', 'Si vide, le message présent dans le module Texte sera utilisé');
        $this->addForm($form);

        $form = new BackForm("Slideshow", "group");
        $form->addGroupOpts('class', 'boutique_slideshow');
        $this->addForm($form);

        $form = new BackForm("Utilise le slideshow général", "select", "boutique_config_general_slideshow");
        $form->addOption('0', 'non');
        $form->addOption('1', 'oui');
        $this->addForm($form);

        if (!isset($formvars['id'])) {
            $formvars['id'] = 0;
        }
        return $this->displayForm($formvars['id']);
    }

    function Listing($formvars = array())
    {

        $form = new BackForm("#Id", "text", "b.boutique_id");
        $this->addForm($form);

        $form = new BackForm("Nom", "text", "b.boutique_nom");
        $form->addAttr('size', 20);
        $this->addForm($form);

        $form = new BackForm("Domaine", "text", "b.boutique_domaine");
        $form->addAttr('size', 20);
        $this->addForm($form);

        // SQL
        $sql = "SELECT b.boutique_id, b.boutique_id";
        $sql .= ", b.boutique_nom";
        $sql .= ", b.boutique_domaine";
        $sql .= ", b.boutique_admin_email";
        $sql .= ", b.boutique_contact_email";
        $sql .= ", l.langue_nom";
        $sql .= ", CONCAT(d.devise_nom, ' (', d.devise_code, ')')";
        $sql .= ", bpt.boutique_prix_type_libelle";
        $sql .= ", bt.boutique_type_libelle";
//        $sql.=", CONCAT(d.devise_nom, ' (', d.devise_code, ')')";
        $sql .= ", CONCAT('<a target=\"_blank\" href=\"http://',b.boutique_domaine,'/admin/auto_connexion.php?auto_connexion=', MD5(CONCAT(c.client_id, '" . Client::KEY_AUTOCONNECT . "')), '\"><img src=\"" . SITE_URL . "back/styles/images/home.gif\" /></a>')";
        $sql .= " FROM (boutique b)";
        $sql .= " LEFT JOIN langue l ON(b.langue_id = l.langue_id)";
        $sql .= " LEFT JOIN devise d ON(b.devise_id = d.devise_id)";
        $sql .= " LEFT JOIN boutique_prix_type bpt ON(bpt.boutique_prix_type_id = b.boutique_prix_type_id)";
        $sql .= " LEFT JOIN boutique_type bt ON(bt.boutique_type_id = b.boutique_type_id)";
        $sql .= " LEFT JOIN client c ON(c.boutique_id = b.boutique_id AND c.client_type_id = " . Client::TYPE_ANNONCEUR . ")";
        $sql .= " WHERE 1";

        $this->request = $sql;
        //-- SQL

        // LABELS
        $label = new BackLabel("ID");
        $this->addLabel($label);

        $label = new BackLabel("NOM");
        $this->addLabel($label);

        $label = new BackLabel("DOMAINE");
        $this->addLabel($label);

        $label = new BackLabel("ADMIN EMAIL", "mail");
        $this->addLabel($label);

        $label = new BackLabel("ADMIN CONTACT", "mail");
        $this->addLabel($label);

        $label = new BackLabel("LANGUE");
        $this->addLabel($label);

        $label = new BackLabel("DEVISE");
        $this->addLabel($label);

        $label = new BackLabel("TYPE DE PRIX");
        $this->addLabel($label);

        $label = new BackLabel("TYPE DE BOUTIQUE");
        $this->addLabel($label);

        $label = new BackLabel("CONNEXION");
        $this->addLabel($label);
        //-- LABELS

        return $this->displayList($formvars['type']);
    }

//--
}