<?php

class bCommande_export extends BackModule
{

    // remonter des emails
    function statut()
    {

        $idStatut = $_POST['commande_statut_id'];
        $idCommande = $_POST['commande_id'];


        $tags = array();
        $tags['motif'] = @$_POST['commande_motif'];
        $tags['colis'] = @$_POST['commande_colis'];

        $commande = new Commande;

        list($sujet, $txt, $html) = $commande->getMail($idCommande, $idStatut, $tags);
        echo $sujet . '<br/><hr/><br/>' . $html;
    }

    // Remonter des options de statut
    function statut_option($formvars = array())
    {

        $commande = new Commande; // utile pour les constantes

        echo '<br/><u><strong>Motif compl&eacute;mentaire : </strong></u><br/>';

        $statut = new Statut;
        $arrOpts = $statut->getOption(array('commande_id' => $_POST['commande_id'], 'commande_statut_id' => $_POST['commande_statut_id']));
        if (!empty($arrOpts)) {

            echo "<script>";
            echo("var tableauFromPHP = new Array();\n");
            for ($i = 0; $i < count($arrOpts); $i++) {
                $j = $arrOpts[$i]['commande_statut_option_id'];
                $arrOpts[$i]['commande_statut_option_nom_email'] = addslashes($arrOpts[$i]['commande_statut_option_nom_email']);
                $arrOpts[$i]['commande_statut_option_nom_email'] = str_replace(chr(10), "\\r", $arrOpts[$i]['commande_statut_option_nom_email']);
                $arrOpts[$i]['commande_statut_option_nom_email'] = str_replace(chr(13), "\\n", $arrOpts[$i]['commande_statut_option_nom_email']);
                echo("tableauFromPHP[$j] = '{$arrOpts[$i]['commande_statut_option_nom_email']}';\n");
            }
            echo "</script>";


            foreach ($arrOpts as $tmp) {
                echo '<p>';
                echo '<input class="commande" onclick="change_motif(tableauFromPHP, \'' . $tmp['commande_statut_option_id'] . '\');change_statut(\'no_update\');" type="radio" name="commande_statut_option_id" id="commande_statut_option_id_' . $tmp['commande_statut_option_id'] . '" value="' . $tmp['commande_statut_option_id'] . '">';
                echo '<label class="commande" for="commande_statut_option_id_' . $tmp['commande_statut_option_id'] . '">';
                echo $tmp['commande_statut_option_nom_nom'];
                echo '</label>';
                echo '</p>';
            }
        }

        echo '<textarea onkeyup="change_statut(\'no_update\')" rows="3" cols="10" style="padding:5px;width: 600px" name="commande_motif" id="commande_motif" ></textarea><br/>';

        echo '<br/><u><strong>Document(s) joint(s) : </strong></u><br/>';
        echo '<input type="checkbox" name="commande_bc" id="commande_bc" value="true" /> Joindre le bon de commande en PDF<br/>';
        if ($_POST['commande_statut_id'] == Commande::STATUT_EXPEDIE) {
            echo '<input type="checkbox" name="commande_fc" id="commande_fc" value="true" checked="checked" /> Joindre la confirmation d\'achat en PDF<br/>';
            echo '<br/><u><strong>Num&eacute;ro de colis : </strong></u><br/>';
            echo '<input onkeyup="change_statut(\'no_update\')"  style="padding:5px;width: 600px" name="commande_colis" id="commande_colis" />';
        }
    }

