<?php
/*
 *   Copyright (c) 2007 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

/**
 * YakiBiki: user module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 252 2008-03-30 11:52:06Z msakamoto-sf $
 */

require_once('yb/Xhwlay.php');
require_once('yb/smarty/Renderer.php');
// {{{ Bookmark Container and Page Flow (Story) Configurations

$pageFlow = array(
    "story" => array(
        "name" => "profile.default",
        "bookmark" => "on",
        ),
    "page" => array(
        // {{{ default edit page
        "*" => array(
            "class" => "yb_mdl_profile_Edit",
            "method" => "input_page",
            "event" => array(
                "onEdit_Confirm" => "onValidate",
                ),
            ),
        "edit_confirm" => array(
            "class" => "yb_mdl_profile_Edit",
            "method" => "confirm_page",
            "event" => array(
                "onEdit_Input" => null,
                "onEdit_Save" => null,
                ),
            ),
        "edit_finish" => array(
            "class" => "yb_mdl_profile_Edit",
            "method" => "finish_page",
            "bookmark" => "last",
            ),
        // }}}
        ),
    "event" => array(
        // {{{ default edit event
        "onEdit_Confirm" => array(
            "class" => "yb_mdl_profile_Edit",
            "method" => "on_confirm",
            "transit" => array(
                "success" => "edit_confirm",
                "errorback" => "edit_input",
                ),
            ),
        "onEdit_Input" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "edit_input"
                ),
            ),
        "onEdit_Save" => array(
            "class" => "yb_mdl_profile_Edit",
            "method" => "on_save",
            "transit" => array(
                "success" => "edit_finish"
                ),
            ),
        // }}}
        ),
    "guard" => array(
        "onValidate" => array(
            "class" => "yb_mdl_profile_Guards",
            "method" => "guard_on_validate",
            ),
        ),
    );

// }}}

$ybx =& new yb_Xhwlay();
$ybx->pageFlow = $pageFlow;
$ybx->need_login = true;
echo $ybx->run();

/**
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 * vim: set expandtab tabstop=4 shiftwidth=4:
 */
?>
