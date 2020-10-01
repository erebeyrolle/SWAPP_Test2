<?php

// PENDING : a revoir

class BackForm {

    var $sql = null;
    var $title;
    var $type;
    var $name;
    var $comment;
    var $pos;
    var $compare;
    var $value;
    var $format;
    var $data;
    var $parent;
    var $curID;
    var $formID;
    var $champs;
    var $readonly = false;
    var $translate = false;
    //si le champs est multi langue ("traduisible")
    var $multi = false;
    //si le champs doit s'afficher ou non en fonction du __LANGUE__ (1 = oui, sinon non)
    var $multiple = false;
    //si le champs select est � selection multiple
    var $tinyMCE = false;
    var $options = array();
    var $attrs = array();
    var $mceOpts = array();
    var $groupOpts = array();
    var $param;
    var $method; //une methode qui peut �tre appel�e en ajax
//	function BackForm($title, $type, $name='') {

    function __sleep() {

        $this->sql = null;
        $array = get_object_vars($this);

        $vars = array();
        foreach ($array as $k => $v) {
            array_push($vars, $k);
        }

        return $vars;
    }

    function __wakeup() {
        $this->sql = Site_SQL::getInstance();
    }

    function BackForm() {

        $this->sql = Site_SQL::getInstance();

        $numargs = func_num_args();



        if ($numargs >= 2) {
            $arg_list = func_get_args();

            $this->title = $arg_list[0];
            $this->type = $arg_list[1];
            if (isset($arg_list[2]))
                $this->name = str_replace('.', '*', $arg_list[2]);
            else
                $this->name = '';
            $this->compare = 'LIKE';
            if (isset($arg_list[3]))
                $this->param = $arg_list[3];

            // Gestion des attributs par d�faut
            switch ($this->type) {
                case "text":
                    $this->addAttr('maxlength', '255');
                    $this->addAttr('size', '30');
                    $this->addAttr('title', 'Le champ ' . $this->title . ' est requis');
                    break;
                case "textarea":
                    $this->addAttr('title', 'Le champ ' . $this->title . ' est requis');
                    $this->addAttr('rows', '10');
                    $this->addAttr('cols', '50');
                    break;
                case "tinymce":
                    $GLOBALS['displayMceEditor'] = true;
                    $this->addAttr('class','mceEditor');
                    $this->addAttr('style', 'width:655px;');
                    $this->addAttr('style', 'height:400px;');
                    $this->addAttr('rows', '20');
                    $this->addAttr('cols', '50');
                    $this->tinyMCE = true;
                    $this->type = 'textarea';
                    break;
                case "autocomplete":
                    $this->compare = 'EQUAL';
                    $this->addAttr('style', 'width:300px');
                    $this->addAttr('title', 'Le champ ' . $this->title . ' est requis');
                    break;
                case "image":
                    $GLOBALS['displayMcImageManager'] = true;
                    $this->addAttr('title', 'Le champ ' . $this->title . ' est requis');
                    $this->addAttr('style', 'width:50%');
                    break;
                case "file":
                    $GLOBALS['displayMcFileManager'] = true;
                    $this->addAttr('title', 'Le champ ' . $this->title . ' est requis');
                    $this->addAttr('style', 'width:50%');
                    break;
                case "old_file":
                    $this->addAttr('title', 'Le champ ' . $this->title . ' est requis');
                    $this->addAttr('style', 'width:50%');
                    break;
                case "date":
                    $this->compare = 'EQUAL';
                    $GLOBALS['displayCalendar'] = true;
                    $GLOBALS['displayMcFileManager'] = true;
                    $GLOBALS['displayMcImageManager'] = true;
                    $this->addAttr('class', 'validate-date-au');
                    $this->addAttr('title', 'Le champ ' . $this->title . ' doit avoir ce format JJ/MM/AAAA exemple: 02/12/1983 pour le 2 D&eacute;cembre 1983');
                    $this->addAttr('maxlength', '10');
                    $this->addAttr('size', '10');
                    $this->format = '%d/%m/%Y';
                    break;
                case "between":
                    $this->compare = 'BETWEEN';
                    $GLOBALS['displayCalendar'] = true;
                    $GLOBALS['displayMcFileManager'] = true;
                    $GLOBALS['displayMcImageManager'] = true;
                    $this->addAttr('class', 'validate-date-au');
                    $this->addAttr('title', 'Le champ ' . $this->title . ' doit avoir ce format JJ/MM/AAAA exemple: 02/12/1983 pour le 2 D&eacute;cembre 1983');
                    $this->addAttr('maxlength', '10');
                    $this->addAttr('size', '10');
                    $this->format = '%d/%m/%Y';
                    break;
                case "datetime":
                    $GLOBALS['displayCalendar'] = true;
                    $this->addAttr('maxlength', '19');
                    $this->addAttr('size', '19');
                    $this->format = '%d/%m/%Y %H:%M';
                    break;
                case "mail";
                    $this->addAttr('title', 'Le champ ' . $this->title . ' doit avoir ce format user@domain.ext');
                    $this->addAttr('class', 'validate-email');
                    $this->type = 'text';
                    break;
                case "group";
                    $this->addGroupOpts('visible', true);
                    break;
                case "select":
                    $this->compare = 'EQUAL';
                    break;
                default:
                    break;
            }
        }
    }

