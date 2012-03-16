<?php
/*
  Concerto Platform - Online Adaptive Testing Platform
  Copyright (C) 2011-2012, The Psychometrics Centre, Cambridge University

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; version 2
  of the License, and not any of the later versions.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!isset($ini))
{
    require_once'../../Ini.php';
    $ini = new Ini();
}
?>

<script>
    $(function(){
        $('#divHiddenThemer').themeswitcher({
            loadTheme:"Cupertino",
            imgpath: "js/lib/themeswitcher/images/",
            onSelect:function(){
            }
        });
        $("#dd_login").dialog({
            modal:true,
            title:"<?= Language::string(211) ?>",
            resizeable:false,
            closeOnEscape:false,
            dialogClass:"no-close",
            open:function(){
                Methods.iniTooltips();
            },
            buttons:{
                "login":function(){ User.uiLogIn(); }
            }
        });
    });
</script>
<div id="dd_login">
    <span><?= Language::string(212) ?></span>
    <div class="padding ui-widget-content ui-corner-all margin">
        <table>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(173) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(260) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="text" id="dd_login_inp_login" class="fullWidth margin ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
            <tr>
                <td class="noWrap horizontalPadding ui-widget-header"><?= Language::string(179) ?>:</td>
                <td><span class="tooltip spanIcon ui-icon ui-icon-help" title="<?= Language::string(261) ?>"></span></td>
                <td class="fullWidth">
                    <div class="horizontalMargin">
                        <input type="password" id="dd_login_inp_password" class="fullWidth margin ui-widget-content ui-corner-all" />
                    </div>
                </td>
            </tr>
        </table>
    </div>	 
</div>

<div id="divHiddenThemer" style="display:none;">
</div>