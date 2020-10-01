<?php

// PENDING : a revoir

class BackLabel
{

    var $sql = null;
    var $name;
    var $align;
    var $format;
    var $abr;
    var $comment;
    var $option;
    var $param;
    var $valueSql;
    // valeur sql
    var $multi = false;
    var $order = true;
    var $curID;
    //id de la ligne en cours (%ID%
    var $pos;

    function BackLabel($name, $format = '', $abr = '')
    {

        // Instance de la classe Site_SQL
        $this->sql = Site_SQL::getInstance();

        $this->name = $name;
        $this->format = $format;
        $this->abr = $abr;

        switch ($this->format) {
            case "statut":
                $this->align = "center";
                break;
            case "actif":
                $this->align = "center";
                break;
            case "action":
                $this->align = "center";
                break;
            case "translate":
                $this->multi = true;
                $this->order = false;
                break;
            case "image":
                $this->order = false;
                break;
            case "pdf":
                $this->order = false;
                $this->format = '';
                break;
            case "custom":
                $this->order = false;
                break;
            case "custom_v2":
                $this->order = false;
                break;
            case "left":
                $this->align = "left";
                $this->format = '';
                break;
            case "expedition":
                $this->order = false;
                break;
            default:
                break;
        }
    }

    function setVar($var, $value)
    {
        $this->$var = $value;
    }

    function getVar($var)
    {
        return $this->$var;
    }

    function displayName($module)
    {
        $out = "";
        if (!$this->multi || ($this->multi == true && MULTI)) {
            $out .= "<th";


            $width = (($this->name == "ID") ? 'width:30px;' : '');
            $align = (($this->align) ? 'text-align:' . $this->align . '' : '');

            $style = 'style="' . $width . $align . '"';

            if (abs($module->getVar('orderBy')) === $this->pos && $module->getVar('orderBy') < 0)
                $out .= " class='sort desc'";
            elseif (abs($module->getVar('orderBy')) === $this->pos && $module->getVar('orderBy') > 0)
                $out .= " class='sort asc'";
            elseif (!$this->order)
                $out .= " class='text'";

            if ($this->pos == 2 && $module->droits['CONTEXT'] && ($module->droits['MOD'] || $module->droits['DEL']))
                $out .= " colspan='2'";

            $out .= " " . $style . ">";

            if ($this->abr)
                $out .= " <img class='infobulle' title=\"Informations::" . htmlentities($this->name) . "\" style='float:left;cursor:help' src='/back/styles/images/info2.gif' alt='' />";

            if ($this->abr)
                $title = $this->abr;
            else
                $title = $this->name;

            if (!$this->order || !empty($this->valueSql))
                $out .= $title;
            else {
                if ($module->getVar('orderBy'))
                    if (abs($module->getVar('orderBy')) === $this->pos && $module->getVar('orderBy') > 0)
                        $out .= "<a href='" . $module->orderUrl(-$this->pos) . "'>" . $title . "</a>";
                    else
                        $out .= "<a href='" . $module->orderUrl($this->pos) . "'>" . $title . "</a>";
            }

            $out .= "</th>";
        }
        return $out;
    }