    function setVar($var, $value) {
        //$this->$var = $value;
        if ($var == 'translate' && $value) {
            if (is_string($value) && $value != LANGUE_ISO)
                $this->$var = $value;
            else
                $this->$var = LANGUE_ISO;

            $this->multi = true;
        }
        elseif ($var == 'multiple' && $value) {
            $this->multiple = true;
            $this->addAttr('multiple', 'multiple');
        }
        else
            $this->$var = $value;
    }

    function getVar($var) {
        return $this->$var;
    }

    function addOption($value, $name, $level=0, $selected=true, $pos=false, $style=false) {
        if ($pos == false)
            array_push($this->options, array("value" => $value, "name" => $name, "level" => $level, "selected" => $selected, "style" => $style));
        else {
            $begin = array_slice($this->options, 0, $pos + 1);
            $end = array_slice($this->options, $pos + 1);
            $this->options = $begin;
            array_push($this->options, array($value, $name, $level, $selected));
            $this->options = array_merge($this->options, $end);
        }
    }

    function addOptionSQL($listSql, $posSQL=0, $level=0, $id=0) {

        if (!is_array($listSql[0])) {
            //$tmp = array();
            $listSql = array($listSql);
        }

        if (!is_array($listSql[0]))
            $listSql[0] = $listSql;

        if (empty($listSql[$posSQL][1]))
            $listSql[$posSQL][1] = true;

        if ($posSQL == 0) {

            $this->sql->query($listSql[$posSQL][0], SQL_ALL, SQL_INDEX);
            $rows = $this->sql->record;
            foreach ($rows as $row) {
                $this->addOption($row[0], clean_encode($row[1]), $level, $listSql[$posSQL][1]);
                $row[0] = (isset($row[2]) ? $row[2] : $row[0]);
                $this->addOptionSQL($listSql, $posSQL + 1, $level + 1, $row[0]);
            }
        } elseif (isset($listSql[$posSQL][0])) {
            $sql2 = str_replace('%ID%', $id, $listSql[$posSQL][0]);
            $this->sql->query($sql2, SQL_ALL, SQL_INDEX);
            $rows = $this->sql->record;
            foreach ($rows as $row) {
                $this->addOption($row[0], clean_encode($row[1]), $level, $listSql[$posSQL][1]);

                if (isset($listSql[$posSQL][2]) && $listSql[$posSQL][2] > 0) {

                    $listSql[$posSQL][2]--;
                    $this->addOptionSQL($listSql, $posSQL, $level + 1, $row[0]);
                } elseif (isset($listSql[$posSQL][2]) && $listSql[$posSQL][2] < 0)
                    $this->addOptionSQL($listSql, $posSQL, $level + 1, $row[0]);
                $this->addOptionSQL($listSql, $posSQL + 1, $level + 1, $row[0]);
            }
        }
    }

    function addAttr($name, $value) {
        $exist = false;
        $pos = 0;

        if ($name == 'readonly' && $value == 'readonly')
            $this->readonly = true;

        foreach ($this->attrs as $attr) {
            if ($attr['name'] == $name) {
                $exist = true;
                break;
            }
            $pos++;
        }
        if ($exist == true) {
            $separator = " ";
            if ($name != 'class')
                $separator = ";";

            if ($name == 'id' || $name == 'size')
                $this->attrs[$pos]['value'] = $value;
            else
                $this->attrs[$pos]['value'] = $this->attrs[$pos]['value'] . $separator . $value;
        }
        else
            array_push($this->attrs, array('name' => $name, 'value' => $value));
    }

    function delAttr($name, $value) {
        $exist = false;
        $pos = 0;
        foreach ($this->attrs as $attr) {
            if ($attr['name'] == $name && strpos($attr['value'], $value) !== false) {
                $exist = true;
                break;
            }
            $pos++;
        }
        if ($exist == true) {
            $this->attrs[$pos]['value'] = str_replace($value, '', $this->attrs[$pos]['value']); //=$this->attrs[$pos]['value'].$separator.$value;
//			$separator = " ";
//			if($name != 'class') $separator = ";";
//			
//			$this->attrs[$pos]['value']=$this->attrs[$pos]['value'].$separator.$value;
        }
    }

    function addGroupOpts($name, $value) {
        $this->groupOpts[$name] = $value;
    }

    function addMceOpts($name, $value) {
        array_push($this->mceOpts, array('name' => $name, 'value' => $value));
    }

    function getAttr() {
        $out = "";
        foreach ($this->attrs as $attr) {
            $out.=" " . $attr['name'] . "='" . $attr['value'] . "'";
        }
        return $out;
    }

    function getGroupOpts($name) {
        if (isset($this->groupOpts[$name]))
            return $this->groupOpts[$name];
        else
            return;
    }

    function getMceOpts() {
        $out = "";
        $out.=",{";
        $i = 0;
        foreach ($this->mceOpts as $mceOpt) {
            if ($i > 0)
                $out.=",";
            $out.=$mceOpt['name'] . ":'" . $mceOpt['value'] . "'";
            $i++;
        }
        $out.="}";
        return $out;
    }

    function getOption($value) {
        $out = "";
        foreach ($this->options as $option) {
            if ($option['style'] == 1)
                $option_style = 'color:#cc0000';
            else
                $option_style = '';
            if (is_array($value)) {
                $sel = false;
                foreach ($value as $val) {
                    if ($val == $option['value']) {
                        $sel = true;
                        break;
                    }
                }
                if ($sel == true && $option['selected'] == true)
                    $out.="<option value='" . $option['value'] . "' style='".$option_style."' selected='selected'>";
                else
                    $out.="<option value='" . $option['value'] . "' style='".$option_style."'>";
            }
            else {
                if ($value == $option['value'] && $option['selected'] == true)
                    $out.="<option value='" . $option['value'] . "' style='".$option_style."' selected='selected'>";
                else
                    $out.="<option value='" . $option['value'] . "' style='".$option_style."' >";
            }
            for ($i = 0; $i < $option['level']; $i++) {
                $out.="&nbsp;&nbsp;";
            }
            $out.=$option['name'] . "</option>";
        }
        return $out;
    }

