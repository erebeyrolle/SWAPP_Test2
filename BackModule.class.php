<?php

// PENDING : a revoir

class BackModule {

    var $sql = null;
    // Caract豩stiques du module
    var $name;
    // module_nom : titre du module
    var $name_url;
    // module_page : url du module
    var $parent;
    // parent_module_titre : titre du module parent
    var $parent_url;
    var $orderBy;
    var $groupBy;
    var $page;
    var $limit;
    var $request;
    // Requete de selection
    // --
    // Elements du module
    var $miniForm = array();
    var $labels = array();
    var $forms = array();
    var $bases = array();
    //Base de donn裨s) ger裨s) par le module
    // --
    var $path = array();
    //Chemin haut (Gestion truc > edition truc|ajout truc etc..)
    var $urlForms;
    // Forms to Url
    var $sqlForms;
    // Forms to SQL
    var $droits = array();
    var $div = false; // AJAX

    function BackModule($formvars = array()) {

        // Instance de la classe Site_SQL
        $this->sql = Site_SQL::getInstance();
        //--
        $this->path[] = "<span><a href='" . BACK_URL . "'><img alt='home_btn' src='" . BACK_URL . "styles/images/tableau.png' /></a>&nbsp;</span>";

        // Réperation des caractéstiques du module
        @list($arrM) = $this->get($formvars);

        $this->name = $arrM['module_nom'];
        $this->name_url = $arrM['module_page'];
        $this->parent = $arrM['parent_module_titre'];
        $this->groupBy = $arrM['module_group'];
        if (!empty($formvars['order']))
            $this->orderBy = $formvars['order'];
        else
            $this->orderBy = $arrM['module_order'];

        if (!empty($_SESSION['limit'][$this->name_url]))
            $this->limit = $_SESSION['limit'][$this->name_url];
        else
            $this->limit = $arrM['module_limit'];

        if (!empty($formvars['page']))
            $this->page = $formvars['page'];
        else
            $this->page = 1;

        if (!empty($formvars['parent_url']))
            $this->parent_url = $formvars['parent_url'];

        if (!empty($formvars['div']))
            $this->div = $formvars['div'];
        else
            $this->div = false;

        if (strpos($arrM['module_bases'], '|') === false)
            array_push($this->bases, $arrM['module_bases']);
        else
            $this->bases = explode('|', $arrM['module_bases']);


        $this->path[] = "<span>&raquo; <a href='" . $this->link_to('cleanList') . "'>" . $this->name . "</a></span>";

        $droit = new BackDroit;
        $this->droits = $droit->get($this->name_url);
        //--
    }

    /**
     * @param string $idx : 'path' | 'query'
     * @return string
     */

    function getUrl($idx = 'path'){
        $arr = parse_url($_SERVER['REQUEST_URI']);
        return (!empty($arr[$idx])) ? $arr[$idx] : '';
    }

    function get($formvars=array(), $champs="") {

        $sql = "SELECT bm.module_id, bm.module_parent_id, bm.module_nom, bm.module_titre, bm.module_page, bm.module_rang, bm.actif_id, bm.module_order, bm.module_group, bm.module_limit, bm.module_bases, bmp.module_titre as parent_module_titre";
        $sql.=" FROM back_module bm";
        $sql.=" LEFT JOIN back_module bmp ON (bmp.module_id = bm.module_parent_id)";
        $sql.=" WHERE 1";
        if (!empty($formvars['module_page']))
            $sql.=" AND bm.module_page = '" . $formvars['module_page'] . "'";

        if (empty($champs)) {
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            return $this->sql->record;
        } else {
            $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
            return $this->sql->record[$champs];
        }
    }

    function setVar($var, $value) {

        $this->$var = $value;
    }

    function getVar($var) {

        return $this->$var;
    }

    function addLabel($label) {

        if ($label->multi == false || ($label->multi == true && MULTI == 1)) {
            $label->setVar('pos', count($this->labels) + 2);
            array_push($this->labels, $label);
        }
    }

    function addForm($form) {

        $form->setVar('pos', count($this->labels) + 2);
        $form->setVar('parent', $this->name_url);
        array_push($this->forms, $form);
    }

    function displayPath() {

        $out = "<div id='path'>";
        foreach ($this->path as $link) {
            $out .= $link;
        }
        $out .= "</div>";
        $out .= "<div id='title'><h1>" . $this->name . "</h1></div>";

        return $out;
    }

    function displayList($type = 'html') {

        switch ($type) {
            case 'html':
                $out = $this->displayListHTML();
                break;
            case 'csv':
                $out = $this->displayListCSV();
                break;
            case 'sql':
                $out = $this->displayListSQL();
                break;
            case 'group':
                $out = $this->displayListGROUP();
                break;
            case 'simple':
                $out = $this->displayListSIMPLE();
                break;
            case 'graph':
                $out = $this->displayListGRAPH();
                break;
            case 'graphcsv':
                $out = $this->displayListGRAPHCSV();
                break;
            default :
                $out = $this->displayListHTML();
                break;
        }

        if ($this->div) {
            $out .= "<script type='text/javascript'>";
            $out .= "	var myTips = new Tips($$('.infobulle'));";
            $out .= "</script>";
        }
        //$out = \Bydedi\Template\Filter\SSL::getInstance()->replaceHttpProtocol($out);
        return $out;
    }

    function update_multi($formvars = array(), $table = null, $langue = true) {
        $arrT = $this->bases;
        $parent_key = false;

        foreach ($arrT as $table) {

            $sql_insert = '';
            $sql = '';
            $clef = '';

            $sql = "DESC $table";

            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $rows = $this->sql->record;

            $sql_insert = "UPDATE $table SET ";
            $i = 0;
            $values = "";

            foreach ($rows as $tab_champ) {
                if ($tab_champ['Key'] != "PRI") {

                    if (isset($formvars[strtolower($tab_champ['Field'])])) {
                        if ($i != 0) {
                            $sql_insert.=", ";
                            $values.=", ";
                        }

                        if (($tab_champ['Type'] == "date" || $tab_champ['Type'] == "datetime") && $formvars[strtolower($tab_champ['Field'])]) {
                            if ($tab_champ['Type'] == "date")
                                $day = $formvars[strtolower($tab_champ['Field'])];
                            else
                                list($day, $time ) = explode(" ", $formvars[strtolower($tab_champ['Field'])]);

                            list($jours, $mois, $an) = explode("/", $day);

                            if ($tab_champ['Type'] == "date")
                                $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours;
                            else
                                $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours . ' ' . $time;
                            //$tab_champ['Field'] = "DATE_FORMAT(".$tab_champ['Field'].",'%d/%m/%Y')";
                        }
                        if ($formvars[strtolower($tab_champ['Field'])] == "NULL") {
                            $sql_insert.=$tab_champ['Field'] . " = " . $formvars[strtolower($tab_champ['Field'])];
                        } else {
                            if (get_magic_quotes_gpc()) {
                                $sql_insert.=$tab_champ['Field'] . " = \"" . $formvars[strtolower($tab_champ['Field'])] . "\"";
                            } else {
                                $sql_insert.=$tab_champ['Field'] . " = \"" . $this->sql->db->real_escape_string($formvars[strtolower($tab_champ['Field'])]) . "\"";
                            }
                        }
                        $i++;
                    }
                } else {
                    $clef = $tab_champ['Field'];
                }
            }

            if (!$parent_key)
                $parent_key = $clef;

            if (strpos($table, '_nom') !== false && $parent_key) {
                $sql_insert.=" WHERE " . $parent_key . " = '" . $formvars['back_id'] . "'";
                $sql_insert.=" AND langue_id = '" . LANGUE . "'";
            }
            else
                $sql_insert.=" WHERE $clef = '" . $formvars['back_id'] . "'";

            if ($i > 0)
                $req = $this->sql->query($sql_insert);
        }

        if ($langue) {
            //echo "lol";
            $this->updateLangue($formvars, $arrT[0]);
        }
    }

    function update($formvars = array(), $table = null, $langue = true) {

        $arrT = array();

        if ($table != null) {
            array_push($arrT, $table);
        } else {
            $arrT = $this->bases;
        }

        foreach ($arrT as $table) {
            $sql_insert = '';
            $sql = '';
            $clef = '';

            $sql = "DESC $table";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $rows = $this->sql->record;

            $sql_insert = "UPDATE $table SET ";
            $i = 0;
            $values = "";

            foreach ($rows as $tab_champ) {
                if ($tab_champ['Key'] != "PRI") {
                    if (isset($formvars[strtolower($tab_champ['Field'])])) {
                        if ($i != 0) {
                            $sql_insert.=", ";
                            $values.=", ";
                        }

                        //$tmpField = $tab_champ['Field'];

                        if ($tab_champ['Type'] == "date" && $formvars[strtolower($tab_champ['Field'])]) {
                            list($jours, $mois, $an) = explode("/", $formvars[strtolower($tab_champ['Field'])]);

                            $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours; //2007-08-04
                        }
                        if ($tab_champ['Type'] == "datetime" && $formvars[strtolower($tab_champ['Field'])]) {
                            //echo($tab_champ['Field'] . " - " .$formvars[strtolower($tab_champ['Field'])]."<br />");

                            list($day, $time ) = explode(" ", $formvars[strtolower($tab_champ['Field'])]);
                            list($jours, $mois, $an) = explode("/", $day);

                            $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours . ' ' . $time; //2007-08-04
                        }

                        if ($formvars[strtolower($tab_champ['Field'])] == "NULL") {
                            $sql_insert.=$tab_champ['Field'] . " = " . $formvars[strtolower($tab_champ['Field'])];
                        } else {
                            if (get_magic_quotes_gpc()) {
                                $sql_insert.=$tab_champ['Field'] . " = \"" . $formvars[strtolower($tab_champ['Field'])] . "\"";
                            } else {
                                $sql_insert.=$tab_champ['Field'] . " = \"" . $this->sql->db->real_escape_string($formvars[strtolower($tab_champ['Field'])]) . "\"";
                            }
                        }
                        $i++;
                    }
                } else {
                    $clef = $tab_champ['Field'];
                }
            }

            $sql_insert.=" WHERE $clef = '" . $formvars['back_id'] . "'";

            $req = $this->sql->query($sql_insert);
        }

        if ($langue)
            $this->updateLangue($formvars, $arrT[0]);
        //--
    }