    function DisplayData($module, $data)
    {
        $out = "";
        $class = "";

        if (!$this->multi || ($this->multi == true && MULTI)) {

            if (abs($module->getVar('orderBy')) === $this->pos)
                $class .= "sort";
            elseif (!$this->order)
                $class = "text";
            else
                $class = "";


            $align = (($this->align) ? 'style="text-align:' . $this->align . '"' : '');

            $out .= "<td " . $align . " class='" . $class . "'>";

            if (!empty($this->valueSql)) {
                $sql = str_replace('%DATA%', $data, $this->valueSql);
                $sql = str_replace('%ID%', $this->curID, $sql);
                $this->sql->query($sql, SQL_INIT, SQL_INDEX);
                $data = $this->sql->record[0];
            }

            if ($data == '--') {
                $out .= $data;
            } else {
                switch ($this->format) {
                    case "actif":
                        $name_url = (($this->option) ? $this->option : $module->name_url);
                        $out .= "<img class='imgjs' alt='switch_actif' id=\"" . $name_url . "_" . $this->curID . "\" onclick=\"switch_actif('" . $name_url . "','" . $this->curID . "','actif')\" src=\"" . BACK_URL . "styles/images/actif" . $data . ".png\" />";
                        break;
                    case "statut":
                        $name_url = (($this->option) ? $this->option : $module->name_url);
                        $out .= "<img class='imgjs' alt='switch_actif' id=\"" . $name_url . "_" . $this->curID . "\" onclick=\"switch_actif('" . $name_url . "','" . $this->curID . "','" . $name_url . "_statut')\" src=\"" . BACK_URL . "styles/images/actif" . $data . ".png\" />";
                        break;
                    case "statut_v2":
                        $name_url = (($this->option) ? $this->option : $module->name_url);
                        $out .= "<div class='divjs' onclick=\"show_status(this,'" . $name_url . "','" . $this->curID . "','" . $name_url . "_statut')\"><img class='imgjs' alt='switch_statut' id=\"" . $name_url . "_" . $this->curID . "\" src=\"" . $data . "\" /><img class='img_down' src='/back/styles/images/nav_dwn1.gif' alt='' /></div>";
                        break;
                    case "statut_boutique":
                        $name_url = (($this->option) ? $this->option : $module->bases[0]);
                        $out .= "<div class='divjs' onclick=\"show_status_boutique(this,'" . $name_url . "','" . $this->curID . "','" . $name_url . "_statut')\"><img class='imgjs' alt='switch_statut' id=\"" . $name_url . "_" . $this->curID . "\" src=\"" . $data . "\" /><img class='img_down' src='/back/styles/images/nav_dwn1.gif' alt='' /></div>";
                        break;
                    case 'statut_boutique_listing':
                        // TODO : recup toutes les boutiques et pour chaque boutiques remonte le statut de la lignes
                        $boutiques = Boutique::getInstance()->get();
                        foreach ($boutiques as $b) {
                            $name_url = (($this->option) ? $this->option : $module->bases[0]);
                            $sql = "SELECT boutique_statut_image FROM " . $name_url . "_boutique ab LEFT JOIN boutique_statut bs ON(ab." . $name_url . "_boutique_statut_id = bs.boutique_statut_id) WHERE ab." . $name_url . "_id = '" . $this->curID . "' AND ab.boutique_id = '" . $b['boutique_id'] . "'";
                            $this->sql->query($sql);
                            $data = $this->sql->record[0];
                            $out .= "<div>" . $b['boutique_nom'];
                            $out .= "<div class='divjs' onclick=\"show_status_boutique(this,'" . $name_url . "_boutique','" . $this->curID . "','" . $name_url . "_boutique_statut')\"><img class='imgjs' alt='switch_statut' id=\"" . $name_url . "_" . $this->curID . "\" src=\"" . $data . "\" /><img class='img_down' src='/back/styles/images/nav_dwn1.gif' alt='' /></div>";
                            $out .= "</div>";
                        }
                        break;
                    case "default":
                        //$def_status = $module->isDefault($target_table,$data['id']);
                        if ($data)
                            $out .= "<img onclick=\"setDefault('" . $module->name_url . "','" . $module->parent_url . "','" . $this->curID . "','1','" . $module->link_to('searchList') . "')\" style='cursor:pointer' src='" . BACK_URL . "styles/images/bullet_star.png' alt='default' border='0' height='20' width='20' />";
                        else
                            $out .= "<img onclick=\"setDefault('" . $module->name_url . "','" . $module->parent_url . "','" . $this->curID . "','1','" . $module->link_to('searchList') . "')\" style='cursor:pointer' src='" . BACK_URL . "styles/images/bullet_white.png' alt='default' border='0' height='20' width='20' />";
                        break;
                    case "date":
                        if (!empty($data))
                            $out .= date(str_replace('%', '', $this->option[0]), strtotime($data));
                        else
                            $out .= "";
                        break;
                    case "image":
                        //$out .= $data;
                        if (strpos($data, '|') !== false)
                            list($data, $infos) = explode('|', $data);

                        $out .= "<img alt='' src='";
                        $out .= $data;

                        if (empty($infos))
                            $out .= "' />";
                        else
                            $out .= "' class='infobulle' title='Informations::" . $infos . "' />";

                        break;
                    case "devise":
                        setlocale(LC_MONETARY, 'fr_FR');
                        $out .= money_format('%n', $data);
                        //$out .= "<img alt='popupajax' src='/back/styles/images/search.gif' />";
                        break;
                    case "listselect":
                        if (empty($this->option))
                            die('ERROR 256');

                        if (strpos($data, '_') !== false)
                            list($produit_id, $parent_id) = explode('_', $data);
                        else {
                            $produit_id = $data;
                            $parent_id = "";
                        }
                        if (!empty($parent_id))
                            $out .= "<span style='margin-right:3px;'>Voir la suite</span>";
                        else
                            $out .= "<span style='margin-right:3px;'>" . $data . "</span>";

                        foreach ($this->option as $k => $v) {
                            switch ($k) {
                                case 'group':
                                    $out .= "<a href='" . $module->link_to('modForm', array('id' => $this->curID)) . "#group_" . $v . "'><img alt='popupajax' src='/back/styles/images/search.gif' /></a>";
                                    break;
                                case 'module':
                                    $link = str_replace('%ID%', $this->curID, $v);

                                    if (strpos($data, '_') !== false)
                                        list($produit_id, $parent_id) = explode('_', $data);
                                    else {
                                        $produit_id = $data;
                                        $parent_id = "";
                                    }
                                    //list($produit_id, $parent_id) = explode('_',$data);

                                    $link = str_replace('%1%', $produit_id, $link);
                                    $link = str_replace('%2%', $parent_id, $link);

                                    $link = str_replace('%IMG%', "<img alt='popupajax' src='/back/styles/images/last.gif' />", $link);


                                    if (!empty($data) || $this->param['link'])
                                        $out .= $link;
                                    break;
                                default:
                                    die('ERROR 257');
                            }
                        }
                        //$out .= "<span><a href='#'>".$data."</a></span>";
                        //$out.= "<img alt='popupajax' src='/back/styles/images/search.gif' />";
                        break;
                    case "mail":
                        //$out.= "<img alt='popupajax' src='/back/styles/images/mail.gif' />";
                        $out .= "<a href='mailto:" . $data . "'>";
                        $out .= $data;
                        $out .= "</a>";
                        break;
                    case "custom":
                        $this->option[1] = $this->curID;
                        $out .= "<span><a onclick=\"ajax_form('" . $module->link_to('custom', $this->option) . "'); return false;\" href='#'>" . $data . "</a></span>";
                        $out .= "<a onclick=\"ajax_form('" . $module->link_to('custom', $this->option) . "'); return false;\" href='#'>";
                        $out .= "<img alt='popupajax' src='/back/styles/images/comment.gif' />";
                        $out .= "</a>";
                        break;
                    case "custom_v2":
                        $this->option[1] = $this->curID;
                        $out .= "<a href='" . $module->link_to('custom', $this->option) . "'>";
                        $out .= "<img alt='popupajax' src='/back/styles/images/comment.gif' />";
                        $out .= "</a>";
                        break;
                    case "custom_v3":
                        $this->option[1] = $this->curID;
                        $out .= "<span><a onclick=\"ajax_form('" . $module->link_to('custom', $this->option) . "'); return false;\" href='#'></a></span>";
                        $out .= "<a onclick=\"ajax_form('" . $module->link_to('custom', $this->option) . "'); return false;\" href='#'>";
                        $out .= "<img alt='popupajax' src='" . $data . "' />";
                        $out .= "</a>";
                        break;
                    case "translate":
                        $sql = " SELECT l.langue_ini, l.langue_id, l.langue_img, x." . $module->getVar('name_url') . "_nom_id FROM langue l";
                        $sql .= " LEFT JOIN " . $module->getVar('name_url') . "_nom x ON (x.langue_id = l.langue_id AND " . $module->getVar('name_url') . "_id = '" . $this->curID . "')";
                        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
                        $arrLangue = $this->sql->record;
                        //echo $sql;
                        //print_r($arrLangue);
                        foreach ($arrLangue as $tmpLangue) {
                            $class = (($tmpLangue[$module->getVar('name_url') . '_nom_id']) ? 'img_ok' : 'img_flou');
                            $out .= "<img class='" . $class . "' alt='trad_" . $tmpLangue['langue_img'] . "' src='" . $tmpLangue['langue_img'] . "' />&nbsp;&nbsp;";
                        }
                        break;
                    case "eip":
                        $out .= "<p class='editable " . $module->getVar('name_url') . "-----" . $this->option[0] . "-----" . $this->curID . "'>";
                        $out .= $data;
                        $out .= "</p>";
                        break;
                    case "action":
                        $out .= '<a href="#null" onclick="ajax_form(\'' . $this->url . $this->curID . '/\')">' . $this->texte . '</a>';
                        break;


                    case "cmdfrs_qte_cmd":

                        $out.="<input type='text' size='4' value='".$data."' name='' style='text-align:center' onchange='changeCmdFrsQteCmd(this.value, ".$this->curID.")' />";

                        break;

                    case "cmdfrs_qte_recu":

                        $out.="<input type='text' size='4' value='".$data."' name='' style='text-align:center' onchange='changeCmdFrsQteRecu(this.value, ".$this->curID.")' />";

                        break;

                    case "expedition":
                        $expedition_id = $data;
                        $sql = " SELECT * FROM commande_expedition";
                        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
                        $arrExpedition = $this->sql->record;

                        // on peut plus modifier cette valeur si on a une commande fournisseur associé
                        $sql = " SELECT commande_fournisseur_id, commande_parent_id FROM commande WHERE commande_id = '".$this->curID."'";
                        $this->sql->query($sql, SQL_INIT, SQL_ASSOC);
                        $zeCmd = $this->sql->record;

                        if(!empty($zeCmd['commande_parent_id'])) {


                            $out .= "<select id='commande_expedition' name='commande_expedition' onchange='changeCommandeExpedition(this.value, $this->curID)'";

                            if (!empty($zeCmd['commande_fournisseur_id'])) {
                                $out .= " disabled='disabled'";
                            }

                            $out .= ">";
                            if (empty($expedition_id)) {
                                $out .= "<option value='null' selected='selected'>---</option>";
                            } else {
                                $out .= "<option value='null'>---</option>";
                            }
                            foreach ($arrExpedition as $e) {
                                if ($e['commande_expedition_id'] == $expedition_id) {
                                    $out .= "<option value=" . $e['commande_expedition_id'] . " selected='selected'>" . $e['commande_expedition_nom'] . " </option>";
                                } else {
                                    $out .= "<option value=" . $e['commande_expedition_id'] . ">" . $e['commande_expedition_nom'] . " </option>";
                                }
                            }
                            $out .= "</select>";
                        }
                        break;

                    case "livraison":
                        $livraison_nom = $data;
                        $sql = " SELECT * FROM livraison l LEFT JOIN livraison_nom ln ON ln.livraison_id = l.livraison_id WHERE l.livraison_statut_id = 1";
                        $this->sql->query($sql, SQL_ALL, SQL_ASSOC);
                        $arrLivraison = $this->sql->record;
                        $out .= "<select id='commande_livraison' name='commande_livraison' onchange='changeCommandeLivraison(this.value, $this->curID)'>";
                        foreach ($arrLivraison as $l) {
                            if ($l['livraison_nom_nom'] == $livraison_nom) {
                                $out .= "<option value=" . $l['livraison_id'] . " selected='selected'>" . $l['livraison_nom_nom'] . " </option>";
                            } else {
                                $out .= "<option value=" . $l['livraison_id'] . ">" . $l['livraison_nom_nom'] . " </option>";
                            }
                        }
                        $out .= "</select>";
                        break;

                    default:
                        if (empty($this->format))
                            $out .= $data;
                        else {
                            $link = str_replace('%ID%', $this->curID, $this->format);
                            $link = str_replace('%1%', $data, $link);
                            $out .= "<span>" . $link . "</span>";
                            $out .= "<img alt='popupajax' src='/back/styles/images/last.gif' />";
                        }
                        break;
                }
            }
            $out .= "</td>";
        }
        return $out;
    }
}