    function displayForm($value, $module) {
        $out = "";

        if ($this->type == 'select_simple') {
            if (!is_array($value))
                $this->value = htmlentities($value, ENT_QUOTES, 'UTF-8');
            else
                $this->value = $value;

            if ($this->multiple == true)
                $out.= "<select name='" . $this->name . "[]'";
            else
                $out.= "<select name='" . $this->name . "'";
            $out.=$this->getAttr();
            $out.= ">";
            if ($this->multiple == true)
                $out.=$this->getOption($value);
            else
                $out.=$this->getOption($this->value);
            $out.="</select>";
        }
        else {
            if (!$this->multi || $this->translate || ($this->multi == true && MULTI)) {
                // on affecte
                if (!is_array($value))
                    $this->value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                else
                    $this->value = $value;


                if ($this->type == 'hidden') {
                    $out.= "<input type='hidden' name='" . $this->name . "' value='" . $this->value . "'";
                    $out.=$this->getAttr();
                    $out.= "/>";
                } elseif ($this->type == 'back_now') {
                    $out.= "<input type='hidden' name='" . $this->name . "' value='" . date("d/m/Y H:i:s") . "'";
                    $out.=$this->getAttr();
                    $out.= "/>";
                } else {
                    if ($this->multi && MULTI && $this->translate && $this->translate != LANGUE_ISO)
                        $out.= "<p class='" . $this->champs . "'>";
                    else
                        $out.= "<p>";

                    $out.= "<label>";
                    if ($this->multi && MULTI && $this->translate && $this->translate == LANGUE_ISO)
                        $out.= "<span>";

                    if ($this->multi && MULTI && $this->translate && $this->translate != LANGUE_ISO)
                        $out.= '&nbsp;';
                    else
                        $out.= $this->title;

                    if ($this->multi && MULTI && $this->translate && $this->translate == LANGUE_ISO)
                        $out.= "</span>";

                    if ($this->comment)
                        $out .= " <img class='infobulle' title=\"Aide::" . htmlentities($this->comment) . "\" style='cursor:help' src='/back/styles/images/info2.gif' alt='' />";

                    if ($this->tinyMCE)
                        $out .= " <a href=\"javascript:toggleEditor('" . $this->name . "');\"><img src='/back/styles/images/tinymce.png' alt='Activer / D&eacute;activer le WYSIWYG' /></a>";

                    $out.= "</label>";

                    $tmpval = $this->value;
                    unset($this->value);

                    if ($this->multi && MULTI && $this->translate && $this->translate == LANGUE_ISO)
                        $out.= "<a name='" . $this->name . "' href='#" . $this->name . "'><img class='img_exp_input' onclick=\"expand_lang(this,'" . $module->name_url . "','" . urlencode(serialize($this)) . "','" . $this->name . "');\" src='" . BACK_URL . "styles/images/exp_expand.gif' alt='show_trads' /></a>";
                    else
                        $out.= "<img src='" . BACK_URL . "styles/images/transparent.gif' height='20' width='20' alt='show_trads' class='img_exp_input' style='cursor:default;'/>";

                    $this->value = $tmpval;

                    if ($this->multi && MULTI && $this->translate)
                        $out.= "<img src='" . SITE_URL . "media/lang/" . $this->translate . ".gif' class='img_lang_input' alt='" . $this->translate . "' />";
                    else
                        $out.= "<img src='" . BACK_URL . "styles/images/transparent.gif' height='11' width='16' alt='show_trads' class='img_exp_input' style='cursor:default;'/>";

                    switch ($this->type) {
                        case "text":
                            $out.= "<input type='text' name='" . $this->name . "' value='" . $this->value . "'";
                            $out.=$this->getAttr();
                            $out.= "/>";
                            break;
                        /* case "autocomplete":
                          @list($ac_data, $ac_id) = explode('|', $this->value);
                          $out.= "<input type='text' id='id_".$this->name."' name='temp_".$this->name."' value='".$ac_data."'";
                          $out.=$this->getAttr();
                          $out.= "/>";
                          $out.= "<input type='text' style='display:none;' id='final_".$this->name."' name='".$this->name."' value='".$ac_id."'";
                          $out.=$this->getAttr();
                          $out.= "/>";

                          $out .= "<script type=\"text/javascript\">";
                          $out .= "

                          window.addEvent('domready', function(){
                          var auto = new AjaxAutoCompleter(
                          'id_".$this->name."',

                          '".BACK_URL."html".$this->method."'
                          );
                          });
                          ";
                          $out .= "</script>";


                          break; */
                        case "autocomplete":
                            //$objComplete = new $this->param($p);
                            if (!empty($this->value)) {
                                $classComplete = "b" . ucfirst($this->param);
                                $objComplete = new $classComplete();
                                $value = $objComplete->getAutocomplete(array('id' => $this->value));
                                list($ac_data, $ac_id) = explode('|', $value);
                            } else {
                                $ac_data = "";
                                $ac_id = "";
                            }
                            $out.= "<input type='text' id='id_" . $this->name . "' name='temp_" . $this->name . "' value='" . $ac_data . "'";
                            $out.=$this->getAttr();
                            $out.= "/>";
                            $out.= "<input type='text' style='display:none;' id='final_" . $this->name . "' name='" . $this->name . "' value='" . $ac_id . "'";
                            $out.=$this->getAttr();
                            $out.= "/> <a href='#null' onclick=\"document.getElementById('final_" . $this->name . "').value='';document.getElementById('id_" . $this->name . "').value='';\"><img src='/back/styles/images/actif2.png' alt='' /></a>";

                            $out.=" | <a href='#null' onclick='if(document.getElementById(\"final_" . $this->name . "\").value!=\"\") javascript:window.open(\"/back/html/" . $this->param . "/form/\"+document.getElementById(\"final_" . $this->name . "\").value+\"/\")'><img src='/back/styles/images/button_edit.png' alt='' /></a>  <a href='/back/html/" . $this->param . "/form/' target='_blank'><img src='/back/styles/images/exp_expandold.png' alt='' /></a>";

                            $out .= "<script type=\"text/javascript\">";
                            $out .= "
							window.addEvent('domready', function(){
							 	var auto = new AjaxAutoCompleter(
							 		'id_" . $this->name . "',
							 		
							 		'" . BACK_URL . "html" . $this->method . "'
							 	);
							}); 
						";
                            $out .= "</script>";


                            break;
                        case "image":
                            $out.= "<input class='fileBTN' type=\"button\" value=\"Parcourir\" onclick=\"javascript:mcImageManager.open('" . $this->formID . "','" . $this->name . "','',''" . $this->getMceOpts() . ");\" />";
                            $out.= "<input type='text' name='" . $this->name . "' value='" . $this->value . "'";
                            $out.=$this->getAttr();
                            $out.= "/>";
                            break;
                        case "file":
                            $out.= "<input class='fileBTN' type=\"button\" value=\"Parcourir\" onclick=\"javascript:mcFileManager.open('" . $this->formID . "','" . $this->name . "','',''" . $this->getMceOpts() . ");\" />";
                            $out.= "<input type='text' name='" . $this->name . "' value='" . $this->value . "'";
                            $out.=$this->getAttr();
                            $out.= "/>";
                            break;
                        case "old_file":
                            $out.= "<input type='file' name='" . $this->name . "' value='" . $this->value . "'";
                            $out.=$this->getAttr();
                            $out.= "/>";
                            break;
                        case "textarea":
                            // on regarde si la valeur contenu dans le textarea contient des tags si c'est le cas on affiche le tinymce
                            $textValue = html_entity_decode($this->value, ENT_COMPAT, 'UTF-8');
                            $textValueNoTags = strip_tags($textValue);

                            if (!empty($this->value) && mb_strlen($textValue) != mb_strlen($textValueNoTags))
                                $this->addAttr('class', 'mceEditor');
                            $out.="<textarea name='" . $this->name . "' id='" . $this->name . "'";
                            $out.=$this->getAttr();
                            $out.=">";
                            $out.=$this->value;
                            $out.="</textarea>";
                            //if($module->div && $this->tinyMCE)
                            if($this->tinyMCE){
                                $out .= "<script type=\"text/javascript\">";
                                $out .= ' var tinyMCEmode = false;
                                                                                            //tinyMCE.idCounter=0;
                                                                    try {
                                                                        if(tinyMCEmode) {
                                                                            tinyMCE.execCommand("mceRemoveControl", false, "'.$this->name.'");
                                                                            tinyMCEmode = false;
                                                                        } else {
                                                                            // test si une instance de l editeur existe deja, si oui, on le supprimer pour le recree, sinon l editeur bug...
                                                                                    if (tinyMCE.getInstanceById("'.$this->name.'")){
                                                                                                                                tinyMCE.remove(tinyMCE.getInstanceById("'.$this->name.'"));
                                                                                                                            }
                                                                            tinyMCE.execCommand("mceAddControl", false, "'.$this->name.'");
                                                                            tinyMCEmode = true;
                                                                        }
                                                                    } catch(e) {
                                                                        //error handling							
                                                                        //alert(e);
                                                                     }

                                                              ';
                                //$out .= "tinyMCE.execCommand('mceAddControl', false, '".$this->name."');";
                                $out .= "</script>";
                            }
                            break;
                        case "select_ss":
                            $this->addOptionSQL(array($this->optionsSpec[1]));
                            $out.= "<select style='float:left;width:185px' id='" . $this->name . "SSS' name='" . $this->name . "SSS'";
                            $out.=$this->getAttr();
                            $out.= ">";
                            $out.=$this->getOption($this->value);
                            $out.="</select>";
                            $out.="<a href='#null' onclick=\"addSS('" . $this->name . "')\"><img src='/back/styles/images/expandivold.png' style='float:left;margin:7px 5px;'></a>";
                            $out.=$this->displaySsElement($this->optionsSpec[0]);
                            break;
                        case "select":
                            if ($this->multiple == true)
                                $out.= "<select name='" . $this->name . "[]'";
                            else
                                $out.= "<select name='" . $this->name . "'";
                            $out.=$this->getAttr();
                            $out.= ">";
                            if ($this->multiple == true)
                                $out.=$this->getOption($value);
                            else
                                $out.=$this->getOption($this->value);
                            $out.="</select>";
                            break;
                        case "date":
                            $out.= "<img id='" . $this->name . "_img' style='margin-right:5px;margin-top:4px;cursor: pointer;' title='Date selector' src='/back/styles/images/calendar.gif' alt='choix_calendar' />";

                            if ((int) $this->value) {
                                if (strpos($this->value, ' ') !== false)
                                    list($day, $dumb) = explode(" ", $this->value);
                                else {
                                    $day = $this->value;
                                    $dumb = "";
                                }
                                // PENDING a verifier @
                                @list($jours, $mois, $an) = explode("-", $day);
                                if (strlen($jours) == 4)
                                    $tmpDate = date('d/m/Y', strtotime($this->value));
                                else
                                    $tmpDate = $this->value;

                                //							$tmpDate = date('d/m/Y',strtotime($this->value));
                                $out.= "<input type='text' value='" . $tmpDate . "' id='" . $this->name . "' name='" . $this->name . "'";
                            }
                            else
                                $out.= "<input type='text' value='' id='" . $this->name . "' name='" . $this->name . "'";

                            $out.=$this->getAttr();
                            $out.= "/>";

                            $out.= '<script type="text/javascript">
											Calendar.setup(
												{
													inputField  : "' . $this->name . '",
													ifFormat    : "' . $this->format . '",
													button			: "' . $this->name . '_img"
												}
											);
											</script>
										';
                            break;


                        case "datetime":
                            $out.= "<img id='" . $this->name . "_img' style='margin-right:5px;margin-top:4px;cursor: pointer;' title='Date selector' src='/back/styles/images/calendar.gif' alt='choix_calendar' />";

                            if ((int) $this->value) {
                                if (strpos($this->value, ' ') !== false)
                                    list($day, $dumb) = explode(" ", $this->value);
                                else {
                                    $day = $this->value;
                                    $dumb = "";
                                }
                                list($jours, $mois, $an) = explode("-", $day);
                                if (strlen($jours) == 4) {
                                    $tmpDate = date('d/m/Y H:i', strtotime($this->value));
                                } else {
                                    $tmpDate = $this->value;
                                }


                                //							$tmpDate = date('d/m/Y',strtotime($this->value));
                                $out.= "<input type='text' value='" . $tmpDate . "' id='" . $this->name . "' name='" . $this->name . "'";
                            }
                            else
                                $out.= "<input type='text' value='' id='" . $this->name . "' name='" . $this->name . "'";

                            $out.=$this->getAttr();
                            $out.= "/>";
                            $out.= '<script type="text/javascript">
											Calendar.setup(
												{
													inputField  : "' . $this->name . '",
													ifFormat    : "' . $this->format . '",
													button			: "' . $this->name . '_img"
												}
											);
											</script>
										';
                            break;

                        case "between":

                            $out.= "<img id='" . $this->name . "_debut_img' style='margin-right:5px;margin-top:4px;cursor: pointer;' title='Date selector' src='/back/styles/images/calendar.gif' alt='choix_calendar' />";

                            if (!empty($value[0]) && (int) $value[0]) {
                                if (strpos($value[0], ' ') !== false)
                                    list($day, $dumb) = explode(" ", $value[0]);
                                else {
                                    $day = $value[0];
                                    $dumb = "";
                                }
                                //list($day , $dumb) = explode(" ",$value[0]);
                                if (strpos($day, '-') !== false)
                                    list($jours, $mois, $an) = explode("-", $day);
                                else {
                                    $jours = $day;
                                    $mois = "";
                                    $an = "";
                                }
                                if (strlen($jours) == 4)
                                    $tmpDate = date('d/m/Y', strtotime($value[0]));
                                else
                                    $tmpDate = $value[0];

                                $out.= "<input type='text' value='" . $tmpDate . "' id='" . $this->name . "_debut' name='" . $this->name . "[]'";
                            }
                            else
                                $out.= "<input type='text' value='' id='" . $this->name . "_debut' name='" . $this->name . "[]'";

                            $out.=$this->getAttr();
                            $out.= "/>";

                            $out.= '<script type="text/javascript">
										Calendar.setup(
											{
												inputField  : "' . $this->name . '_debut",
												ifFormat    : "' . $this->format . '",
												button			: "' . $this->name . '_debut_img"
											}
										);
										</script>
									';


                            $out.= " au <img id='" . $this->name . "_fin_img' style='margin-right:5px;margin-top:4px;cursor: pointer;' title='Date selector' src='/back/styles/images/calendar.gif' alt='choix_calendar' />";

                            if (!empty($value[1]) && (int) $value[1]) {
                                if (strpos($value[1], ' ') !== false)
                                    list($day, $dumb) = explode(" ", $value[1]);
                                else {
                                    $day = $value[1];
                                    $dumb = "";
                                }
                                //list($day , $dumb) = explode(" ",$value[1]);
                                if (strpos($day, '-') !== false)
                                    list($jours, $mois, $an) = explode("-", $day);
                                else {
                                    $jours = $day;
                                    $mois = "";
                                    $an = "";
                                }
                                if (strlen($jours) == 4)
                                    $tmpDate = date('d/m/Y', strtotime($value[1]));
                                else
                                    $tmpDate = $value[1];

//							$tmpDate = date('d/m/Y',strtotime($this->value));
                                $out.= "<input type='text' value='" . $tmpDate . "' id='" . $this->name . "_fin' name='" . $this->name . "[]'";
                            }
                            else
                                $out.= "<input type='text' value='' id='" . $this->name . "_fin' name='" . $this->name . "[]'";

                            $out.=$this->getAttr();
                            $out.= "/>";

                            $out.= '<script type="text/javascript">
										Calendar.setup(
											{
												inputField  : "' . $this->name . '_fin",
												ifFormat    : "' . $this->format . '",
												button			: "' . $this->name . '_fin_img"
											}
										);
										</script>
									';
                            break;
                        case "checkbox":
                            $checked = (($this->value) ? 'checked="checked"' : '');
                            $out.= "<input type=\"checkbox\" name=\"" . $this->name . "\" value='1' " . $checked . " style=\"vertical-align:middle;margin:5px;border: 0px\" />";
                            $out.= "<span style=\"vertical-align:middle;\">" . $this->comment . "</span>";
                            $this->comment = '';
                            break;
                        case "statut":
                            $this->value = $value;
                            $sql = "SELECT a." . $module->name_url . "_statut_id as id, a." . $module->name_url . "_statut_nom as name FROM " . $module->name_url . "_statut a ORDER BY a." . $module->name_url . "_statut_rang";
                            $module->sql->query($sql, SQL_ALL, SQL_ASSOC);
                            $arrStatut = $module->sql->record;
                            //print_r($_GET);
                            foreach ($arrStatut as $statut) {
                                $checked = '';
                                //echo $statut['id'].' = '.$miniValue."<br/>";echo $checked;
                                if (is_array($this->value))
                                    foreach ($this->value as $miniValue) {
                                        if (!$checked)
                                            $checked = (($statut['id'] == $miniValue) ? 'checked="checked"' : '');
                                    }
                                else
                                    foreach ($this->param as $miniValue) {
                                        if (!$checked)
                                            $checked = (($statut['id'] == $miniValue) ? 'checked="checked"' : '');
                                    }
                                $out.= "<input type=\"checkbox\" name=\"" . $this->name . "[]\" id=\"" . $this->name . "_".$statut['id']."\" value='" . $statut['id'] . "' " . $checked . " style=\"vertical-align:middle;margin:5px;border: 0px\" />";
                                $out.= "<label class=\"label-checkbox\" for=\"" . $this->name . "_".$statut['id']."\">" . $statut['name'] . "</label>";
                            }
                            if (!is_array($this->value))
                                $this->value = $this->param;
                            break;
                        case "multi_statut":
                            $this->value = $value;
                            $sql = "SELECT a." . $module->name_url . "_statut_id as id, b." . $module->name_url . "_statut_nom_nom as name FROM " . $module->name_url . "_statut a, " . $module->name_url . "_statut_nom b WHERE a." . $module->name_url . "_statut_id = b." . $module->name_url . "_statut_id AND b.langue_id = '" . LANGUE . "' ORDER BY a." . $module->name_url . "_statut_rang";
                            $module->sql->query($sql, SQL_ALL, SQL_ASSOC);
                            $arrStatut = $module->sql->record;
                            //print_r($_GET);
                            foreach ($arrStatut as $statut) {
                                $checked = '';
                                //echo $statut['id'].' = '.$miniValue."<br/>";echo $checked;
                                if (is_array($this->value))
                                    foreach ($this->value as $miniValue) {
                                        if (!$checked)
                                            $checked = (($statut['id'] == $miniValue) ? 'checked="checked"' : '');
                                    }
                                else
                                    foreach ($this->param as $miniValue) {
                                        if (!$checked)
                                            $checked = (($statut['id'] == $miniValue) ? 'checked="checked"' : '');
                                    }
                                $out.= "<input id=\"statut-multiple-" . $statut['id'] . "\" type=\"checkbox\" name=\"" . $this->name . "[]\" value='" . $statut['id'] . "' " . $checked . " style=\"vertical-align:middle;margin:5px;border: 0px\" />";
                                $out.= "<label class=\"label-multi-statut\" for=\"statut-multiple-" . $statut['id'] . "\">" . $statut['name'] . "</label>";
                            }
                            if (!is_array($this->value))
                                $this->value = $this->param;
                            break;

                        /*                         * *********************** *
                         * Module : CRM -> COMMANDE *
                         * CAS : STATUS							*
                         * ************************ */
                        case "spe_statut":

                            $out.="<div style='float:left;'>";
                            $out.= "<select name=\"" . $this->name . "\" onchange=\"change_statut('" . $this->curID . "',document." . $this->formID . "." . $this->name . "[document." . $this->formID . "." . $this->name . ".selectedIndex].value)\">\n";

                            $module->sql->query($this->param, SQL_ALL);
                            $req = $module->sql->record;

                            foreach($req as $dataSelect) {
                                $selected = ((isset($this->value) && $dataSelect[0] == $this->value) ? 'selected="selected"' : '');
                                $out.= "<option value=\"" . $dataSelect[0] . "\" " . $selected . "> " . $dataSelect[1] . "</option>\n";
                            }
                            $out.= "</select>";


                            $out.= "<span id=\"div_commande_colis\" style=\"display:none;\"><br /><u><strong># Colis :</strong></u><br /><input onkeyup=\"change_statut('" . $this->curID . "',document." . $this->formID . "." . $this->name . "[document." . $this->formID . "." . $this->name . ".selectedIndex].value)\" style=\"padding:5px;width: 600px\" type=\"text\" name=\"commande_colis\" id=\"commande_colis\" /></span>";
                            $out.= "<span id=\"div_commande_motif\" style=\"display:none;\"><br /><u><strong>Motif :</strong></u><br /><textarea onkeyup=\"change_statut('" . $this->curID . "',document." . $this->formID . "." . $this->name . "[document." . $this->formID . "." . $this->name . ".selectedIndex].value)\" rows=\"3\" cols=\"10\" style=\"padding:5px;width: 600px\" name=\"commande_motif\" id=\"commande_motif\" ></textarea></span>";

                            $out.= "<br /><br /><u><strong>Pr&eacute;visualisation du Mail version HTML :</strong></u><br />";
                            $out.= "<div class=\"div_previ\" id=\"" . $this->name . "_txt\" >&nbsp;</div>";

                            $out.= "<script type=\"text/javascript\">";
                            $out.= "	change_statut('" . $this->curID . "',document." . $this->formID . "." . $this->name . "[document." . $this->formID . "." . $this->name . ".selectedIndex].value);";
                            $out.= "</script>";
                            $out.= "</div>";
                            break;
                        case "spe_statut_option":
                            $out.="<div style='float:left;'>";
                            $out.= "<select name=\"" . $this->name . "\" onchange=\"change_statut()\">\n";

                            $module->sql->query($this->param, SQL_ALL);
                            $req = $module->sql->record;

                            foreach($req as $dataSelect) {
                                $selected = ((isset($this->value) && $dataSelect[0] == $this->value) ? 'selected="selected"' : '');
                                $out.= "<option value=\"" . $dataSelect[0] . "\" " . $selected . "> " . $dataSelect[1] . "</option>\n";
                            }
                            $out.= "</select>";

                            $out.= "<div id='div_commande_options'>";
                            $out.= "</div>";

                            $out.= "<br /><br /><u><strong>Pr&eacute;visualisation du Mail version HTML :</strong></u><br />";
                            $out.= "<div class=\"div_previ\" id=\"" . $this->name . "_txt\" >&nbsp;</div>";

                            $out.= "<script type=\"text/javascript\">";
                            $out.= "	change_statut();";
                            $out.= "</script>";
                            $out.= "</div>";
                            break;
                        case "list_checkbox":
                            foreach ($this->sql->record as $r) {
                                if (!empty($this->value)) {
                                    $checked = '';
                                    if (is_array($this->value)) {
                                        foreach ($this->value as $miniValue) {
                                            if (!$checked) {
                                                $checked = (($r[0] == $miniValue) ? 'checked="checked"' : '');
                                            }
                                        }
                                    } else {
                                        foreach ($this->value as $miniValue) {
                                            if (!$checked) {
                                                $checked = (($r[0] == $miniValue) ? 'checked="checked"' : '');
                                            }
                                        }
                                    }
                                }else{
                                    $checked = 'checked="checked"';
                                }
                                $out.= '<input id="' . $this->name . '_' . $r[0] . '" type="checkbox" name="' . $this->name . '[]" value="' . $r[0] . '" ' . $checked . ' style="margin:5px;vertical-align:middle;" />';
                                $out.= '<label for="' . $this->name . '_' . $r[0] . '" class="label-checkbox">' . $r[1] . '</label>';
                            }

                            break;
                        case "content":
                            $content = $this->getVar('content');
                            $out .= '<div style=\'float:left;\'>';
                            foreach ($content as $key => $c) {
                                $out .= '<p><label style="width: 225px;font-weight: 600">%' . strtoupper($key) . '%</label><label style="width: 300px;">' . $c . '</label></p>';
                            }
                            $out .= '</div>';
                            break;
                        default:
                            die('ERROR 155');
                            break;
                    }
                    if ($this->readonly)
                        $out.="<span><img alt='readonly' style='margin:5px;' src='" . BACK_URL . "styles/images/readonly.gif' /></span>";
                    $out.="</p>\n";
                }
            }
        }
        return $out;
    }