    function updateLangue($formvars, $table) {

        $lang = array();

        foreach ($formvars as $k => $v) {
            if (strpos($k, '|') !== false)
                list($langue_id, $champ) = explode("|", $k);
            else {
                $langue_id = $k;
                $champ = "";
            }
            if (!empty($langue_id) && !empty($champ)) {
                $lang[$langue_id][$champ] = $v;
            }
        }

        if ($lang) {

            foreach ($lang as $k => $v) {

                $sql = "SELECT " . $table . "_nom_id as id FROM " . $table . "_nom WHERE langue_id = " . $k . " AND " . $table . "_id = " . $formvars['back_id'] . "";
                $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
                $nbResults = $this->sql->record['id'];

                if ($nbResults) {
                    $v['back_id'] = $nbResults;

                    BackModule::update($v, $table . '_nom', false);
                } else {

                    $v['langue_id'] = $k; //id de la langue
                    $v[$table . '_id'] = $formvars['back_id']; //table_id = ID parent
                    $tableTMP = $table . '_nom';

                    BackModule::create($v, $tableTMP, false);
                }
            }
        }
    }

    function duplicate($formvars = array(), $table = null, $langue = true) {

        $id = $this->create($formvars, $table, $langue);
        return $id;
    }

    function create($formvars = array(), $table = null, $langue = true) {

        if (empty($formvars['langue_id']))
            $formvars['langue_id'] = LANGUE;

        $arrT = array();

        if ($table == null)
            $arrT = $this->bases;
        else
            array_push($arrT, $table);

        foreach ($arrT as $table) {

            $sql_insert = '';
            $sql = '';

            $sql = "DESC $table";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $rows = $this->sql->record;

            $sql_insert = "INSERT INTO $table (";
            $i = 0;
            $values = "";

            foreach ($rows as $tab_champ) {
                if (isset($formvars[strtolower($tab_champ['Field'])])) {
                    if ($i != 0) {
                        $sql_insert.=", ";
                        $values.=", ";
                    }

                    $sql_insert.=$tab_champ['Field'];

                    if ($tab_champ['Type'] == "date" && $formvars[strtolower($tab_champ['Field'])]) {
                        //list($day , $time ) = explode(" ",$formvars[strtolower($tab_champ['Field'])]);
                        list($jours, $mois, $an) = explode("/", $formvars[strtolower($tab_champ['Field'])]);

                        $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours; //2007-08-04
                        //$tab_champ['Field'] = "DATE_FORMAT(".$tab_champ['Field'].",'%d/%m/%Y')";
                    }
                    if ($tab_champ['Type'] == "datetime" && $formvars[strtolower($tab_champ['Field'])]) {
                        list($day, $time ) = explode(" ", $formvars[strtolower($tab_champ['Field'])]);
                        list($jours, $mois, $an) = explode("/", $day);

                        $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours . ' ' . $time; //2007-08-04
                        //$tab_champ['Field'] = "DATE_FORMAT(".$tab_champ['Field'].",'%d/%m/%Y')";
                    }
                    if ($formvars[strtolower($tab_champ['Field'])] == "NULL") {
                        $values.=$formvars[strtolower($tab_champ['Field'])];
                    } else {
                        if (get_magic_quotes_gpc()) {
                            $values.="\"" . $formvars[strtolower($tab_champ['Field'])] . "\"";
                        } else {
                            $values.="\"" . $this->sql->db->real_escape_string($formvars[strtolower($tab_champ['Field'])]) . "\"";
                        }
                    }


                    $i++;
                }
            }

            $sql_insert.=") VALUES ($values)";

            $req = $this->sql->query($sql_insert);

            $lastid = $this->sql->db->insert_id;
        }
        $formvars['back_id'] = $lastid;

        if ($langue)
            $this->updateLangue($formvars, $arrT[0]);

        //exit;
        return $lastid;
        //--
    }

    function create_multi($formvars = array(), $table = null, $langue = true) {
        $formvars['langue_id'] = LANGUE;

        $arrT = array();
        if ($table == null)
            $arrT = $this->bases;
        else
            array_push($arrT, $table);

        foreach ($arrT as $table) {
            $sql_insert = '';
            $sql = '';

            $sql = "DESC $table";
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $rows = $this->sql->record;

            $sql_insert = "INSERT INTO $table (";
            $i = 0;
            $values = "";
            foreach ($rows as $tab_champ) {
                if ($tab_champ['Key'] == "PRI") {
                    $clef = $tab_champ['Field'];
                }

                if (isset($formvars[strtolower($tab_champ['Field'])])) {
                    if ($i != 0) {
                        $sql_insert.=", ";
                        $values.=", ";
                    }

                    $sql_insert.=$tab_champ['Field'];

                    if (($tab_champ['Type'] == "datetime") && $formvars[strtolower($tab_champ['Field'])]) {
                        list($day, $time ) = explode(" ", $formvars[strtolower($tab_champ['Field'])]);
                        list($jours, $mois, $an) = explode("/", $day);

                        $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours . ' ' . $time; //2007-08-04
                        //$tab_champ['Field'] = "DATE_FORMAT(".$tab_champ['Field'].",'%d/%m/%Y')";
                    } elseif ($tab_champ['Type'] == "date" && $formvars[strtolower($tab_champ['Field'])]) {
                        list($jours, $mois, $an) = explode("/", $formvars[strtolower($tab_champ['Field'])]);

                        $formvars[strtolower($tab_champ['Field'])] = $an . '-' . $mois . '-' . $jours; //2007-08-04
                    }

                    if ($formvars[strtolower($tab_champ['Field'])] == "NULL") {
                        $values.=$formvars[strtolower($tab_champ['Field'])];
                    } else {
                        if (get_magic_quotes_gpc()) {
                            $values.="\"" . $formvars[strtolower($tab_champ['Field'])] . "\"";
                        } else {
                            $values.="\"" . $this->sql->db->real_escape_string($formvars[strtolower($tab_champ['Field'])]) . "\"";
                        }
                    }
                    $i++;
                }
            }

            $sql_insert.=") VALUES ($values)";

            //echo $sql_insert;exit();

            $req = $this->sql->query($sql_insert);

            $lastid = $this->sql->db->insert_id;

            $formvars[$clef] = $lastid;


            if (!isset($parent_key))
                $parent_key = $lastid;
        }


        $formvars['back_id'] = $parent_key;

        if ($langue)
            $this->updateLangue($formvars, $arrT[0]);

        return $parent_key;
    }

    function delete($formvars = array()) {

        foreach ($this->bases as $table) {
            $sql_insert = '';
            $sql = '';
            $clef = '';

            $sql = "DELETE FROM " . $table . " WHERE " . $table . "_id = " . $formvars['back_id'] . " LIMIT 1";
            $req = $this->sql->query($sql);

            return $req;
            exit;
        }
        //--
    }

    function delete_multi($formvars = array()) {

        foreach ($this->bases as $table) {
            $sql_insert = '';
            $sql = '';
            $clef = '';

            $sql = "DELETE FROM " . $table . " WHERE " . $table . "_id = " . $formvars['back_id'] . " LIMIT 1";
            $req = $this->sql->query($sql);

            return $req;
            exit;
        }
        //--
    }

    function displayListCSV() {

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        // SEARCH
        $isSearch = count($this->forms) > 0;
        if ($isSearch) {

            $js = "";
            $inputSearch = "";
            for ($i = 0; $i < count($this->forms); $i++) {
                if (isset($_GET[$this->forms[$i]->name]))
                    $inputSearch .= $this->forms[$i]->Displayform($_GET[$this->forms[$i]->name], $this);
                else
                    $inputSearch .= $this->forms[$i]->Displayform("", $this);
                if ($this->forms[$i]->value != '') {
                    $js.="showSearch('searchTableList');";
                    $this->urlForms.=$this->forms[$i]->getUrl();
                    $this->sqlForms.=$this->forms[$i]->getSql();
                }
            }
        }
        //-- SEARCH
        //  CONSTRUCTION DE LA REQUETE
        $sql = $this->request;
        $sql .=$this->sqlForms;

        // GROUP BY
        if ($this->groupBy)
            $sql.=" GROUP BY " . $this->groupBy;
        //-- GROUP BY
        // ORDER BY
        if (!empty($this->orderBy)) {
            if ($this->orderBy < 0)
                $sql.=" ORDER BY " . abs($this->orderBy) . " DESC";
            else
                $sql.=" ORDER BY " . $this->orderBy;
        }
        //-- ORDER BY
        // LIMIT
        //if($this->limit)
        //{
        //  $offset = ($this->page - 1) * $this->limit;
        //	$sql.=" LIMIT ".$offset.",".$this->limit;
        //}
        //-- LIMIT
        $this->sql->query($sql, SQL_ALL, SQL_INDEX);
        $rows = $this->sql->record;

        header('Content-Disposition: attachment; filename="export_' . $this->name_url . '_' . date('Y_m_d', time()) . '.csv"');
        header("Content-type: text/csv; charset=UTF-8");
        $out = "";
        foreach ($this->labels as $label) {
            $out .= '"' . $label->name . '"';
            $out .= ";";
        }
        $out .= "\r\n";

        foreach ($rows as $row) {
            $i = 1;
            foreach ($this->labels as $label) {
                if ($label->name == "MESSAGE") {
                    $out .= ";";
                    $i++;
                    continue;
                }
                $row[$i] = '"'.html_entity_decode(str_replace(array('"'), array('""'), $row[$i])).'"';

                $row[$i] = str_replace(';', ';', $row[$i]);
                $row[$i] = str_replace("\r\n", " ", $row[$i]);
                $row[$i] = str_replace("\n", " ", $row[$i]);
                $out .= $row[$i];
                $out .= " ; ";
                $i++;
            }
            $out .= "\r\n";
        }

        echo $out;
    }

