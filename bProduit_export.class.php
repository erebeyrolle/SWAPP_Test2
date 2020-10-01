<?php

class bProduit_export extends BackModule {

    function bProduit_export($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    // Export le catalogue
    function export_catalogue($formvars = array()) {

        $arrParam = array();
        if (!empty($formvars['pc*categorie_id'])) {
            $arrParam['categorie_id'] = $formvars['pc*categorie_id'];
        }
        if (!empty($formvars['p*produit_statut_id'])) {
            $arrParam['produit_statut_list'] = implode($formvars['p*produit_statut_id'], ',');
        }
        if (!empty($formvars['p*marque_id'])) {
            $arrParam['marque_id'] = $formvars['p*marque_id'];
        }
        if (!empty($formvars['p*fournisseur_id'])) {
            $arrParam['fournisseur_id'] = $formvars['p*fournisseur_id'];
        }
        if (!empty($formvars['p*produit_signalement_id'])) {
            $arrParam['produit_signalement_id'] = $formvars['p*produit_signalement_id'];
        }

        $catalogue = new Catalogue;
        $catalogue->export($arrParam);
    }

    // Import des visuels produit
    function import_visuel_produit(){

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        if (@$_GET['submitBTN'] == 'cmd_import') {
            $catalogue = new Catalogue;
            $catalogue->import_visuel_produit();

            echo '<style>.recap td {text-align:left;}</style>
                <table class="records recap">
		    <thead>
		      <tr>
		        <th colspan="2" style="text-align:left;">R&eacute;capitulatif : </th>
		      </tr>
		    </thead>
		    <tbody>
		      <tr class="resultr highlight">
		        <td rowspan="3">
                    Visuels :		        
                </td>
		        <td>
		            '.$catalogue->rapport_import['visuel_insert'].' visuel'.($catalogue->rapport_import['visuel_insert'] > 1?'s':'').' importé'.($catalogue->rapport_import['visuel_insert'] > 1?'s':'').'
                </td>
              </tr>
              <tr class="resultr highlight">
		        <td>
		            <a href="#" onclick="jQuery(this).parent(\'td\').find(\'div\').toggle(); return false;">
		            '.$catalogue->rapport_import['visuel_produit_not_found'].' référence'.($catalogue->rapport_import['visuel_produit_not_found'] > 1?'s':'').' non trouvé'.($catalogue->rapport_import['visuel_produit_not_found'] > 1?'s':'').'
		            </a>
		            <div style="display: none;">'.implode('<br>', $catalogue->rapport_import['visuel_produit_not_found_list']).'</div>
                </td>
              </tr>
              <tr class="resultr highlight">
                <td>
		            <a href="#" onclick="jQuery(this).parent(\'td\').find(\'div\').toggle(); return false;">
		            '.$catalogue->rapport_import['visuel_produit_multiple'].' référence'.($catalogue->rapport_import['visuel_produit_multiple'] > 1?'s':'').' non unique'.($catalogue->rapport_import['visuel_produit_multiple'] > 1?'s':'').'
		            </a>
		            <div style="display: none;">'.implode('<br>', $catalogue->rapport_import['visuel_produit_multiple_list']).'</div>
                </td>
              </tr>
              <tr class="resultr">
                <td colspan="2">';
            if(!empty($catalogue->error)) {
                foreach($catalogue->error as $err){
                    echo '<div>Visuel : '.$err['visuel'].' - '.$err['commentaire'].'</div>';
                }
            }else{
                echo 'Aucune erreur';
            }
            echo '</td>
              </tr>
            </tbody>
          </table>';
        }else{
            $tab_visuel = scandir(Catalogue::IMPORT_PATH_VISUEL_PRODUIT);
            $num_visuel = count($tab_visuel) - 2;
            echo '
            <form name="miniForm" method="post" action="/back/html/produit/export/import_visuel_produit/" onsubmit="checkForm(this,1);flo();return false;">
              <input type="hidden" value="" name="submitBTN"/>
              <table class="records">
                <thead>
                  <tr>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="resultr highlight" id="boutique_line_19">
                    <td class="">
                    Il y a '.$num_visuel.' visuel'.($num_visuel > 1?'s':'').' à importer<br>
                    <a onclick="javascript:mcFileManager.open(\'miniForm\',\'file_import\',\'\',\'\',{}); return false;" href="#">Ajouter de nouvelles photos</a><br>
                    <i>déposer les photos dans /import/produits</i>
                    </td>
                  </tr>
                </tbody>
              </table>';
            if($num_visuel > 0) {
                echo '<table class="records">
                <tbody>
                  <tr>
                    <td><input type="submit" value="IMPORTER" name="cmd_import" onclick="document.miniForm.submitBTN.value=this.name;" style="width: 100%;"/></td>
                  </tr>
                </tbody>
              </table>';
            }
            echo '</form>';
        }
    }

    // import catalogue
    function import_catalogue($formvars = array()) {

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        if (@$_GET['submitBTN'] == 'cmd_import' && (!empty($formvars['file_import']) || pathinfo($formvars['file_import'], PATHINFO_EXTENSION) != 'csv' )) {
            // var_dump($formvars);exit;
            // nom du fichier Excel
            // $arrParam['filename'] = 'import_20100615.xls';
            $arrParam['filename'] = str_replace(SITE_URL,SITE_DIR,$formvars['file_import']);

            // liste des feuilles à parcourir
            $arrParam['sheets'] = array('Catalogue');

            // numéro de la ligne a laquelle commence les données à inserer, pour eviter les lignes d'entete
            $arrParam['startData'] = 2;

            // $arrParam['clear_bdd'] = true;

            $catalogue = new Catalogue;
            $catalogue->import($arrParam);

            echo '<style>.recap td {text-align:left;}</style>
                <table class="records recap">
		    <thead>
		      <tr>
		        <th colspan="2" style="text-align:left;">R&eacute;capitulatif : </th>
		      </tr>
		    </thead>
		    <tbody>
		      <tr class="resultr highlight">
		        <td rowspan="3">
                Produits :
                </td>
                <td>'.$catalogue->rapport_import['produit_total'].' produit'.($catalogue->rapport_import['produit_total']>0?'s':'').' traité'.($catalogue->rapport_import['produit_total']>0?'s':'').'.'.(!empty($catalogue->rapport_import['produit_without_category'])?'<br><a href="#" onclick="jQuery(this).parent(\'td\').find(\'div\').toggle(); return false;">'.count($catalogue->rapport_import['produit_without_category']).' produit'.(count($catalogue->rapport_import['produit_without_category'])>1?'s':'').' sans catégorie</a><div style="display: none;">'.implode('<br>', $catalogue->rapport_import['produit_without_category']):'').'</div></td>
              </tr>
              <tr class="resultr highlight">
                <td>'.$catalogue->rapport_import['produit_insert'].' produit'.($catalogue->rapport_import['produit_insert']>0?'s':'').' créé'.($catalogue->rapport_import['produit_insert']>0?'s':'').'.</td>
              </tr>
              <tr class="resultr highlight">
                <td>'.$catalogue->rapport_import['produit_update'].' produit'.($catalogue->rapport_import['produit_update']>0?'s':'').' modifié'.($catalogue->rapport_import['produit_update']>0?'s':'').'.</td>
              </tr>';
                if(!empty($catalogue->rapport_import['categorie_insert'])) {
                    echo '<tr class="resultr highlight">
                        <td>
                            Catégories :
                        </td>
                        <td>' . $catalogue->rapport_import['categorie_insert'] . ' catégorie'.($catalogue->rapport_import['categorie_insert']>0?'s':'').' créée'.($catalogue->rapport_import['categorie_insert']>0?'s':'').'.</td>
                      </tr>';
                }
                if(!empty($catalogue->rapport_import['marque_insert'])) {
                    echo '<tr class="resultr highlight">
                    <td>
                        Marques :
                    </td>
                    <td>' . $catalogue->rapport_import['marque_insert'] . ' marque'.($catalogue->rapport_import['marque_insert']>0?'s':'').' créée'.($catalogue->rapport_import['marque_insert']>0?'s':'').'.</td>
                  </tr>';
                }
                if(!empty($catalogue->rapport_import['fournisseur_insert'])) {
                    echo '<tr class="resultr highlight">
                    <td>
                        Fournisseurs :
                    </td>
                    <td>' . $catalogue->rapport_import['fournisseur_insert'] . ' fournisseur'.($catalogue->rapport_import['fournisseur_insert']>0?'s':'').' créé'.($catalogue->rapport_import['fournisseur_insert']>0?'s':'').'.</td>
                  </tr>';
                }
              echo '<tr class="resultr">
                <td colspan="2">';
                    if(!empty($catalogue->error)) {
                        foreach($catalogue->error as $err){
                            echo '<div>Produit : '.$err['produit'].' - '.$err['commentaire'].'</div>';
                        }
                    }else{
                        echo 'Aucune erreur';
                    }
                echo '</td>
              </tr>
            </tbody>
          </table>';
        }else{
        echo '
            <a href="'.SITE_URL . 'media/catalogue_produit-model-v2.csv">Télécharger un modèle de document</a>';

        if(!empty($formvars['file_import']) && pathinfo($formvars['file_import'], PATHINFO_EXTENSION) != 'csv' ){
            echo '<p>Merci d\'utiliser un fichier CSV pour l\'importation</p>';
        }

		echo '<form name="miniForm" method="post" action="/back/html/produit/export/import_catalogue/" onsubmit="checkForm(this,1);flo();return false;">
		  <input type="hidden" value="" name="submitBTN"/>
		  <table class="records">
		    <thead>
		      <tr>
		        <th></th>
		      </tr>
		    </thead>
		    <tbody>
		      <tr class="resultr highlight" id="boutique_line_19">
		        <td class="">
		        <p>
                          <label>Fichier Catalogue &agrave; Jour</label>
                          
                          <input type="button" onclick="javascript:mcFileManager.open(\'miniForm\',\'file_import\',\'\',\'\',{});" value="Parcourir" class="fileBTN"/>
                          <input type="text" size="20" class="required" style="width: 50%;" title="Le champ est requis" value="" name="file_import"/>
                        </p>

		        </td>
		      </tr>
		    </tbody>
		  </table>
		  <table class="records">
		    <tbody>
		      <tr>
		        <td><input type="submit" value="VALIDER" name="cmd_import" onclick="document.miniForm.submitBTN.value=this.name;" style="width: 100%;"/></td>
		      </tr>
		    </tbody>
		  </table>
		</form>
		';
        }
    }


    function export_inventaire($formvars = array()) {


        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"inventaire_".date('YmdHi').".csv\";" );
        header("Content-Transfer-Encoding: binary");

        $sql="SELECT p.produit_id, p.produit_ref, p.produit_fournisseur_ref";
        $sql.=", f.fournisseur_nom, pn.produit_nom_nom";
        $sql.=", p.produit_quantite, p.produit_type_id";
        $sql.=" FROM produit p";
        $sql.=" LEFT JOIN fournisseur f ON (p.fournisseur_id = f.fournisseur_id)";
        $sql.=" LEFT JOIN produit_nom pn ON (pn.produit_id = p.produit_id AND pn.langue_id = '".LANGUE_ID."')";
        $sql.=" WHERE p.produit_statut_id IN (1,2)";
        $sql.=" GROUP BY produit_id";

        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        $arrProduit = $this->sql->record;


        echo 'ID;';
        echo 'REF INTERNE;';
        echo 'REF FABRICANT;';
        echo 'FOURNISSEUR;';
        echo 'NOM;';
        echo 'ATTRIBUT;';
        echo 'TYPE;';
        echo 'STOCK;';

        echo "\r\n";

        foreach($arrProduit as $p) {

            switch($p['produit_type_id']) {

                case '1':
                    $typeProduit = 'avec stock';
                    break;
                case '2':
                    $typeProduit = 'sans stock';
                    break;
                case '3':
                    $typeProduit = 'non vendu';
                    break;

            }


            $sql="SELECT pa.produit_attribut_id, pa.produit_attribut_ref, pa.produit_attribut_ref_frs";
            $sql.=", pa.produit_attribut_quantite";
            $sql.=" FROM produit_attribut pa";
            $sql.=" WHERE pa.produit_attribut_statut_id IN (1,2)";
            $sql.=" AND pa.produit_id = '".$p['produit_id']."'";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $arrAttribut = $this->sql->record;

            if(!empty($arrAttribut)) {

                foreach($arrAttribut as $a) {

                    echo $p['produit_id'].'ATT'.$a['produit_attribut_id'].';';
                    echo $p['produit_attribut_ref'].';';
                    echo $p['produit_attribut_ref_frs'].';';
                    echo utf8_decode($p['fournisseur_nom']).';';
                    echo utf8_decode($p['produit_nom_nom']).';';
                    echo $typeProduit.';';
                    echo $a['produit_attribut_quantite'].';';
                    echo "\r\n";
                }



            }

            else {
                echo $p['produit_id'].';';
                echo $p['produit_ref'].';';
                echo $p['produit_fournisseur_ref'].';';
                echo utf8_decode($p['fournisseur_nom']).';';
                echo utf8_decode($p['produit_nom_nom']).';';
                echo ';';
                echo $typeProduit.';';
                echo $p['produit_quantite'].';';
                echo "\r\n";
            }





        }



    }

}

