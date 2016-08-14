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
 * YakiBiki: login module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 99 2007-12-12 09:59:45Z msakamoto-sf $
 */

// {{{ requires

require_once('yb/Xhwlay.php');
require_once('yb/smarty/Renderer.php');

// }}}
// {{{ Bookmark Container and Page Flow (Story) Configurations

$pageFlow = array(
    "story" => array(
        "name" => "login.default",
        "bookmark" => "on",
        ),
    "page" => array(
        "login" => array(
            "class" => "yb_mdl_login_Default",
            "method" => "logined_page",
            "bookmark" => "last",
            ),
        "*" => array(
            "class" => "yb_mdl_login_Default",
            "method" => "input_page",
            "event" => array(
                "onLogin" => "validateLogin",
                ),
            ),
        ),
    "event" => array(
        "onLogin" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "login"
                ),
            ),
        ),
    "guard" => array(
        "validateLogin" => array(
            "class" => "yb_mdl_login_Default",
            "method" => "guard_validateLogin",
            ),
        ),
    );

// }}}

$ybx =& new yb_Xhwlay();
$ybx->pageFlow = $pageFlow;
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