    function displayListSQL() {

        // SEARCH
        $isSearch = count($this->forms) > 0;
        if ($isSearch) {
            $inputSearch = "";
            $js = "";
            for ($i = 0; $i < count($this->forms); $i++) {
                if (isset($_GET[$this->forms[$i]->name]))
                    $inputSearch .= $this->forms[$i]->Displayform($_GET[$this->forms[$i]->name], $this);
                else
                    $inputSearch .= $this->forms[$i]->Displayform('', $this);
                if ($this->forms[$i]->value != '') {
                    $js.="showSearch('searchTableList');";
                    $this->urlForms.=$this->forms[$i]->getUrl();
                    $this->sqlForms.=$this->forms[$i]->getSql();
                }
            }
        }
        //-- SEARCH
        //  CONSTRUCTION DE LA REQUETE
        $sql = $this->request;
        $sql2 = $this->sqlForms;

        // GROUP BY
        if ($this->groupBy)
            $sql2.=" GROUP BY " . $this->groupBy;
        //-- GROUP BY
        // ORDER BY
        if (!empty($this->orderBy)) {
            if ($this->orderBy < 0)
                $sql2.=" ORDER BY " . abs($this->orderBy) . " DESC";
            else
                $sql2.=" ORDER BY " . $this->orderBy;
        }
        return array($sql, $sql2);
    }