    function displayGroup($i, $module = '') {
        $out = "";
        if ($i != 0)
            $out .= "</fieldset>";


        if (!empty($this->title)) {
            $class = $this->getGroupOpts('class');

            $anchor = (($class) ? "<a name='group_" . $class . "'></a>" : '');

            $out .="<div class='expandiv'>" . $anchor . "<img style='float:left;' alt='expand' src='" . BACK_URL . "styles/images/expandiv.png' /> <span style='float:left;' >" . $this->title . "</span>";

            if (!empty($class)) {
                //print_r($this);
                $out .= "<input type='hidden' name='" . $class . "' value='" . serialize($this) . "' id='" . $class . "'/>";
                if ($module->droits['ADD']) {
                    if ($class == 'filleul')
                        $out .= "<a style='float:right;text-decoration:none;' href='#' onclick=\"ajax_form('" . $module->link_to('addForm', array('div' => 'ajax_pop', 'parrain_id' => $this->curID)) . "'); return false;\"> <img src='/back/styles/images/exp_expand.gif' alt='ajouter' /> Ajouter </a>";
                    else
                        $out .= "<a style='float:right;text-decoration:none;' href='#' onclick=\"ajax_form('" . $module->link_to('addForm', array('div' => 'ajax_pop', $this->parent . '_id' => $this->curID)) . "'); return false;\"> <img src='/back/styles/images/exp_expand.gif' alt='ajouter' /> Ajouter </a>";
                }
            }

            $out .= "<div class='clear'></div>";
            $out .= "</div>";
        }
        $fieldStyle = (($this->getGroupOpts('visible') == true) ? '' : 'display:none;');

        if (isset($class))
            $out .="<fieldset style='" . $fieldStyle . "' class='searchList editForm' id='field_" . $class . "'>";
        else
            $out .="<fieldset style='" . $fieldStyle . "' class='searchList editForm'>";

        return $out;
    }

