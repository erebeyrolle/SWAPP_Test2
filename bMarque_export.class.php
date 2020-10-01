<?php

class bMarque_export extends BackModule {

    function bMarque_export($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    // Import des visuels marque
    function import_visuel_marque(){

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        if (@$_GET['submitBTN'] == 'cmd_import') {
            $catalogue = new Catalogue;
            $catalogue->import_visuel_marque();

            echo '<style>.recap td {text-align:left;}</style>
                <table class="records recap">
		    <thead>
		      <tr>
		        <th colspan="2" style="text-align:left;">R&eacute;capitulatif : </th>
		      </tr>
		    </thead>
		    <tbody>
		      <tr class="resultr highlight">
		        <td rowspan="2">
                    Visuels :		        
                </td>
		        <td>
		            '.$catalogue->rapport_import['visuel_insert'].' visuel'.($catalogue->rapport_import['visuel_insert'] > 1?'s':'').' importé'.($catalogue->rapport_import['visuel_insert'] > 1?'s':'').'
                </td>
              </tr>
              <tr class="resultr highlight">
		        <td>
		            <a href="#" onclick="jQuery(this).parent(\'td\').find(\'div\').toggle(); return false;">
		            '.$catalogue->rapport_import['visuel_produit_not_found'].' marque'.($catalogue->rapport_import['visuel_produit_not_found'] > 1?'s':'').' non trouvé'.($catalogue->rapport_import['visuel_produit_not_found'] > 1?'s':'').'
		            </a>
		            <div style="display: none;">'.implode('<br>', $catalogue->rapport_import['visuel_produit_not_found_list']).'</div>
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
            $tab_visuel = scandir(Catalogue::IMPORT_PATH_VISUEL_MARQUE);
            $num_visuel = count($tab_visuel) - 2;
            echo '
            <form name="miniForm" method="post" action="/back/html/marque/export/import_visuel_marque/" onsubmit="checkForm(this,1);flo();return false;">
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
                    <i>déposer les photos dans /import/marques</i>
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
}
?>
