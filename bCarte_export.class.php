<?php

class bCarte_export extends BackModule {

    public function __construct($formvars = array()) {
        parent::BackModule($formvars);
        $GLOBALS['displayMcFileManager'] = true;
    }

    // import catalogue
    function import_carte($formvars = array()) {

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        if (@$_GET['submitBTN'] == 'cmd_import') {
            //var_dump($formvars);exit;
            // nom du fichier Excel
            // $arrParam['filename'] = 'import_20100615.xls';
            $arrParam['filename'] = str_replace(SITE_URL, SITE_DIR,$formvars['file_import']);

            // liste des feuilles à parcourir
            $arrParam['sheets'] = array('Catalogue');

            // numéro de la ligne a laquelle commence les données à inserer, pour eviter les lignes d'entete
            $arrParam['startData'] = 2;

            $carte = new GiftCard();
            $import = $carte->import($arrParam);
            if (null === $import) {
               echo 'l\'import s\'est effectué avec succès';
            } else {
                echo nl2br($import);
            }

        }else{

            echo '
        <a href="'.SITE_URL.'media/documents/carte.xls">Fichier d\'exemple</a>
		<form name="miniForm" method="post" action="/back/html/carte/export/import_carte/" onsubmit="checkForm(this,1);flo();return false;">
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
                          <label>Fichier &agrave; Jour</label>

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

    // import catalogue
    function export_carte()
    {

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        $carte = new GiftCard();
        $import = $carte->export();
        if (!empty($import)) {
            echo nl2br($import);
        }

    }
}