    function getUrl() {
        $out = "";
        if (is_array($this->value)) {
            foreach ($this->value as $value) {
                $out.="&amp;" . $this->name . "[]=" . $value;
            }
            return $out;
        }
        else
            return "&amp;" . $this->name . "=" . $this->value;
    }

    function getSql() {
        //error_log($this->name." > ".$this->compare." > ".print_r($this->value,true));
        $out = "";
        $column = str_replace('*', '.', $this->name);
        if (!is_array($this->value)) {

            if ($this->type == "date" || $this->type == "datetime") {
                if (strpos($this->value, ' ') !== false)
                    list($day, $time) = explode(" ", $this->value);
                else {
                    $day = $this->value;
                    $time = "";
                }

                if (strpos($this->value, '/') !== false)
                    list($jours, $mois, $an) = explode("/", $day);
                else
                    list($an, $mois, $jours) = explode("-", $day);

                $this->value = $an . '-' . $mois . '-' . $jours;
                if (!empty($time))
                    $this->value .= ' ' . $time; //2007-08-04
                //$tab_champ['Field'] = "DATE_FORMAT(".$tab_champ['Field'].",'%d/%m/%Y')";
            }
            $out = "";
            switch ($this->compare) {
                case 'LIKE':
                    $out.=" AND " . $column . " LIKE '%" . addslashes(html_entity_decode($this->value, ENT_QUOTES, 'UTF-8')) . "%'";
                    break;
                case 'EQUAL':
                    if ($this->type == "date")
                        $out.=" AND DATE(" . $column . ") = '" . addslashes(html_entity_decode($this->value, ENT_QUOTES, 'UTF-8')) . "'";
                    elseif ($this->value == 'NULL')
                        $out.=" AND " . $column . " IS NULL";
                    else
                        $out.=" AND " . $column . " = '" . addslashes(html_entity_decode($this->value, ENT_QUOTES, 'UTF-8')) . "'";
                    break;

                default:
            }
        }
        else {
            //error_log($this->compare." ".$this->name);
            switch ($this->compare) {
                case 'EQUAL':
                    $out.=" AND (";
                    $or = false;
                    foreach ($this->value as $data) {
                        if ($or == true)
                            $out.=" OR ";
                        else
                            $or = true;
                        $out.= $column . " = '" . addslashes(html_entity_decode($data, ENT_QUOTES, 'UTF-8')) . "'";
                    }
                    $out.=")";
                    break;
                case 'BETWEEN':
                    if (empty($this->value[0]) && empty($this->value[1]))
                        break;
                    if (empty($this->value[0]))
                        $this->value[0] = date('d/m/Y');
                    if (empty($this->value[1]))
                        $this->value[1] = date('d/m/Y');
                    list($jours0, $mois0, $an0) = explode("/", $this->value[0]);
                    list($jours1, $mois1, $an1) = explode("/", $this->value[1]);

                    $this->value[0] = $an0 . '-' . $mois0 . '-' . $jours0;
                    $this->value[1] = $an1 . '-' . $mois1 . '-' . $jours1;
                    //$tab_champ['Field'] = "DATE_FORMAT(".$tab_champ['Field'].",'%d/%m/%Y')";
                    //}
                    $out.=" AND DATE(" . $column . ") BETWEEN '" . $this->value[0] . "' AND '" . $this->value[1] . "'";

                    break;
                default:
            }
        }
        return $out;
    }