    function displayListHTML() {

        if (!$this->droits['READ']) {
            header('Location: /back/');
            exit;
            //exit;
        }
        $out = $this->displayPath();

        // SEARCH
        $isSearch = count($this->forms) > 0;
        if ($isSearch) {

            $js = "";
            $inputSearch = "";

            for ($i = 0; $i < count($this->forms); $i++) {

                if (isset($_GET[$this->forms[$i]->name]))
                    $inputSearch .= $this->forms[$i]->Displayform($_GET[$this->forms[$i]->name], $this);
                else
                    $inputSearch .= $this->forms[$i]->Displayform('', $this);

                if ($this->forms[$i]->value != '') {
                    if (strpos($js, "show") === false)
                        $js.="showSearch('searchTableList');";
                    $this->urlForms.=$this->forms[$i]->getUrl();
                    $this->sqlForms.=$this->forms[$i]->getSql();
                }
            }

            $search = "<div id='searchTableList' class='records' style='display:none;'>";
            $search .="<table style='border:1px solid #666;border-top:3px solid #666' class='records'><tr><td>";
            $search .="<form id='form1' action='" . $this->getUrl() . "' name='form1' method='get'>";
            $search .="<input type='hidden' name='order' value='" . $this->orderBy . "' />";
            $search .="<table style='width:100%'><tr><td style='border:0'>";
            $search .="<fieldset class='searchList'>";
            $search .= $inputSearch;
            $search .="</fieldset></td><td style='border:0' valign='bottom'><p class='SubmitP'><input type='submit' value='Appliquer' /></p>";
            $search .="</td></tr></table></form></td></tr></table></div>";
        }
        //-- SEARCH
        //  CONSTRUCTION DE LA REQUETE
        $sql = $this->request;
        $sql .=$this->sqlForms;

        // GROUP BY
        if ($this->groupBy)
            $sql.=" GROUP BY " . $this->groupBy;
        //-- GROUP BY
        // ORDER BY
        if (!empty($this->orderBy)) {
            if ($this->orderBy < 0)
                $sql.=" ORDER BY " . abs($this->orderBy) . " DESC";
            else
                $sql.=" ORDER BY " . $this->orderBy;
        }
        //-- ORDER BY
        // LIMIT
        if ($this->limit) {
            $offset = ($this->page - 1) * $this->limit;
            $sql.=" LIMIT " . $offset . "," . $this->limit;
        }
        //-- LIMIT
        //error_log($sql);
        $sql = preg_replace("#^SELECT([.])*#", "SELECT SQL_CALC_FOUND_ROWS\\1", trim($sql));

        //-- CONSTRUCTION
        //error_log($sql);
        //echo $sql;
        // RECUPERATION DES RESULTATS
        $this->sql->query($sql, SQL_ALL, SQL_INDEX);
        $rows = $this->sql->record;
        // --
        // RECUPERATION DU NB DE LIGNES TOTALES
        $sql = "SELECT FOUND_ROWS() as nb_rows";
        $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
        $nbResults = $this->sql->record['nb_rows'];
        // -- RECUPERATION DU NB DE LIGNES TOTALES
        // CONSTRUCTION DE LA PAGINATION
        if ($this->limit)
            $nbPage = ceil($nbResults / $this->limit); //Nombre total
        else {
            $nbPage = 1;
            $this->limit = $nbResults;
        }

        $curPage = $this->page; //Page en cours

        $records = "<table style='margin:0' id='%ID_RECORDS%' class='records'>";
        $records .= "<tbody><tr><td class='table_controls'><div class='box'>";

        $records .= "<div class='filter_controls'>";
        if ($nbPage > 1) {
            $records .= "<span class='button_label'>";

            if ($curPage > 1) {
                $records .= "<a href='" . $this->pageUrl(1) . "'><img alt='page_start' src='" . BACK_URL . "styles/images/page_start.png' /></a> ";
                $records .= "<a href='" . $this->pageUrl($curPage - 1) . "'><img alt='page_previous' src='" . BACK_URL . "styles/images/page_previous.png' /></a> ";
            }
            if ($curPage != 1)
                $records .= "<a href='" . $this->pageUrl(1) . "'>1</a> ";
            if ($curPage - 3 > 2)
                $records .= " ... ";
            if ($curPage - 3 == 2)
                $records .= "<a href='" . $this->pageUrl($curPage - 3) . "'>" . ($curPage - 3) . "</a> ";
            if ($curPage - 2 > 1)
                $records .= "<a href='" . $this->pageUrl($curPage - 2) . "'>" . ($curPage - 2) . "</a> ";
            if ($curPage - 1 > 1)
                $records .= "<a href='" . $this->pageUrl($curPage - 1) . "'>" . ($curPage - 1) . "</a> ";
            $records .= '<b>' . $curPage . '</b> ';
            if ($curPage + 1 < $nbPage)
                $records .= "<a href='" . $this->pageUrl($curPage + 1) . "'>" . ($curPage + 1) . "</a> ";
            if ($curPage + 2 < $nbPage)
                $records .= "<a href='" . $this->pageUrl($curPage + 2) . "'>" . ($curPage + 2) . "</a> ";
            if ($curPage + 3 < $nbPage)
                $records .= " ... ";
            if ($curPage != $nbPage)
                $records .= "<a href='" . $this->pageUrl($nbPage) . "'>" . $nbPage . "</a> ";
            if ($curPage < $nbPage) {
                $records .= "<a href='" . $this->pageUrl($curPage + 1) . "'><img alt='page_next' src='" . BACK_URL . "styles/images/page_next.png' /></a> ";
                $records .= "<a href='" . $this->pageUrl($nbPage) . "'><img alt='page_end' src='" . BACK_URL . "styles/images/page_end.png' /></a> ";
            }

            $records .= "</span>";
        }
        $records .= "</div>";

        $records .= '<div class="pagination_controls">';

        $debut = $offset + 1;
        $fin = $offset + $this->limit;
        if ($fin > $nbResults)
            $fin = $nbResults;

        $records .= '<span class="button_label">';
        if ($nbResults > 0
        )
            $records .= $debut . " - " . $fin . " sur ";
        $records .= $nbResults . " r&eacute;sultat";
        if ($nbResults > 1)
            $records.="s";
        $records .= '</span>';
        $records .= '<span class="button_label">- Affichage :</span>';
        $records .= '<select onchange="change_limit(this.options[this.selectedIndex].value,\'' . $this->name_url . '\',\'' . urlencode($this->pageUrl(1)) . '\');">';

        $arrLim = array('10', '25', '50', '100', '250', '500');
        foreach ($arrLim as $valLim) {
            $selected = (($this->limit == $valLim) ? 'selected="selected"' : '');
            $records .= '<option value="' . $valLim . '" ' . $selected . '>' . $valLim . '</option>';
        }
        /*
          $records .= '<option value="10" '.$selected.'>10</option>';
          $records .= '<option value="25" '.$selected.'>25</option>';
          $records .= '<option value="50" '.$selected.'>50</option>';
          $records .= '<option value="100" '.$selected.'>100</option>';
          $records .= '<option value="250" '.$selected.'>250</option>';
          $records .= '<option value="500" '.$selected.'>500</option>'; */

        $records .= '</select>';
        $records .= '</div>';

        $records .= "</div></td></tr></tbody>";
        $records .= "</table>";
        //-- CONSTRUCTION DE LA PAGINATION

        $searchClass = (($isSearch) ? "" : "off");

        $out .= '
		<div>
		  <div id="TabView" style="width:100%;border-bottom:3px solid #666;">
		    <div class="display_control_tabs" >
		      <dl>
		        <dt> Afficher : </dt>
		        <dd>
		          <ul class="tabs">
		            <li class="first ' . $searchClass . '" id="tab_0"> <a onclick="showSearch(\'searchTableList\'); return false;" href="#"> Rechercher </a> </li>
		           ';
        if ($this->droits['ADD'])
            $out .= '<li class="" id="tab_1"> <a href="' . $this->link_to('addForm') . '"> Ajouter </a> </li>';
        else
            $out .= '<li class="off" id="tab_1"> <a href="#"> Ajouter </a> </li>';
        $out .= '<li class="off" id="tab_2"> <a href="#"> Modifier </a> </li>
		            <li class="off" id="tab_4"> <a href="#"> Supprimer </a> </li>
		          </ul>
		        </dd>
		      </dl>
		    </div>
		   <div class="records_view_options">
		      <dl>
		        <dt> Action &rsaquo; </dt>
		        <dd>
		          <ul>
		           <!-- <li class="table_view_option"> <a class="current" id="f_table_view_option" href="' . $this->link_to('export', array('type' => 'pdf')) . '"> PDF </a> </li>-->
		            <li title="Informations::Export du listing en CSV" class="infobulle pie_view_option_CSV"> <div class="overlay_export_bg"></div><div class="overlay_export">CSV</div> <a class="f_pie_view_option" href="' . $this->link_to('export', array('type' => 'csv')) . '"> CSV </a> </li>
		';
        $sql = "SELECT * FROM back_module_export me, back_module m WHERE me.module_id=m.module_id AND module_page = '" . $this->name_url . "' AND module_export_import = 0 ORDER BY module_export_rang";
        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        $arrExport = $this->sql->record;
        foreach ($arrExport as $export) {
            if ($export['module_export_out'] == "ajax")
                $exportUrl = "ajax_form('" . $this->link_to('moduleExport', array($export['module_export_methode'])) . "')";
            else
                $exportUrl = "document.location.href='" . $this->link_to('moduleExport', array($export['module_export_methode'])) . "'";
            if (!empty($export['module_export_alert']))
                $out.= '<li title="Informations::' . $export['module_export_nom'] . '" onclick="if(confirm(\'' . addslashes($export['module_export_alert']) . '\')) ' . $exportUrl . '; else return false;" class="pie_view_option_' . $export['module_export_type'] . ' infobulle"><div class="overlay_export_bg"></div><div class="overlay_export">' . $export['module_export_mini'] . '</div><a class="f_pie_view_option" href="#"> ' . $export['module_export_type'] . ' </a> </li>';
            else
                $out.= '<li title="Informations::' . $export['module_export_nom'] . '" onclick="' . $exportUrl . '" class="pie_view_option_' . $export['module_export_type'] . ' infobulle"><div class="overlay_export_bg"></div><div class="overlay_export">' . $export['module_export_mini'] . '</div><a class="f_pie_view_option" href="#" > ' . $export['module_export_type'] . ' </a> </li>';
        }
        $out.= '
		          </ul>
		        </dd>
		      </dl>';
        $sql = "SELECT * FROM back_module_export me, back_module m WHERE me.module_id=m.module_id AND module_page = '" . $this->name_url . "' AND module_export_import = 1 ORDER BY module_export_rang";
        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        $arrImport = $this->sql->record;
        if ($arrImport) {
            $out.= '
		      <dl style="margin-left:5px;">
		        <dt> Import &rsaquo; </dt>
		        <dd>
		          <ul>
		';

            foreach ($arrImport as $export) {
                if ($export['module_export_out'] == "ajax")
                    $exportUrl = "ajax_form('" . $this->link_to('moduleExport', array($export['module_export_methode'])) . "')";
                else
                    $exportUrl = "document.location.href='" . $this->link_to('moduleExport', array($export['module_export_methode'])) . "'";
                if (!empty($export['module_export_alert']))
                    $out.= '<li title="Informations::' . $export['module_export_nom'] . '" onclick="if(confirm(\'' . addslashes($export['module_export_alert']) . '\')) ' . $exportUrl . '; else return false;" class="pie_view_option_' . $export['module_export_type'] . ' infobulle"><div class="overlay_export_bg"></div><div class="overlay_export">' . $export['module_export_mini'] . '</div><a class="f_pie_view_option" href="#"> ' . $export['module_export_type'] . ' </a> </li>';
                else
                    $out.= '<li title="Informations::' . $export['module_export_nom'] . '" onclick="' . $exportUrl . '" class="pie_view_option_' . $export['module_export_type'] . ' infobulle"><div class="overlay_export_bg"></div><div class="overlay_export">' . $export['module_export_mini'] . '</div><a class="f_pie_view_option" href="#" > ' . $export['module_export_type'] . ' </a> </li>';
            }
            $out.= '
		          </ul>
		        </dd>
		      </dl>		';
        }

        $out.='</div>
		    <div class="clear"></div>
		  </div>
		</div>
		';

        $out .= "<div>";
        $out .= "<div class='table'>";
        if (!empty($search))
            $out .= $search;
        $out .= str_replace('%ID_RECORDS%', 'records_top', $records); // AFFICHAGE DE LA PAGINATION
        $out .= "<table id='back_results' class='records'>";
        // AFFICHAGE DES EN-TETES
        $out .= "<thead>";
        $out .= "<tr>";
        $nbLabels = 1;
        foreach ($this->labels as $label) {
            $nbLabels++;
            $out .= $label->displayName($this);
        }
        $out .= "</tr>";
        $out .= "</thead>";
        // --
        // AFFICHAGE DES RESULTATS
        $out .= "<tbody>";
        $highlight = 0;
        foreach ($rows as $row) {
            $highlight = abs(1 - ($highlight));

            $class = (($highlight) ? 'highlight' : '');

            $out .= "<tr id='" . $this->name_url . "_line_" . $row[0] . "' class='resultr $class'>";

//			if($this->droits['CONTEXT'] && ($this->droits['MOD'] || $this->droits['DEL']))

            if ($this->droits['CONTEXT'] && ($this->droits['MOD'] || $this->droits['DEL']) && $row[0] != 'no_context') {

                $out.="<td style='width:11px;padding:0.6em 0.3em;'>" . $this->displayContextMenu($row[0]) . "</td>";
            }
            //$out .= "<td style='width:11px;padding:0.6em 0.3em;'><img style='cursor:pointer' alt='context_menu_btn' onmouseover=\"context_menu('".$this->link_to('contextMenu', array('id' => $row[0]))."',this); return false;\" src='".BACK_URL."styles/images/folder.gif' /></td>";

            if ($row[0] == 'no_context')
                $out .= "<td style='width:11px;padding:0.6em 0.3em;'></td>";



            //$out .= "<td style='width:11px;padding:0.6em 0.3em;'><img style='cursor:pointer' alt='context_menu_btn' onclick=\"context_menu('".$row[0]."','".$this->name_url."'); return false;\" src='".BACK_URL."styles/images/folder.gif' /></td>";

            $i = 1;
            foreach ($this->labels as $label) {
                $label->setVar('curID', $row[0]);
                $out .= $label->displayData($this, @$row[$i]);
                $i++;
            }
            $out .= "</tr>";
            $out .= "\n";
        }

        $displayNoRows = ((count($rows) > 0) ? 'display:none;' : 'display:cell;' );

        $out .= "<tr><td id='noresultr' colspan='" . $nbLabels . "' style='" . $displayNoRows . "text-align:center;font-weight:bold;text-align:center;'>Aucun R&eacute;sultat</td></tr>";
        $out .= "</tbody>";
        //-- AFFICHAGE DES RESULTATS
        $out .= "</table>";
        $out .= str_replace('%ID_RECORDS%', 'records_bottom', $records); // AFFICHAGE DE LA PAGINATION
        $out .= "</div>";
        $out .= "</div>";

        //$out .= "<form id='ajax_form'><input name='ajax_obj' id='ajax_obj' type='text' value='".urlencode(gzencode(serialize($this)))."' /></form>";

        $out .= "<script type='text/javascript'>";
        if (!empty($js))
            $out .= $js;
        $out .= "</script>";

        return $out;
    }

