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
 * YakiBiki: category module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 355 2008-09-16 12:53:48Z msakamoto-sf $
 */

require_once('yb/Xhwlay.php');
require_once('yb/smarty/Renderer.php');
// {{{ Bookmark Container and Page Flow (Story) Configurations

$pageFlow = array(
    "story" => array(
        "name" => "category.default",
        "bookmark" => "on",
        ),
    "page" => array(
        "finish" => array(
            "class" => "yb_mdl_category_Default",
            "method" => "finish_page",
            "bookmark" => "last",
            ),
        "*" => array(
            "class" => "yb_mdl_category_Default",
            "method" => "list_page",
            "event" => array(
                "onCreate" => "onValidate_Create",
                "onSave" => "onValidate_Save",
                ),
            ),
        ),
    "event" => array(
        "onCreate" => array(
            "class" => "yb_mdl_category_Default",
            "method" => "on_create",
            "transit" => array(
                "success" => "finish",
                "errorback" => "*",
                ),
            ),
        "onSave" => array(
            "class" => "yb_mdl_category_Default",
            "method" => "on_save",
            "transit" => array(
                "success" => "finish",
                "errorback" => "*",
                ),
            ),
        ),
    "guard" => array(
        "onValidate_Create" => array(
            "class" => "yb_mdl_category_Default",
            "method" => "guard_on_validate_create",
            ),
        "onValidate_Save" => array(
            "class" => "yb_mdl_category_Default",
            "method" => "guard_on_validate_save",
            ),
        ),
    );

// }}}

$ybx =& new yb_Xhwlay();
$ybx->pageFlow = $pageFlow;
$ybx->need_login = true;
$ybx->roles = array('sys', 'category');
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