    function displaySsElement($sql) {
        $out = "";
        $this->sql->query($sql, SQL_ALL, SQL_INDEX);
        $rows = $this->sql->record;
        $out.="<ul class='ulssEleme' id='" . $this->name . "SS'>";
        foreach ($rows as $row) {
            $out.="<li id='" . $this->name . $row[0] . "'><a href='#null' onclick=\"deleteSS('" . $this->name . $row[0] . "')\"><img src='/back/styles/images/trash.gif' alt='Delete'></a><span>" . $row[1] . "</span><input type='hidden' name='" . $this->name . "[]' value='" . $row[0] . "'</li>";
        }
        $out.="</ul>";
        return $out;
    }
    function addCategorieRecursive($categorie_id,$niveau=1,$offset="",$param="", $cat_spec= "") {
        $categorie = new Categorie;

        $opts = array();
        $opts['no_boutique'] = true;

        if(empty($categorie_id)) {
            $opts['control_hors_stock'] = (!empty($param));
            $opts['only_parent'] = true;
            if ($cat_spec != -1) {
                $opts['categorie_id'] = $cat_spec;
            }
        }else {
            $niveau++;
            $opts['parent_id'] = $categorie_id;
        }

        $arrCategorie = $categorie->get($opts);

        if(!empty($arrCategorie)) {

            foreach($arrCategorie as $cat) {
                $nom = "";
                if($niveau>0) {
                    for($i=0;$i<=$niveau;$i++) {

                        $nom.="&nbsp;&nbsp;";
                    }
                }
                $cat_parent = $categorie->get(array('categorie_id' => $cat['categorie_id']), 'parent_id');
                $cat_parent_parent = $categorie->get(array('categorie_id' => $cat_parent), 'parent_id');
                if (!empty($cat_parent) && empty($cat_parent_parent)){
                    $style = true;
                    $nom.=$cat["categorie_nom_nom"];
                }else{
                    $style = false;
                    $nom.=$cat['categorie_nom_nom'];
                }
                $this->addOption($cat['categorie_id'], $nom, 0, true, false, $style);
                //if(isset($GLOBALS['curElement']) && $el['site_element_id']==$GLOBALS['curElement']) selected ?
                $this->addCategorieRecursive($cat['categorie_id'], $niveau, $cat['categorie_id']);

            }
        }
    }
}