    function displayListGROUP() {

        if (!$this->droits['READ']) {
            return "Vous n'avez pas les droits n&eacute;c&eacute;ssaires.";
            exit;
        }

        //  CONSTRUCTION DE LA REQUETE
        $sql = $this->request;
        $sql .=$this->sqlForms;

        // GROUP BY
        if ($this->groupBy)
            $sql.=" GROUP BY " . $this->groupBy;
        //-- GROUP BY
        // ORDER BY
        if (!empty($this->orderBy)) {
            if ($this->orderBy < 0)
                $sql.=" ORDER BY " . abs($this->orderBy) . " DESC";
            else
                $sql.=" ORDER BY " . $this->orderBy;
        }
        //-- ORDER BY
        // LIMIT
        if ($this->limit) {
            //$offset = ($this->page - 1) * $this->limit;
            $offset = 0;
            $sql.=" LIMIT " . $offset . "," . $this->limit;
        }
        //-- LIMIT
        //echo htmlentities($sql);

        $sql = preg_replace("#^SELECT([.])*#", "SELECT SQL_CALC_FOUND_ROWS\\1", trim($sql));

        //-- CONSTRUCTION
        // RECUPERATION DES RESULTATS
        $this->sql->query($sql, SQL_ALL, SQL_INDEX);
        $rows = $this->sql->record;
        // --
        // RECUPERATION DU NB DE LIGNES TOTALES
        $sql = "SELECT FOUND_ROWS() as nb_rows";
        $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
        $nbResults = $this->sql->record['nb_rows'];
        // -- RECUPERATION DU NB DE LIGNES TOTALES

        $out = "<div>";
        $out .= "<div class='table'>";
        $out .= "<table class='records'>";
        // AFFICHAGE DES EN-TETES
        $out .= "<thead>";

        $out .= "<tr id='tr_" . $this->name_url . "'>";
        $nbLabels = 1;
        foreach ($this->labels as $label) {
            $nbLabels++;
            $out .= $label->displayName($this);
        }
        $out .= "</tr>";
        $out .= "</thead>";
        // --
        // AFFICHAGE DES RESULTATS
        $out .= "<tbody>";
        $highlight = 0;
        foreach ($rows as $row) {
            $highlight = abs(1 - ($highlight));

            $class = (($highlight) ? 'highlight' : '');

            $out .= "<tr id='" . $this->name_url . "_line_" . $row[0] . "' class='resultr $class'>";

//			if($this->droits['CONTEXT'] && ($this->droits['MOD'] || $this->droits['DEL']))

            if ($this->droits['CONTEXT'] && ($this->droits['MOD'] || $this->droits['DEL']) && $row[0] != 'no_context')
                $out.="<td style='width:11px;padding:0.6em 0.3em;'>" . $this->displayContextMenu($row[0], 'ajax_pop') . "</td>";
            //$out .= "<td style='width:11px;padding:0.6em 0.3em;'><img style='cursor:pointer' alt='context_menu_btn' onmouseover=\"context_menu('".$this->link_to('contextMenu', array('id' => $row[0], 'div' => 'ajax_pop'))."',this); return false;\" src='".BACK_URL."styles/images/folder.gif' /></td>";
            if ($row[0] == 'no_context')
                $out .= "<td style='width:11px;padding:0.6em 0.3em;'></td>";
            //$out .= "<td style='width:11px;padding:0.6em 0.3em;'><img style='cursor:pointer' alt='context_menu_btn' onclick=\"context_menu('".$row[0]."','".$this->name_url."'); return false;\" src='".BACK_URL."styles/images/folder.gif' /></td>";

            $i = 1;
            foreach ($this->labels as $label) {
                $label->setVar('curID', $row[0]);
                $out .= $label->displayData($this, $row[$i]);
                $i++;
            }
            $out .= "</tr>";
            $out .= "\n";
        }

        $displayNoRows = ((count($rows) > 0) ? 'display:none;' : 'display:cell;' );

        $out .= "<tr style='" . $displayNoRows . "'><td colspan='" . $nbLabels . "' style='text-align:center;font-weight:bold;text-align:center;'>Aucun R&eacute;sultat</td></tr>";
        $out .= "</tbody>";
        //-- AFFICHAGE DES RESULTATS
        $out .= "</table>";
        $out .= "</div>";
        $out .= "</div>";

        //$out .= "<form id='ajax_form'><input name='ajax_obj' id='ajax_obj' type='text' value='".urlencode(gzencode(serialize($this)))."' /></form>";

        $out .= "<script type='text/javascript'>";
        if (!empty($js))
            $out .= $js;
        $out .= "initGroup('tr_" . $this->name_url . "');";

        $out .= "</script>";

        return $out;
    }

    function displayML($base, $form) {
        $sql = "SELECT b." . $base . "_nom_id, b." . $form->name . " as data, l.langue_id, l.langue_ini, l.langue_img FROM langue l LEFT JOIN " . $base . "_nom b ON (l.langue_id = b.langue_id AND b." . $base . "_id = '" . $form->curID . "') WHERE l.langue_ini <> '" . LANGUE_ISO . "' ";
        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
        $rows = $this->sql->record;
        $name = $form->name;
        foreach ($rows as $row) {
            $form->setVar('translate', $row['langue_ini']);
            $form->setVar('champs', $name);
            $form->delAttr('class', 'required');
            $form->delAttr('id', $name . '_' . $row['langue_id']);
            $form->addAttr('id', $name . '_' . $row['langue_id']);
            $form->setVar('name', $row['langue_id'] . '|' . $name);
            $out .= $form->displayForm($row['data'], $this);

            if ($form->tinyMCE) {
                $out .= "<script type=\"text/javascript\">";
                $out .= "tinyMCE.execCommand('mceAddControl', false, '" . $name . '_' . $row['langue_id'] . "');";
                $out .= "</script>";
            }
        }

        return $out;
    }

    function displayListSIMPLE() {

        // SEARCH
        $isSearch = count($this->forms) > 0;
        if ($isSearch) {

            $js = "";
            $inputSearch = "";
            for ($i = 0; $i < count($this->forms); $i++) {
                if (isset($_GET[$this->forms[$i]->name]))
                    $inputSearch .= $this->forms[$i]->Displayform($_GET[$this->forms[$i]->name], $this);
                else
                    $inputSearch .= $this->forms[$i]->Displayform("", $this);
                if ($this->forms[$i]->value != '') {
                    $this->urlForms.=$this->forms[$i]->getUrl();
                    $this->sqlForms.=$this->forms[$i]->getSql();
                }
            }

            $search = "<div id='searchTableList' class='records'>";
            $search .="<table style='border:1px solid #666;border-top:3px solid #666' class='records'><tr><td>";
            $search .="<form onsubmit=\"checkForm(this);return false;\" id='form1' action='" . $this->getUrl() . "' name='form1' method='get'>";
            $search .="<input type='hidden' name='order' value='" . $this->orderBy . "' />";
            $search .="<table style='width:100%'><tr><td style='border:0'>";
            $search .="<fieldset class='searchList'>";
            $search .= $inputSearch;
            $search .="</fieldset></td><td style='border:0' valign='bottom'><p class='SubmitP'><input type='submit' value='Appliquer' /></p>";
            $search .="</td></tr></table></form></td></tr></table></div>";
        }
        //-- SEARCH
        //  CONSTRUCTION DE LA REQUETE
        $sql = $this->request;
        $sql .=$this->sqlForms;

        // GROUP BY
        if ($this->groupBy)
            $sql.=" GROUP BY " . $this->groupBy;
        //-- GROUP BY
        // ORDER BY
        if (!empty($this->orderBy)) {
            if ($this->orderBy < 0)
                $sql.=" ORDER BY " . abs($this->orderBy) . " DESC";
            else
                $sql.=" ORDER BY " . $this->orderBy;
        }
        //-- ORDER BY
        // LIMIT
        if ($this->limit) {
            $offset = ($this->page - 1) * $this->limit;
            $sql.=" LIMIT " . $offset . "," . $this->limit;
        }
        //-- LIMIT


        $sql = preg_replace("#^SELECT([.])*#", "SELECT SQL_CALC_FOUND_ROWS\\1", trim($sql));

        //-- CONSTRUCTION
        // RECUPERATION DES RESULTATS
        $this->sql->query($sql, SQL_ALL, SQL_INDEX);
        $rows = $this->sql->record;
        // --
        // RECUPERATION DU NB DE LIGNES TOTALES
        $sql = "SELECT FOUND_ROWS() as nb_rows";
        $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
        $nbResults = $this->sql->record['nb_rows'];
        // -- RECUPERATION DU NB DE LIGNES TOTALES
        // CONSTRUCTION DE LA PAGINATION
        if ($this->limit)
            $nbPage = ceil($nbResults / $this->limit); //Nombre total
        else {
            $nbPage = 1;
            $this->limit = $nbResults;
        }

        $curPage = $this->page; //Page en cours

        $records = "<table style='margin:0' id='%ID_RECORDS%' class='records'>";
        $records .= "<tbody><tr><td class='table_controls'><div class='box'>";

        $records .= "<div class='filter_controls'>";
        if ($nbPage > 1) {
            $records .= "<span class='button_label'>";

            if ($curPage > 1) {
                $records .= "<a href='" . $this->pageUrl(1) . "'><img alt='page_start' src='" . BACK_URL . "styles/images/page_start.png' /></a> ";
                $records .= "<a href='" . $this->pageUrl($curPage - 1) . "'><img alt='page_previous' src='" . BACK_URL . "styles/images/page_previous.png' /></a> ";
            }
            if ($curPage != 1)
                $records .= "<a href='" . $this->pageUrl(1) . "'>1</a> ";
            if ($curPage - 3 > 2)
                $records .= " ... ";
            if ($curPage - 3 == 2)
                $records .= "<a href='" . $this->pageUrl($curPage - 3) . "'>" . ($curPage - 3) . "</a> ";
            if ($curPage - 2 > 1)
                $records .= "<a href='" . $this->pageUrl($curPage - 2) . "'>" . ($curPage - 2) . "</a> ";
            if ($curPage - 1 > 1)
                $records .= "<a href='" . $this->pageUrl($curPage - 1) . "'>" . ($curPage - 1) . "</a> ";
            $records .= '<b>' . $curPage . '</b> ';
            if ($curPage + 1 < $nbPage)
                $records .= "<a href='" . $this->pageUrl($curPage + 1) . "'>" . ($curPage + 1) . "</a> ";
            if ($curPage + 2 < $nbPage)
                $records .= "<a href='" . $this->pageUrl($curPage + 2) . "'>" . ($curPage + 2) . "</a> ";
            if ($curPage + 3 < $nbPage)
                $records .= " ... ";
            if ($curPage != $nbPage)
                $records .= "<a href='" . $this->pageUrl($nbPage) . "'>" . $nbPage . "</a> ";
            if ($curPage < $nbPage) {
                $records .= "<a href='" . $this->pageUrl($curPage + 1) . "'><img alt='page_next' src='" . BACK_URL . "styles/images/page_next.png' /></a> ";
                $records .= "<a href='" . $this->pageUrl($nbPage) . "'><img alt='page_end' src='" . BACK_URL . "styles/images/page_end.png' /></a> ";
            }

            $records .= "</span>";
        }
        $records .= "</div>";

        $records .= '<div class="pagination_controls">';

        $debut = $offset + 1;
        $fin = $offset + $this->limit;
        if ($fin > $nbResults)
            $fin = $nbResults;

        $records .= '<span class="button_label">';
        if ($nbResults > 0
        )
            $records .= $debut . " - " . $fin . " sur ";
        $records .= $nbResults . " r&eacute;sultat";
        if ($nbResults > 1)
            $records.="s";
        $records .= '</span>';
        $records .= '</div>';

        $records .= "</div></td></tr></tbody>";
        $records .= "</table>";
        //-- CONSTRUCTION DE LA PAGINATION


        $searchClass = (($isSearch) ? "" : "off");

        $out = "<div>";
        $out .= "<div class='table'>";
        if (!empty($search))
            $out .= $search;


        //MINI FORM
        if ($this->miniForm) {
            $out .="<form onsubmit=\"checkForm(this);return false;\" action='" . $this->getUrl() . "?" . $_SERVER['QUERY_STRING'] . "' method='post' name='miniForm'>";
            $out .="<input type='hidden' name='submitBTN' value='' />";
        }
        //-- MINI FORM

        $out .= "<table class='records'>";
        // AFFICHAGE DES EN-TETES
        $out .= "<thead>";
        $out .= "<tr>";
        $nbLabels = 1;
        foreach ($this->labels as $label) {
            $nbLabels++;
            $out .= $label->displayName($this);
        }
        $out .= "</tr>";
        $out .= "</thead>";
        // --
        // AFFICHAGE DES RESULTATS
        $out .= "<tbody>";
        $highlight = 0;
        foreach ($rows as $row) {
            $highlight = abs(1 - ($highlight));

            $class = (($highlight) ? 'highlight' : '');

            $out .= "<tr id='" . $this->name_url . "_line_" . $row[0] . "' class='resultr $class'>";

            $i = 1;
            foreach ($this->labels as $label) {
                $label->setVar('curID', $row[0]);
                $out .= $label->displayData($this, $row[$i]);
                $i++;
            }
            $out .= "</tr>";
            $out .= "\n";
        }

        $displayNoRows = ((count($rows) > 0) ? 'display:none;' : 'display:cell;' );

        $out .= "<tr><td id='noresultr' colspan='" . $nbLabels . "' style='" . $displayNoRows . "text-align:center;font-weight:bold;text-align:center;'>Aucun R&eacute;sultat</td></tr>";
        $out .= "</tbody>";
        //-- AFFICHAGE DES RESULTATS
        $out .= "</table>";

        $out .= str_replace('%ID_RECORDS%', 'records_bottom', $records); // AFFICHAGE DE LA PAGINATION
        //MINI FORM
        if ($this->miniForm) {
            $out .= "<table class='records'><tr><td>";

            $out .= "<input style='width:100%' type='submit' onclick=\"document.miniForm.submitBTN.value=this.value;\" name='" . $this->miniForm['submitName'] . "' value='" . $this->miniForm['submitValue'] . "'>";
            $out .= "</td></tr></table>";
            $out .= "</form>";
        }
        //-- MINI FORM

        $out .= "</div>";
        $out .= "</div>";

        //$out .= "<form id='ajax_form'><input name='ajax_obj' id='ajax_obj' type='text' value='".urlencode(gzencode(serialize($this)))."' /></form>";

        $out .= "<script type='text/javascript'>";
        $out .= "	initPopUp('div_ajaxForm');";
        $out .= "</script>";

        return $out;
    }