    function details($formvars = array())
    {
        // recup info commande
        /* $sql = "SELECT c.commande_id, DATE_FORMAT(commande_date_ajout,'%d-%m-%Y %H:%i:%s') AS commande_date,SUM(IF(commande_produit_attribut_id IS NULL,produit_quantite,produit_option_valeur_quantite)) AS qte, commande_statut_nom_nom, paiement_nom, commande_livraison_nom";
          $sql.=", SUM(IF(commande_produit_attribut_id IS NULL, produit_quantite * produit_prix, produit_option_valeur_quantite * (produit_prix + produit_option_valeur_prix))) AS stotal_ht";
          $sql.=", SUM(IF(commande_produit_attribut_id IS NULL, produit_quantite * produit_prix, produit_option_valeur_quantite * (produit_prix + produit_option_valeur_prix))) + commande_port_ht AS total_ht";
          $sql.=", SUM(IF(commande_produit_attribut_id IS NULL, produit_quantite * ROUND(produit_prix * (1 + produit_taxe),2), produit_option_valeur_quantite * ROUND((produit_prix + produit_option_valeur_prix) * (1 + produit_taxe),2))) AS stotal_ttc";
          $sql.=", SUM(IF(commande_produit_attribut_id IS NULL, produit_quantite * ROUND(produit_prix * (1 + produit_taxe),2), produit_option_valeur_quantite * ROUND((produit_prix + produit_option_valeur_prix) * (1 + produit_taxe),2))) + ROUND(commande_port_ht * (1 + commande_port_taxe),2) AS total_ttc";
          $sql.=", commande_port_ht, ROUND(commande_port_ht * (1 + commande_port_taxe),2) AS commande_port_ttc";
          $sql.=", c.client_id, client_nom, client_societe, client_rue, client_rue2, client_cp, client_ville, client_etat, client_pays, client_telephone, client_email";
          $sql.=", livraison_nom, livraison_societe, livraison_rue, livraison_rue2, livraison_cp, livraison_ville, livraison_etat, livraison_pays";
          $sql.=", facturation_nom, facturation_societe, facturation_rue, facturation_rue2, facturation_cp, facturation_ville, facturation_etat, facturation_pays";
          $sql.=" FROM (commande c)";
          $sql.=" LEFT JOIN commande_produit cp ON (cp.commande_id = c.commande_id)";
          $sql.=" LEFT JOIN commande_produit_attribut cpa ON (cp.commande_produit_id = cpa.commande_produit_id)";
          $sql.=" LEFT JOIN commande_statut cs ON (cs.commande_statut_id = c.commande_statut_id)";
          $sql.=" LEFT JOIN commande_statut_nom csn ON (csn.commande_statut_id = c.commande_statut_id AND csn.langue_id='1')";
          $sql.=" WHERE c.commande_id = '" . $formvars['id'] . "'";
          $sql.=" GROUP BY c.commande_id";

          $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
          list($arrCommande) = $this->sql->record; */

        $commande = new Commande;
        list($arrCommande) = $commande->get(array('no_boutique' => true, 'commande_id' => $formvars['id']));

        //-- recup info commande
        // recup produit commande
        /* $sql = "SELECT cp.commande_produit_id";
          $sql.=", cp.produit_id, produit_ref, produit_nom, produit_option_valeur_nom, IF(commande_produit_attribut_id IS NULL,produit_prix,produit_prix+produit_option_valeur_prix) AS prix_ht, IF(commande_produit_attribut_id IS NULL,produit_quantite,produit_option_valeur_quantite) AS qte";
          $sql.=", IF(commande_produit_attribut_id IS NULL,produit_prix * produit_quantite,(produit_prix+produit_option_valeur_prix) * produit_option_valeur_quantite) AS prix_total_ht";
          $sql.=", IF(commande_produit_attribut_id IS NULL,ROUND(produit_prix * (1 + produit_taxe),2) * produit_quantite, ROUND((produit_prix + produit_option_valeur_prix) * (1 + produit_taxe),2) * produit_option_valeur_quantite) AS prix_total_ttc";
          $sql.=", cpa.produit_attribut_id";
          $sql.=", cpa.commande_produit_attribut_id";
          $sql.=" FROM (commande_produit cp)";
          $sql.=" LEFT JOIN commande_produit_attribut cpa ON (cp.commande_produit_id = cpa.commande_produit_id)";
          $sql.=" WHERE cp.commande_id = '" . $formvars['id'] . "'";

          $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
          $arrP = $this->sql->record; */

        $arrP = $commande->getProduit(array('commande_id' => $arrCommande['commande_id']));
        //-- recup produit commande

        /* $sql = "SELECT commande_statut_nom_nom, DATE_FORMAT(commande_statut_historique_date_ajout, '%d-%m-%Y %H:%i:%s') AS commande_statut_historique_date_ajout
          FROM commande_statut_historique csh
          LEFT JOIN commande_statut_nom csn ON(csh.commande_statut_id = csn.commande_statut_id AND csn.langue_id = '" . LANGUE . "')
          WHERE commande_id = '" . $formvars['id'] . "'
          ORDER BY commande_statut_historique_date_ajout DESC
          ";

          $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
          $arrStat = $this->sql->record; */

        $arrStat = $commande->getHistory(array('commande_id' => $arrCommande['commande_id']));

        $sql = "SELECT commande_commentaire FROM commande WHERE commande_id = '" . $arrCommande['commande_id'] . "'";
        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        list($arrComm) = $this->sql->record;

        echo '<table class="records popup-details">
            <tr>
                <th height="15" colspan="4">COMMANDE n&deg;' . $arrCommande['commande_id'] . '</th>
            </tr>
    <tr class="unselect_enr1">
        <td><b>Date commande</b></td>
        <td>' . $arrCommande['commande_date'] . '</td>
        <td><b>Client</b></td>
        <td>' . $arrCommande['client_nom'] . '(#' . $arrCommande['client_id'] . ')<br/><a href="mailto:' . $arrCommande['client_email'] . '">' . $arrCommande['client_email'] . '</a></td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>Statut</b></td>
        <td>' . $arrCommande['commande_statut_nom_nom'] . '</td>
        <td><b>Mode de paiement</b></td>
        <td>' . $arrCommande['paiement_nom'] . '</td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>N° Facture</b></td>
        <td>' . $arrCommande['commande_facture_numero'] . '</td>
        <td><b>Date Facture</b></td>
        <td>' . $arrCommande['commande_facture_date'] . '</td>
    </tr>
    </table>';

    echo '<table class="records popup-details">
    <tr>
        <th height="15" colspan="4">LIVRAISON</th>
    </tr>
    <tr class="unselect_enr1">
        <td><b>Mode</b></td>
        <td>' . $arrCommande['commande_livraison_nom'] . '</td>
        <td><b>D&eacute;lai</b></td>
        <td>' . $arrCommande['commande_livraison_delai'] . ' jours</td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>Suivi</b></td>
        <td>' . $arrCommande['commande_livraison_colis'] . '</td>
        <td><b>Info</b></td>
        <td>' . $arrCommande['commande_livraison_info'] . '</td>
    </tr>
    </table>';
// Tableau récapitulatif adresses livraison & facturation
echo('VARDUMP ID COMMANDE :' . $arrCommande['commande_id'] . '<br />');
var_dump( $arrCommande['commande_id']);
echo('<br />' . 'VARDUMP ARRAY COMMANDE :');
var_dump($arrCommande);
echo('VarDump4');
var_dump($arrCommande['livraison_societe']);
echo('VarDump5');
var_dump($arrCommande['facturation_societe']);
    echo '<table class="records popup-details">
     <tr>
        <th height="15" colspan="2">ADRESSE LIVRAISON</th>
        <th height="15" colspan="2">ADRESSE FACTURATION</th>
    </tr>
    <tr class="unselect_enr1">
        <td><b>Société</b></td>
        <td>' . $arrCommande['livraison_societe'] . '</td>
        <td><b>Société</b></td>
        <td>' . $arrCommande['facturation_societe'] . '</td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>Nom</b></td>
        <td>' . $arrCommande['livraison_nom'] . '</td>
        <td><b>Nom</b></td>
        <td>' . $arrCommande['facturation_nom'] . '</td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>Rue</b></td>
        <td>' . $arrCommande['livraison_rue'] . '</td>
        <td><b>Rue</b></td>
        <td>' . $arrCommande['facturation_rue'] . '</td>
    </tr>
    <tr class="unselect_enr1">
        <td><b>Rue2</b></td>
        <td>' . $arrCommande['livraison_rue2'] . '</td>
        <td><b>Rue2</b></td>
        <td>' . $arrCommande['facturation_rue2'] . '</td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>Code Postal</b></td>
        <td>' . $arrCommande['livraison_cp'] . '</td>
        <td><b>Code Postal</b></td>
        <td>' . $arrCommande['facturation_cp'] . '</td>
    </tr>
    <tr class="unselect_enr1">
        <td><b>Ville</b></td>
        <td>' . $arrCommande['livraison_ville'] . '</td>
        <td><b>Ville</b></td>
        <td>' . $arrCommande['facturation_ville'] . '</td>
    </tr>
    <tr class="unselect_enr0">
        <td><b>Pays</b></td>
        <td>' . $arrCommande['livraison_pays'] . '</td>
        <td><b>Pays</b></td>
        <td>' . $arrCommande['facturation_pays'] . '</td>
    </tr>
    <tr class="unselect_enr1">
        <td><b>T&eacute;l&eacute;phone</b></td>
        <td>' . $arrCommande['livraison_telephone'] . '</td>
        <td><b>T&eacute;l&eacute;phone</b></td>
        <td>' . $arrCommande['facturation_telephone'] . '</td>
    </tr>
</table>';

echo '<table class="records popup-details">
        <tr>
            <th height="15" colspan="8">
                RECAPITULATIF DES PRODUITS COMMANDES
            </th>
        </tr>
        <tr>
            <th height="15">ID</th>
            <th height="15">REF</th>
            <th height="15">REF FRS</th>
            <th height="15">NOM</th>
            <th height="15">QTE</th>
            <th height="15">PRIX UNITAIRE HT</th>
            <th height="15">PRIX HT</th>
        </tr>';

        foreach ($arrP as $arrProduit) {
            echo '
                <tr class="unselect_enr0" align="center">
                    <td>' . $arrProduit['produit_id'] . '</td>
                    <td>' . $arrProduit['produit_ref'] . '</td>
                    <td>' . $arrProduit['produit_ref_frs'] . '</td>
                    <td>' . $arrProduit['produit_nom'];
            if (!empty($arrProduit['produit_option_valeur_nom'])) {
                echo ' (' . $arrProduit['produit_option_valeur_nom'] . ')';
            }
            echo '</td>
                    <td>' . $arrProduit['qte'] . '</td>
                    <td>' . $arrProduit['prix_ht'] . ' EUR</td>
                    <td>' . $arrProduit['prix_total_ht'] . ' EUR</td>
                </tr>';
        }
        echo '<tr>
                    <td colspan="6">Sous-total HT</td>
                    <td>' . round($arrCommande['stotal_ht'], 2) . ' EUR</td>
                 </tr>
                 <tr>
                    <td colspan="6">Frais de Port HT</td>
                    <td>' . round($arrCommande['commande_port_ht'], 2) . ' EUR</td>
                 </tr>
                 <tr>
                    <td colspan="6">Total HT</td>
                    <td>' . round($arrCommande['total_ht'], 2) . ' EUR</td>
                 </tr>
                 <tr>
                    <td colspan="6">Total TTC</td>
                    <td>' . round($arrCommande['total_ttc'], 2) . ' EUR</td>
                 </tr>
                 <tr>
                    <td colspan="6">Reste à payer</td>
                    <td>' . round($arrCommande['montant_a_paye'], 2) . ' EUR</td>
                 </tr>
                 <tr>
                    <td colspan="6">Dont T.V.A</td>
                    <td>' . round($arrCommande['commande_total_taxe'], 2) . ' EUR</td>
                </tr>';
        echo '</table>';

        echo '
<table class="records popup-details">
    <tr>
        <th height="15" colspan="2">HISTORIQUE DE LA COMMANDE</th>
    </tr>
    <tr>
        <th height="15">DATE</th>
        <th height="15">STATUT</th>
    </tr>
';

        foreach ($arrStat as $arrStatut) {
            echo '<tr class="unselect_enr0" align="center">
            <td>' . $arrStatut['commande_date'] . '</td>
            <td>' . $arrStatut['commande_statut_nom_nom'] . '</td>
        </tr>';

        }
        echo '</table>';
    }

    function export_comptable($formvars = array())
    {


        ini_set('max_execution_time', '3600');
        ini_set('memory_limit', '1G');
        $commande = new Commande();

        $option = array();
        $option['withFacture'] = 1;
        $option['list_commande_statut_id'] = '4';

        if (!empty($formvars['c*commande_facture_date'])) {


            $option['facture_date_debut'] = $formvars['c*commande_facture_date'][0];

            $option['facture_date_fin'] = $formvars['c*commande_facture_date'][1];
        }

        $arrCommande = $commande->get($option);


        $arrCommande2 = array();
        $filename = "export_comptable_" . time() . ".txt";
        $dirname = SITE_DIR . "export/comptable_commande/";
        $fh = fopen($dirname . $filename, "w");
        foreach ($arrCommande as $commande) {
            $compte = "707200";
            $libelle = "Ventes accessoires 19,6%";
            $sql = "SELECT client_nom FROM client WHERE client_id = '" . $commande['client_id'] . "'";
            $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
            $commande['client_nom'] = $this->sql->record['client_nom'];
            if ($commande['paiement_id'] == '1') {
                $commande['paiement_nom'] = "CB";
            }
            if ($commande['paiement_id'] == '2') {
                $commande['paiement_nom'] = "CH";
            }
            if ($commande['paiement_id'] == '5') {
                $commande['paiement_nom'] = "TE";
            }
            if ($commande['paiement_id'] == '6') {
                $commande['paiement_nom'] = "VIR";
            }
            if ($commande['paiement_id'] == '7') {
                $commande['paiement_nom'] = "PP";
            }
            if ($commande['paiement_id'] == '6') {
                $commande['paiement_nom'] = "KW";
            }
            if ($commande['paiement_id'] == '8') {
                $commande['paiement_nom'] = "KW";
            }
            if ($commande['facturation_pays'] != "France") {
                $compte = '707930';
                $libelle = "Ventes exportation";
            }
            array_push($arrCommande2, $commande);

            $txt = $commande['commande_facture_date'] . ";411" . strtoupper(mb_substr($commande['client_nom'], 0, 1)) . ";" . strtoupper(mb_substr($commande['client_nom'], 0, 1)) . " divers;" . $commande['total_ttc'] . ";;" . $commande['client_nom'] . ";VT;" . $commande['paiement_nom'] . ";" . $commande['commande_facture_numero'] . "\n";

            $txt .= $commande['commande_facture_date'] . ";445711;TVA collectée 19,6%;;" . ($commande['total_ttc'] - $commande['total_ht']) . ";" . $commande['client_nom'] . ";VT;;" . $commande['commande_facture_numero'] . "\n";

            $txt .= $commande['commande_facture_date'] . ";" . $compte . ";" . $libelle . ";;" . $commande['total_ht'] . ";" . $commande['client_nom'] . ";VT;;" . $commande['commande_facture_numero'] . "\n";
            fputs($fh, $txt);

        }

        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=" . $filename);
        readfile($dirname . $filename);


    }

    /**
     * @param array $formvars
     * @param bool $isCron
     * @return string|PHPExcel_Writer_Excel2007
     */
    public function export_produit_commande($formvars = array(), $isCron = false)
    {
        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        $fournisseur = new Fournisseur();
        $client = new Recipient();

        if (@$_GET['submitBTN'] == 'prod_import') {
            ini_set('display_errors', 1);
            ini_set('error_reporting', E_ALL);

            $invoice_filter = $this->getParam($formvars);

            $now = date('Ymd_His');
            $title = 'Export Produits commandés';

            $cmdModel = new Commande();

            $arrCommande = $cmdModel->get($invoice_filter);

            if (empty($arrCommande)) {
                echo '<br/><p>Aucune commande existante pour cette boutique</p><br/>';
                exit;
            }

            if (empty($invoice_filter['no_boutique']))
                $boutique = Boutique::getInstance()->get(array('boutique_id' => $invoice_filter['boutique_id']));

            $data = array();

            $listIdsCommande = array();

            foreach ($arrCommande as $cmd) {

                $arrProdCommande = $cmdModel->getProduit(array(
                    'commande_id' => $cmd['commande_id'],
                    'fournisseur_id' => $invoice_filter['fournisseur_id'],
                    'export_sap' => true
                ));

                $arrClient = $client->get([
                    'client_id' => $cmd['client_id'],
                    'no_boutique' => true
                ]);

                $listIdsCommande[] = $cmd['commande_id'];

                if (!empty($invoice_filter['no_boutique']))
                    $boutique = Boutique::getInstance()->get(array('boutique_id' => $cmd['boutique_id']));

                foreach ($arrProdCommande as $prodCmd) {

                    if ($invoice_filter['fournisseur_id'] != Fournisseur::METLIFE_ID) {
                        $data[] = $this->processRow($cmd, $prodCmd, $boutique);
                    } else {
                        $data[] = $this->processRowMetlife($cmd, $prodCmd, $arrClient[0]);
                    }
                }
            }

            $sqlUpdate = "UPDATE commande SET commande_produit_date_export = NOW() WHERE commande_id IN(" . implode(',', $listIdsCommande) . ")";

            $this->sql->query($sqlUpdate);

            if ($invoice_filter['fournisseur_id'] != Fournisseur::METLIFE_ID) {
                $objPHPExcel = $this->createSheet($title, $data);
            } else {
                $objPHPExcel = $this->createSheetMetlife($title, $data);
            }

            $filename = \Tools::filenamize($title);

            $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
            $file = $filename . '_' . \Tools::filenamize($boutique[0]['boutique_nom']) . '_' . $now . '.xlsx';
            $filePath = MEDIA_DIR . 'export/' . $file;

            if (!is_dir(MEDIA_DIR . 'export')) {
                mkdir(MEDIA_DIR . 'export');
            }

            if ($isCron) {
                return $objWriter;
            }

            $objWriter->save($filePath);

            $fileUrl = str_replace(SITE_DIR, "/", $filePath);
            echo '<br/><h3>L\'export a été généré et est disponible au lien ci-dessous :</h3><p><a href="' . $fileUrl . '">' . $file . '</a></p><br/>';
            exit;
        }

        $arrBoutique = Boutique::getInstance()->get();
        $arrFournisseur = $fournisseur->get(['no_boutique' => true]);

        echo '
		<form name="miniForm" method="post" action="/back/html/commande/export/export_produit_commande/" onsubmit="checkForm(this,1);flo();return false;">
		  <input type="hidden" value="" name="submitBTN"/>
		  <table class="records">
		    <thead>
		      <tr>
		        <th>Paramètres d\'export</th>
		      </tr>
		    </thead>
		    <tbody>
		      <tr class="resultr highlight" id="boutique_line_19">
		        <td class="">
		        <p>
                          <label>Boutique</label>
                          <select name="boutique_id">
                            <option value="">Toutes les boutiques</option>';
        foreach ($arrBoutique as $b) {
            echo '<option value="' . $b['boutique_id'] . '">' . $b['boutique_nom'] . '</option>';
        }
        echo '</select>
                <br>
                <br>
                <label for="fournisseur">Fournisseur</label>
                <select id="fournisseur" name="fournisseur_id">
                    <option value="0">Tous les fournisseurs</option>';
        foreach ($arrFournisseur as $f) {
            echo "<option value='{$f['fournisseur_id']}'>{$f['fournisseur_nom']}</option>";
        }
        echo '
                
                </select>
                </p>
                <br/>
                <p>
                    <label>Type d\'export</label>
                    <select name="export_type">
                        <option value="1">Commandes non exportées</option>
                        <option value="2">Toutes les commandes</option>
                    </select>
                </p>
		        </td>
		      </tr>
		    </tbody>
		  </table>
		  <table class="records">
		    <tbody>
		      <tr>
		        <td><input type="submit" value="VALIDER" name="prod_import" onclick="document.miniForm.submitBTN.value=this.name;" style="width: 100%;"/></td>
		      </tr>
		    </tbody>
		  </table>
		</form>
		';
    }


    public function generate_cmd_frs()
    {

        $commandeFournisseur = new CommandeFournisseur();
        $commandeFournisseur->generate();

        echo 'Les commandes fournisseurs ont été générées';

    }

    public function preparation_commande()
    {

        $commande = new Commande();
        $arrCommande = $commande->get(array('no_boutique' => true, 'commande_parent_not_null' => true));

        foreach ($arrCommande as $c) {
            if (in_array($c['commande_expedition_id'], array(Commande::EXPEDITION_MAGASIN, Commande::EXPEDITION_FOURNISSEUR)) && ($c['commande_statut_id'] == Commande::STATUT_PAIEMENT || $c['commande_statut_id'] == Commande::STATUT_APPROVISIONNEMENT)) {
                $commande->changeStatut($c['commande_id'], Commande::STATUT_PREPARATION, true, $c);
            } elseif ($c['commande_expedition_id'] == Commande::EXPEDITION_REAPPRO && $c['commande_statut_id'] == Commande::STATUT_PAIEMENT) {
                $commande->changeStatut($c['commande_id'], Commande::STATUT_APPROVISIONNEMENT, true, $c);
            }
        }
        echo 'Les commandes ont été mises à jour';
    }

    public function bon_commande()
    {

        $commandes = new Commande();
        $smarty = new Site_Smarty();

        if(!empty($_GET['commande_id'])) {
            $arrCommande = $commandes->get(
                array('no_boutique' => true
                , 'commande_id' => $_GET['commande_id']
               )
            );

        }
        else {
            $arrCommande = $commandes->get(
                array('no_boutique' => true
                , 'commande_parent_not_null' => true
                , 'commande_expedition' => Commande::EXPEDITION_MAGASIN
                , 'commande_statut_id' => Commande::STATUT_PREPARATION
                ,'order_livraison_nom'=>true)
            );
        }


        $commandes->remplirProduit($arrCommande, array('export_sap' => true));
        $htmlContent = '';

        if (count($arrCommande) != 0) {
            foreach ($arrCommande as $c) {
                $smarty->assign('commande', $c);
                $smarty->assign('produit', $c['produit']);
                $smarty->assign('logoHeader', '/media/logo-cadeauxprives-mail.png');
                $htmlContent .= $smarty->fetch('pdf/bl.tpl');
                $htmlContent .= '<br pagebreak="true"/>';
                $htmlContent .= $smarty->fetch('pdf/bp.tpl');
                $htmlContent .= '<br pagebreak="true"/>';
            }

            $pdf = new Pdf();
            if(!empty($_GET['commande_id'])) {
                $pdfName = 'BC-BP_' . $_GET['commande_id'];
            }
            else {
                $pdfName = 'BC-BP_' . date('Y-m-d');
            }


            $pdf->generate($htmlContent, $pdfName, 'Pdf_Legal_Default2', '', array(null, null), 'D');

        } else {

            echo "Aucunes commandes avec le statut 'En cours de préparation' pour le magasin.";
        }

    }

    /**
     * @param $formvars
     * @return array
     */
    protected function getParam($formvars)
    {
        // Paramètres utilisés pour le filtrage des commandes
        $invoice_filter = array();
        if (!empty($formvars['boutique_id'])) {
            $invoice_filter['boutique_id'] = $formvars['boutique_id'];
        } else {
            $invoice_filter['no_boutique'] = true;
        }
        if (!empty($formvars['export_type']) && $formvars['export_type'] == 1) {
            $invoice_filter['no_export'] = true;
        }
        if (!empty($formvars['time_since'])) {
            $invoice_filter['added_since'] = $formvars['time_since'];
        }
        $invoice_filter['fournisseur_id'] = $formvars['fournisseur_id'];

        return $invoice_filter;
    }

    protected function processRow($cmd, $prodCmd, $boutique)
    {
        $prodNom = $prodCmd['produit_nom'];
        if (!empty($prodCmd['produit_attribut_id'])) {
            $prodNom .= '(' . $prodCmd['produit_option_valeur_nom'] . ')';
        }

        $prodRef = $prodCmd['produit_ref'];
        if (!empty($prodCmd['produit_attribut_id'])) {
            $prodRef .= ' / ' . $prodCmd['produit_attribut_ref'];
        }

        $dataProd = array(
            $boutique[0]['boutique_nom'],
            $cmd['commande_id'],
            '',
            '',
            $cmd["commande_date_ajout"],
            $prodNom,
            $prodRef,
            $prodCmd['qte'],
            $prodCmd['prix_ht'],
            $prodCmd['prix_total_ht'],
            '',
            $cmd['client_id'],
            $cmd['livraison_societe'],
            $cmd['livraison_nom'],
            $cmd['livraison_rue'],
            $cmd['livraison_rue2'],
            $cmd['livraison_cp'],
            $cmd['livraison_ville'],
            $cmd['livraison_pays'],
            $cmd['livraison_telephone']
        );

        return $dataProd;
    }

    /**
     * @param $cmd
     * @param $prodCmd
     * @param $arrClient
     * @return array
     */
    protected function processRowMetlife($cmd, $prodCmd, $arrClient)
    {
        $row = [
            $prodCmd['commande_produit_id'],
            $prodCmd['produit_prix_ttc'],
            $prodCmd['qte'],
            $arrClient['client_nom'],
            $arrClient['client_prenom'],
            $cmd['client_societe'],
            '',
            $cmd['livraison_rue'],
            $cmd['livraison_rue2'],
            $cmd['livraison_cp'],
            $cmd['livraison_ville'],
            '',
            ''
        ];

        return $row;
    }

    /**
     * @param $title
     * @param $data
     * @return Excel
     */
    protected function createSheet($title, $data)
    {
        $entete = array(
            "CLIENT",
            "N° Cde",
            "",
            "",
            "Date",
            "Designations produits",
            "Ref produit",
            "Qté",
            "PU HT",
            "Montant HT",
            "",
            "ID bénéficiaire",
            "Raison sociale_Bon vente",
            "Complément Nom_Bon vente",
            "Rue Bon vente",
            "Localité Bon vente",
            "Code Postal_Bon vente",
            "Ville Bon vente",
            "Pays",
            "Téléphone Bon vente"
        );

        $objPHPExcel = new \Excel();
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setTitle($title);

        $arrWidth = array(
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T"
        );

        foreach ($arrWidth as $column) {
            $worksheet->getColumnDimension($column)->setWidth(25);
        }

        $l = 1;

        $enteteStyle = [
            'font' => array(
                'bold' => true,
                'name' => 'Calibri',
                'size' => 11
            ),
            'alignment' => array(
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrap' => false,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ];
        $objPHPExcel->writeArray($worksheet, $l, $entete);
        $worksheet->getStyle('A' . $l . ':T' . $l)->applyFromArray($enteteStyle);
        $l++;

        foreach ($data as $d) {
            $objPHPExcel->writeArray($worksheet, $l, $d);
            $l++;
        }

        return $objPHPExcel;
    }

    /**
     * @param $title
     * @param $data
     * @return Excel
     */
    protected function createSheetMetlife($title, $data)
    {
        $entete1 = [
            'Fichier pour Mise Sous Pli avec Affranchissement '
        ];

        $entete2 = array(
            "N°",
            "MONTANT DE LA CARTE",
            "QUANTITE CARTES",
            "NOM BENEFICIAIRE",
            "PRENOM BENEFICIAIRE",
            "ENTREPRISE",
            "ETAGE - BATIMENT - RESIDENCE",
            "ADRESSE",
            "COMPLEMENT ADRESSE",
            "CP",
            "VILLE",
            "MESSAGE LIGNE 1",
            "MESSAGE LIGNE 2"
        );

        $objPHPExcel = new \Excel();
        $objPHPExcel->setActiveSheetIndex(0);
        $worksheet = $objPHPExcel->getActiveSheet();
        $worksheet->setTitle($title);
        $worksheet->getDefaultColumnDimension()
            ->setWidth(25);

        $l = 1;

        $enteteStyle = [
            'font' => array(
                'bold' => true,
                'name' => 'Calibri',
                'size' => 9
            ),
            'alignment' => array(
                'horizontal' => 'center',
                'vertical' => 'center',
                'wrap' => false,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => 'ffff00'
                )
            )
        ];
        $objPHPExcel->writeArray($worksheet, $l++, $entete1);
        $worksheet->mergeCells('A1:M1');
        $objPHPExcel->writeArray($worksheet, $l, $entete2);
        $worksheet->getStyle('A' . $l . ':M' . $l)->applyFromArray($enteteStyle);
        $l++;

        foreach ($data as $d) {
            $objPHPExcel->writeArray($worksheet, $l++, $d);
        }

        $worksheet->getStyle('A1:M' . $l)
            ->applyFromArray([
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            ]);

        return $objPHPExcel;
    }

}