    function displayForm($id, $div = '') {
        $out = "";
        $form = new BackForm("Date Modif", "back_now", $this->name_url . "_date_modif");
        $this->addForm($form);

        if (!$id) {
            $form = new BackForm("Date Ajout", "back_now", $this->name_url . "_date_ajout");
            $this->addForm($form);
        }

        $div = $this->div;

        if ($id && !$this->droits['MOD']) {
            $out = "Vous ne disposez pas des autorisations n&eacute;c&eacute;ssaires";
            return $out;
            exit;
        } else if (!$id && !$this->droits['ADD']) {
            $out = "Vous ne disposez pas des autorisations n&eacute;c&eacute;ssaires";
            return $out;
            exit;
        }



        if (empty($div))
            $out .= $this->displayPath();


        // RECUPERATION DES RESULTATS
        if ($id) {
            $sql = $this->request;
            $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
            $_POST = $this->sql->record[0];
            if (!$_POST)
                die();
        }
        //--
        // FORM
        /*
          $submit .="<p class='SubmitP'>";
          if($id) $submit .="<span>Valider les modifications et</span>";
          else $submit .="<span>Valider l'ajout et</span>";
          $submit .="<input onclick=\"$('back_type').value='cmd_back';\" type='submit' name='cmd_back'  id='cmd_back' value='Retourner sur la liste' />";
          $submit .="<input onclick=\"$('back_type').value='cmd_stay';\" type='submit' name='cmd_stay'  id='cmd_stay' value='Rester sur cette page' />";
          if(!$id) $submit .="<input onclick=\"$('back_type').value='cmd_new';\" type='submit' name='cmd_new'  id='cmd_new' value='Ajouter un nouvel enregistrement' />";
          $submit .="</p>";
         */

        //$func = (($displayMceEditor) ? 'tinyMCE.triggerSave();' : '');
        $formID = (($div) ? $div : 'form1');

        $search = "<div id='searchTableList' class='records'>";
        $search .="<table style='border:1px solid #666;border-top:3px solid #666' class='records'><tr><td>";
        $search .="<form onsubmit=\"return false;\" id='" . $formID . "' action='" . $this->link_to('modSQL') . "' name='" . $formID . "' method='post'>";

        $search .="<p class='SubmitP formulaireMod' style='margin-bottom:6px'>";

        if ($id)
            $search .="<span>Valider les modifications et</span>";
        else
            $search .="<span>Valider l'ajout et</span>";

        if (empty($div)) {
            $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_back';\" type='submit' name='cmd_back' value='Retourner sur la liste' />";
            $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_stay';\" type='submit' name='cmd_stay' value='Rester sur cette page' />";
            if (!$id)
                $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_new';\" type='submit' name='cmd_new' value='Ajouter un nouvel enregistrement' />";
            elseif ($this->droits['DUP'])
                $search .="<span> ou </span><input onclick=\"document." . $formID . ".back_type.value='cmd_duplicate';\" type='submit' name='cmd_duplicate' value='Dupliquer' />";
        }
        else
            $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_save_back';\" type='submit' name='cmd_save_back' value='Retourner sur la liste' />";

        $search .="</p>";


        $search .="<input type='hidden' name='back_class' value='" . $this->name_url . "' />";
        $search .="<input type='hidden' name='back_type' value='' />";
        $search .="<input type='hidden' name='back_id' value='" . $id . "' />";

//		$search .="<input type='hidden' name='back_class' id='back_class' value='".$this->name_url."' />";
//		$search .="<input type='hidden' name='back_type' id='back_type' value='' />";
//		$search .="<input type='hidden' name='back_id' id='back_id' value='".$id."' />";
        //$search .="<legend> Informations personnelles </legend>";
        //$search .="<fieldset class='searchList editForm'>";

        for ($i = 0; $i < count($this->forms); $i++) {

            if (isset($_GET[$this->forms[$i]->name]))
                $_POST[$this->forms[$i]->name] = $_GET[$this->forms[$i]->name];

            $this->forms[$i]->setVar('formID', $formID);
            $this->forms[$i]->setVar('curID', $id);

            if ($i == 0 && $this->forms[$i]->getVar('type') != 'group') { //Si aucun group n'est d襩ni dans la class, on en ajoute un par d襡ut
                $form = new BackForm("", "group");
                $search .= $form->displayGroup($i);
                if (isset($_POST[$this->forms[$i]->name]))
                    $search .= $this->forms[$i]->displayForm($_POST[$this->forms[$i]->name], $this);
                else
                    $search .= $this->forms[$i]->displayForm('', $this);
            }
            elseif ($this->forms[$i]->getVar('type') == 'group') { //Si le group est la
                $class = $this->forms[$i]->getGroupOpts('class');

                if (empty($class) || (!empty($class) && !empty($id))) {

                    if (!empty($class)) {
                        $paramAdd['module_page'] = $class;
                        $paramAdd['parent_url'] = $this->name_url;

                        $class = 'b' . ucfirst($class);
                        $obj = new $class($paramAdd);

                        $paramGroup['type'] = 'group'; //csv&co
                        $paramGroup[$this->name_url] = $id;

                        $search .= $this->forms[$i]->displayGroup($i, $obj);
                        $search .= $obj->Listing($paramGroup);
                    }
                    else
                        $search .= $this->forms[$i]->displayGroup($i);
                }
            }
            else {
                $this->forms[$i]->setVar('curID', $id);
                if (isset($_POST[$this->forms[$i]->name]))
                    $search .= $this->forms[$i]->displayForm($_POST[$this->forms[$i]->name], $this);
                else
                    $search .= $this->forms[$i]->displayForm('', $this);
            }

            if ($this->forms[$i]->value != '') {
                $this->urlForms.=$this->forms[$i]->getUrl();
                $this->sqlForms.=$this->forms[$i]->getSql();
            }
        }




        $search .="</fieldset>";

        $search .="<p class='SubmitP formulaireMod'>";

        if ($id)
            $search .="<span>Valider les modifications et</span>";
        else
            $search .="<span>Valider l'ajout et</span>";

        if (empty($div)) {
            $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_back';\" type='submit' name='cmd_back' value='Retourner sur la liste' />";
            $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_stay';\" type='submit' name='cmd_stay' value='Rester sur cette page' />";
            if (!$id)
                $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_new';\" type='submit' name='cmd_new' value='Ajouter un nouvel enregistrement' />";
            elseif ($this->droits['DUP'])
                $search .="<span> ou </span><input onclick=\"document." . $formID . ".back_type.value='cmd_duplicate';\" type='submit' name='cmd_duplicate' value='Dupliquer' />";
        }
        else
            $search .="<input onclick=\"document." . $formID . ".back_type.value='cmd_save_back';\" type='submit' name='cmd_save_back' value='Retourner sur la liste' />";

        $search .="</p>";

        $search .="</form></td></tr></table></div>";
        //--
        //$search .="<p class='SubmitP'><input onclick=\"$('back_type').value='cmd_back';\" type='submit' name='cmd_back'  id='cmd_back' value='Modifier... et Retourner sur le listing' /></p>";
        //$search .="<input type='checkbox' name='cmd_autosave' value='1' /><span>Sauvegarder automatiquement toutes les 5 minutes</span>";

        $addClass = (!empty($_GET['id']) ? "" : "current");
        $modClass = (!empty($_GET['id']) ? "current" : "off");
        $dupClass = (!empty($_GET['id']) ? "off" : "off");
        $delClass = (!empty($_GET['id']) ? "" : "off");


        if (empty($div)) {

            $out .= "<div>";
            $out .= "<div class='table'>";

            $out .= '
			<div>
			  <div id="TabView" style="width:100%;border-bottom:3px solid #666;">
			    <div class="display_control_tabs" >
			      <dl>
			        <dt> Afficher : </dt>
			        <dd>
			          <ul class="tabs">
			            <li class="first" id="tab_0"> <a href="' . $this->link_to('searchList') . '"> Rechercher </a> </li>';
            if ($this->droits['ADD'])
                $out .= '<li class="' . $addClass . '" id="tab_1"> <a href="' . $this->link_to('addForm') . '"> Ajouter </a> </li>';
            else
                $out .= '<li class="off" id="tab_1"> <a href="#"> Ajouter </a> </li>';
            $out .= '
			            <li class="' . $modClass . '" id="tab_2"> <a href="#"> Modifier </a> </li>';

            if ($this->droits['DEL'])
                $out.='<li class="' . $delClass . '" id="tab_4"> <a onclick="delete_item(\'' . $this->link_to('deleteItem') . '\',\'' . (isset($_GET['id']) ? $_GET['id'] : '') . '\')" href="#"> Supprimer </a> </li>';

            else
                $out.='<li class="off" id="tab_4"> <a href="#"> Supprimer </a> </li>';
            $out .= ' </ul>
			        </dd>
			      </dl>
			    </div>
			    <div class="records_view_options">
			    </div>
			    <div class="clear"></div>
			  </div>
			</div>
			';
        }

        $out .= $search;
        $out .= "<script type='text/javascript'>";
        $out .= "	new FormValidator('" . $formID . "', { onFormValidate: validateForm, useTitles: true });";
        //$out .= "	new FormValidator('".$formID."');"; //
        //$out .= "	new Validation('".$formID."', {immediate : true, onFormValidate : validateForm}); // OR new Validation(document.forms[0]);";
        if ($this->div) {
            $out .= "	var myTips = new Tips($$('.infobulle'));";
        }
        //$out .= "	var myTips = new Tips($$('.infobulle'));";
        $out .= "</script>";
        $out .= "</div>";
        $out .= "</div>";
//        $out = \Bydedi\Template\Filter\SSL::getInstance()->replaceHttpProtocol($out);
        return $out;
    }

    function displayContextMenu($id, $div = '') {

        $out = "<div style='width:40px;'>";
        if ($this->droits['MOD']) {
            if (!empty($div)) {
                $out .= "<a class='info_modif' style='height:10px;' href=\"javascript:void(0);\" onclick=\"ajax_form('" . $this->link_to('modForm', array('id' => $id, 'div' => $div)) . "');\"><img src='" . BACK_URL . "styles/images/post.gif' alt='Modifier' /></a>";
            } else {
                $out .= "<a class='info_modif' href=\"javascript:void(0);\" onclick=\"window.location.href='" . $this->link_to('modForm', array('id' => $id)) . "'\"><img src='" . BACK_URL . "styles/images/post.gif' alt='Modifier' /></a>";
            }
        }
        if ($this->droits['DEL']) {
            if (!empty($div)) {
                $out .= "<a class='info_suppr' style='height:10px;' href=\"javascript:void(0);\" onclick=\"delete_item('" . $this->link_to('deleteItem', array('div' => $this->name_url . '_line_' . $id, 'refresh' => $this->name_url)) . "','" . $id . "'); return false;\"><img src='" . BACK_URL . "styles/images/trash.gif' alt='Supprimer' /></a>";
            } else {
                $out .= "<a class='info_suppr' href=\"javascript:void(0);\" onclick=\"delete_item('" . $this->link_to('deleteItem', array('div' => $this->name_url . '_line_' . $id)) . "','" . $id . "'); return false;\"><img src='" . BACK_URL . "styles/images/trash.gif' alt='Supprimer' /></a>";
            }
        }
        $out.="</div>";

        return $out;
    }

    function pageUrl($page) {



        $url = $this->link_to('cleanList');
        $url.="?page=" . $page;
        $url.="&amp;order=" . $this->orderBy;
        $url.=$this->urlForms;
        return $url;
    }

    function orderUrl($order) {
        $url = $this->link_to('cleanList');
        $url.="?page=1";
        $url.="&amp;order=" . $order;
        $url.=$this->urlForms;
        return $url;
    }

    function link_to($item = '', $formvars = array()) {

        $params = $this->getUrl('query');
        if(!empty($params))
            $params = '?' . htmlentities($params);

        switch ($item) {
            case 'addForm':
                if (!empty($formvars)) {
                    foreach ($formvars as $k => $v) {
                        $params .= ( empty($params) ? '?' . $k . '=' . $v : '&amp;' . $k . '=' . $v );
                    }
                }
                $out = '/back/html/' . $this->name_url . '/form/' . $params;
                break;
            case 'modForm':
                if (!empty($formvars['div'])) {
                    $div = 'div=' . $formvars['div'];
                    $params .= ( empty($params) ? '?' . $div : '&amp;' . $div );
                }
                if (empty($formvars['id']))
                    die('no id for modForm');
                $out = '/back/html/' . $this->name_url . '/form/' . $formvars['id'] . '/' . $params;
                break;
            case 'contextMenu':
                if (!empty($formvars['div'])) {
                    $div = 'div=' . $formvars['div'];
                    $params .= ( empty($params) ? '?' . $div : '&amp;' . $div );
                }
                $out = '/back/html/' . $this->name_url . '/context/' . $formvars['id'] . '/' . $params;
                break;
            case 'modSQL':
                $out = '/back/html/' . $this->name_url . '/update/' . $params;
                break;
            case 'searchList':
                $out = '/back/html/' . $this->name_url . '/list/' . $params;
                break;
            case 'cleanList':
                if (!empty($_GET['method'])) {
                    if (!isset($_GET['id']))
                        $_GET['id'] = "";
                    $out = '/back/html/' . $this->name_url . '/custom/' . $_GET['method'] . '/' . $_GET['id'] . '/';
                }
                else
                    $out = '/back/html/' . $this->name_url . '/list/';
                break;
            case 'custom':
                if (empty($formvars))
                    die('no options for custom');
                $out = '/back/html/' . $this->name_url . '/custom/' . $formvars[0] . '/' . $formvars[1] . '/';
                break;
            case 'moduleExport':
                if (empty($formvars))
                    die('no options for custom');
                $out = '/back/html/' . $this->name_url . '/export/' . $formvars[0] . '/' . $params;
                break;
            case 'deleteItem':
                if (!empty($formvars['div'])) {
                    $div = 'div=' . $formvars['div'];
                    $params .= ( empty($params) ? '?' . $div : '&amp;' . $div );
                }
                if (!empty($formvars['refresh'])) {
                    $div = 'refresh=' . $formvars['refresh'];
                    $params .= ( empty($params) ? '?' . $div : '&amp;' . $div );
                }
                $out = '/back/html/' . $this->name_url . '/delete/' . $params;
                break;
            case 'export':
                $out = '/back/html/' . $this->name_url . '/list/' . $formvars['type'] . '/' . $params;
                break;
            default:
                die('no link for <b>' . $item . '</b>');
                break;
        }
        return $out;
    }

    function getValue($table, $champ, $select, $value) {

        $sql = "SELECT " . $champ;
        $sql.=" FROM " . $table;
        $sql.=" WHERE " . $select . " = '" . $value . "'";

        $this->sql->query($sql, SQL_INIT);
        return $this->sql->record[0];
    }

    function isDefault($table, $id) {
        $sql = "SELECT " . $table . "_default FROM " . $table . " WHERE " . $table . "_id = '" . $id . "'";

        $this->sql->query($sql, SQL_INIT);
        return $this->sql->record[0];
    }

    function setDefault($table, $table_base, $id, $default) {
        if (is_null($default))
            $zeID = $id;
        else
            $zeID = BackModule::getValue($table, $table_base . '_id', $table . '_id', $id);


        if ($default == 1) {
            // Si on met spefiquement cette ligne par default, on update tout a 0 et on la met a 1.
            $sql = "UPDATE " . $table . " SET " . $table . "_default = '0' WHERE " . $table_base . "_id = '" . $zeID . "'";
            $this->sql->query($sql);

            $sql = "UPDATE " . $table . " SET " . $table . "_default = '1' WHERE " . $table . "_id = '" . $id . "'";
            $this->sql->query($sql);
            //echo $sql;
        } else {
            // Si default est a 0 ou NULL, alors...
            //On regarde s'il existe une ligne par default
            $sql = "SELECT " . $table . "_id FROM " . $table . " WHERE " . $table_base . "_id = '" . $zeID . "' AND " . $table . "_default = '1'";

            $this->sql->query($sql, SQL_ALL);
            $num_rows = count($this->sql->record);

            if ($num_rows) {
                // Si oui... alors on fait keud
            } else {
                // Si non... on met la premiere ligne par defaut.
                $sql2 = "SELECT " . $table . "_id FROM " . $table . " WHERE " . $table_base . "_id = '" . $zeID . "'";

                $this->sql->query($sql2, SQL_ALL);
                $num_rows2 = count($this->sql->record);
                if ($num_rows2 > 0) {
                    // Si oui... alors on fait keud
                    $sql = "UPDATE " . $table . " SET " . $table . "_default = '1' WHERE " . $table . "_id = '" . $this->sql->record[0][$table . "_id"] . "'";
                    $this->sql->query($sql);
                }
            }
        }

        if (is_null($default)) {
            // $default == NULL (delete)
            $sql = "SELECT " . $table . "_id FROM " . $table . " WHERE " . $table_base . "_id = '" . $id . "'";
            //echo $sql;
        } else {
            // (add/mod)
        }
    }

    function topdf($filename, $name, $options = "") {

        header('Content-Disposition: attachment; filename="' . $name . '.pdf"');
        header("Content-Type: application/pdf; charset=UTF-8");

        flush();
        passthru(HTMLDOC . " -t pdf --quiet --jpeg --webpage " . $options . " " . $filename);
    }

    function displayListGRAPH() {

        if (!$this->droits['READ']) {
            header('Location: /back/');
            exit;
            //exit;
        }
        $out = $this->displayPath();

        // SEARCH
        $isSearch = count($this->forms) > 0;
        if ($isSearch) {

            $js = "";
            $inputSearch = "";
            for ($i = 0; $i < count($this->forms); $i++) {
                if (isset($_GET[$this->forms[$i]->name]))
                    $inputSearch .= $this->forms[$i]->Displayform($_GET[$this->forms[$i]->name], $this);
                else
                    $inputSearch .= $this->forms[$i]->Displayform("", $this);
                if ($this->forms[$i]->value != '') {
                    $this->urlForms.=$this->forms[$i]->getUrl();
                    $this->sqlForms.=$this->forms[$i]->getSql();
                }
            }
        }

        // RECUPERATION DES RESULTATS

        $rows = $this->dataGraph;


        $searchClass = (($isSearch) ? "" : "off");



        $out .= "<div>";
        $out .= "<div class='table'>";
        //$out .= $search;
        // CONSTRUCTION DU GRAPH



        $records = "<table style='margin:0' id='records_top' class='records'>";
        $records .= "<tbody><tr><td class='table_controls'>";

        $records .="<div id='searchGraphList' class='records' style='width:20%;' >";
        $records .="<table style='border:1px solid #666;border-top:0' class='records'><tr><td>";
        $records .="<form id='form1' action='" . $this->getUrl() . "' name='form1' method='get'>";
        $records .="<input type='hidden' name='order' value='" . $this->orderBy . "' />";
        $records .="<table style='width:100%;height:586px;'><tr><td style='border:0'>";
        $records .="<fieldset class='searchList'>";
        $records .= $inputSearch;
        $records .="</fieldset></td>";
        $records .="</tr><tr><td valign='bottom' style='border:none;height:40px;vertical-align:bottom;padding-right:20px;'><p class='SubmitP'><input type='submit' value='Appliquer' /></p></td></tr></table></form></td></tr></table></div>";

        $records .="<div class='box' style='width:80%'>";

        $records .= $this->getGraph(1, "amline", "graph");

        $records .= "</div></td></tr></tbody>";
        $records .= "</table>";
        //-- CONSTRUCTION DE LA PAGINATION

        $out .= $records;


        $out .='<div class="records_view_options" style="height:28px"><dl>
		         <dt> Exporter : </dt>
		        <dd>
		          <ul>';
        $out .='<li class="pie_view_option_CSV"> <a id="f_pie_view_option" href="' . $this->link_to('export', array('type' => 'graphcsv')) . '"class="infobulle" title="Informations::Export du listing"> CSV </a></li>';
        $out .='</ul>
		        </dd>
		      </dl>
		    </div><div class="clear"></div>';

        $out .= "<div><table id='back_results' class='records'>";
        // AFFICHAGE DES EN-TETES
        $out .= "<thead>";
        $out .= "<tr>";
        $nbLabels = 1;
        foreach ($this->labels as $label) {
            $nbLabels++;
            $out .= $label->displayName($this);
        }
        $out .= "</tr>";
        $out .= "</thead>";
        // --
        // AFFICHAGE DES RESULTATS
        $out .= "<tbody>";
        $highlight = 0;

        $sum = array();

        $list = "";

        foreach ($rows as $row) {
            $highlight = abs(1 - ($highlight));

            $class = (($highlight) ? 'highlight' : '');

            $list .= "<tr id='" . $this->name_url . "_line_" . $row[0] . "' class='resultr $class'>";

//			if($this->droits['CONTEXT'] && ($this->droits['MOD'] || $this->droits['DEL']))
            //if($this->droits['CONTEXT'] && $this->droits['MOD'] && $row[0]!='no_context')
            //	$out .= "<td style='width:11px;padding:0.6em 0.3em;'><img style='cursor:pointer' alt='context_menu_btn' onmouseover=\"context_menu('".$this->link_to('contextMenu', array('id' => $row[0]))."',this); return false;\" src='".BACK_URL."styles/images/folder.gif' /></td>";
//if($row[0]=='no_context') $out .= "<td style='width:11px;padding:0.6em 0.3em;'></td>";
            //$out .= "<td style='width:11px;padding:0.6em 0.3em;'><img style='cursor:pointer' alt='context_menu_btn' onclick=\"context_menu('".$row[0]."','".$this->name_url."'); return false;\" src='".BACK_URL."styles/images/folder.gif' /></td>";

            $i = 1;

            foreach ($this->labels as $label) {

                $label->setVar('curID', $row[0]);
                $valOut = $label->displayData($this, $row[$i]);
                $list .= $valOut;
                if (!isset($sum[$i]))
                    $sum[$i] = 0;
                $sum[$i] = $sum[$i] + strip_tags($valOut);
                $i++;
            }

            $list .= "</tr>";
            $list .= "\n";
        }


        $out.='<tr>';
        $out.='<th class="text">CUMUL</th>';
        for ($i = 2; $i <= count($sum); $i++) {
            $out.='<th class="text">' . $sum[$i] . '</th>';
        }
        //<td class="text">320.24</td>
        $out.='</tr>';

        $out.=$list;

        $displayNoRows = ((count($rows) > 0) ? 'display:none;' : 'display:cell;' );

        $out .= "<tr><td id='noresultr' colspan='" . $nbLabels . "' style='" . $displayNoRows . "text-align:center;font-weight:bold;text-align:center;'>Aucun R&eacute;sultat</td></tr>";
        $out .= "</tbody>";
        //-- AFFICHAGE DES RESULTATS
        $out .= "</table></div>";

        $out .= "</div>";
        $out .= "</div>";

        //$out .= "<form id='ajax_form'><input name='ajax_obj' id='ajax_obj' type='text' value='".urlencode(gzencode(serialize($this)))."' /></form>";

        $out .= "<script type='text/javascript'>";
        $out .= $js;
        $out .= "</script>";

        return $out;
    }

    function displayListGRAPHCSV() {

        if (!$this->droits['EXP']) {
            $out = "ERROR 154 !!";
            return $out;
            exit;
        }

        $rows = $this->dataGraph;

        header('Content-Disposition: attachment; filename="export_' . $this->name_url . '_' . date('Y_m_d', time()) . '.csv"');
        header("Content-type: text/csv; charset=UTF-8");
        $out = "";
        foreach ($this->labels as $label) {
            $out .= '"' . utf8_decode($label->name) . '"';
            $out .= ";";
        }
        $out .= "\r\n";

        foreach ($rows as $row) {
            $i = 1;
            foreach ($this->labels as $label) {
                $out .= utf8_decode($row[$i]);
                $out .= ";";
                $i++;
            }
            $out .= "\r\n";
        }

        echo $out;
    }
}