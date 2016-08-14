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
 */

/**
 * YakiBiki: acl module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 357 2008-09-18 04:43:46Z msakamoto-sf $
 */

require_once('yb/Xhwlay.php');
require_once('yb/smarty/Renderer.php');
// {{{ Bookmark Container and Page Flow (Story) Configurations

$pageFlow = array(
    "story" => array(
        "name" => "acl.default",
        "bookmark" => "on",
        ),
    "page" => array(
        // {{{ user manager page
        "user_manager" => array(
            "class" => "yb_mdl_acl_UserManager",
            "method" => "list_page",
            "event" => array(
                "onUserManager_BackTo" => null,
                "onUserManager_Update" => null,
                "onUserManager_Add" => "onSelectUser",
                "onUserManager_Remove" => "onSelectUser",
                ),
            ),
        // }}}
        // {{{ group manager page
        "group_manager" => array(
            "class" => "yb_mdl_acl_GroupManager",
            "method" => "list_page",
            "event" => array(
                "onGroupManager_BackTo" => null,
                "onGroupManager_Update" => null,
                "onGroupManager_Add" => "onSelectGroup",
                "onGroupManager_Remove" => "onSelectGroup",
                ),
            ),
        // }}}
        // {{{ create page
        "create_input" => array(
            "class" => "yb_mdl_acl_Create",
            "method" => "input_page",
            "event" => array(
                "onBackToList" => null,
                "onCreate_UserManager" => null,
                "onCreate_GroupManager" => null,
                "onCreate_Confirm" => "onValidate",
                "onCreate_TemporarySave" => "onValidate",
                ),
            ),
        "create_confirm" => array(
            "class" => "yb_mdl_acl_Create",
            "method" => "confirm_page",
            "event" => array(
                "onBackToList" => null,
                "onCreate_Input" => null,
                "onCreate_Save" => null,
                ),
            ),
        "create_finish" => array(
            "class" => "yb_mdl_acl_Create",
            "method" => "finish_page",
            "bookmark" => "last",
            ),
        // }}}
        // {{{ update page
        "update_input" => array(
            "class" => "yb_mdl_acl_Update",
            "method" => "input_page",
            "event" => array(
                "onBackToList" => null,
                "onUpdate_UserManager" => null,
                "onUpdate_GroupManager" => null,
                "onUpdate_Confirm" => "onValidate",
                "onUpdate_TemporarySave" => "onValidate",
                ),
            ),
        "update_confirm" => array(
            "class" => "yb_mdl_acl_Update",
            "method" => "confirm_page",
            "event" => array(
                "onBackToList" => null,
                "onUpdate_Input" => null,
                "onUpdate_Save" => null,
                ),
            ),
        "update_finish" => array(
            "class" => "yb_mdl_acl_Update",
            "method" => "finish_page",
            "bookmark" => "last",
            ),
        // }}}
        // {{{ delete page
        "delete_confirm" => array(
            "class" => "yb_mdl_acl_Delete",
            "method" => "confirm_page",
            "event" => array(
                "onBackToList" => null,
                "onDelete_Save" => null,
                ),
            ),
        "delete_finish" => array(
            "class" => "yb_mdl_acl_Delete",
            "method" => "finish_page",
            "bookmark" => "last",
            ),
        // }}}
        // {{{ (default list page)
        "*" => array(
            "class" => "yb_mdl_acl_List",
            "method" => "list_page",
            "event" => array(
                "onList_Create" => null,
                "onList_Update" => "onSelectAcl",
                "onList_Delete" => "onSelectAcl",
                ),
            ),
        // }}}
        ),
    "event" => array(
        // {{{ user manager event
        "onUserManager_Add" => array(
            "class" => "yb_mdl_acl_UserManager",
            "method" => "on_add",
            "transit" => array(
                "success" => "user_manager",
                ),
            ),
        "onUserManager_Remove" => array(
            "class" => "yb_mdl_acl_UserManager",
            "method" => "on_remove",
            "transit" => array(
                "success" => "user_manager"
                ),
            ),
        "onUserManager_Update" => array(
            "class" => "yb_mdl_acl_UserManager",
            "method" => "on_update",
            "transit" => array(
                "success" => "user_manager"
                ),
            ),
        "onUserManager_BackTo" => array(
            "class" => "yb_mdl_acl_UserManager",
            "method" => "on_backto",
            "transit" => array(
                "to_create" => "create_input",
                "to_update" => "update_input",
                ),
            ),
        // }}}
        // {{{ group manager event
        "onGroupManager_Add" => array(
            "class" => "yb_mdl_acl_GroupManager",
            "method" => "on_add",
            "transit" => array(
                "success" => "group_manager",
                ),
            ),
        "onGroupManager_Remove" => array(
            "class" => "yb_mdl_acl_GroupManager",
            "method" => "on_remove",
            "transit" => array(
                "success" => "group_manager"
                ),
            ),
        "onGroupManager_Update" => array(
            "class" => "yb_mdl_acl_GroupManager",
            "method" => "on_update",
            "transit" => array(
                "success" => "group_manager"
                ),
            ),
        "onGroupManager_BackTo" => array(
            "class" => "yb_mdl_acl_GroupManager",
            "method" => "on_backto",
            "transit" => array(
                "to_create" => "create_input",
                "to_update" => "update_input",
                ),
            ),
        // }}}
        // {{{ create event
        "onCreate_TemporarySave" => array(
            "class" => "yb_mdl_acl_Create",
            "method" => "on_temporary_save",
            "transit" => array(
                "success" => "create_input",
                ),
            ),
        "onCreate_Confirm" => array(
            "class" => "yb_mdl_acl_Create",
            "method" => "on_confirm",
            "transit" => array(
                "success" => "create_confirm",
                "errorback" => "create_input",
                ),
            ),
        "onCreate_UserManager" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "user_manager"
                ),
            ),
        "onCreate_GroupManager" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "group_manager"
                ),
            ),
        "onCreate_Input" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "create_input"
                ),
            ),
        "onCreate_Save" => array(
            "class" => "yb_mdl_acl_Create",
            "method" => "on_save",
            "transit" => array(
                "success" => "create_finish"
                ),
            ),
        // }}}
        // {{{ update event
        "onUpdate_TemporarySave" => array(
            "class" => "yb_mdl_acl_Update",
            "method" => "on_temporary_save",
            "transit" => array(
                "success" => "update_input",
                ),
            ),
        "onUpdate_Confirm" => array(
            "class" => "yb_mdl_acl_Update",
            "method" => "on_confirm",
            "transit" => array(
                "success" => "update_confirm",
                "errorback" => "update_input",
                ),
            ),
        "onUpdate_UserManager" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "user_manager"
                ),
            ),
        "onUpdate_GroupManager" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "group_manager"
                ),
            ),
        "onUpdate_Input" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "update_input"
                ),
            ),
        "onUpdate_Save" => array(
            "class" => "yb_mdl_acl_Update",
            "method" => "on_save",
            "transit" => array(
                "success" => "update_finish"
                ),
            ),
        // }}}
        // {{{ delete event
        "onDelete_Save" => array(
            "class" => "yb_mdl_acl_Delete",
            "method" => "on_save",
            "transit" => array(
                "success" => "delete_finish"
                ),
            ),
        // }}}
        // {{{ (default list event)
        "onBackToList" => array(
            "class" => "yb_mdl_acl_List",
            "method" => "on_backtolist",
            "transit" => array(
                "success" => "*"
                ),
            ),
        "onList_Create" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "create_input"
                ),
            ),
        "onList_Update" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "update_input"
                ),
            ),
        "onList_Delete" => array(
            "class" => "yb_Xhwlay",
            "method" => "onAlwaysSuccessEvent",
            "transit" => array(
                "success" => "delete_confirm"
                ),
            ),
        // }}}
        ),
    "guard" => array(
        "onValidate" => array(
            "class" => "yb_mdl_acl_Guards",
            "method" => "guard_on_validate",
            ),
        "onSelectUser" => array(
            "class" => "yb_mdl_acl_Guards",
            "method" => "guard_on_select_user",
            ),
        "onSelectGroup" => array(
            "class" => "yb_mdl_acl_Guards",
            "method" => "guard_on_select_group",
            ),
        "onSelectAcl" => array(
            "class" => "yb_mdl_acl_Guards",
            "method" => "guard_on_select_acl",
            ),
        ),
    );

// }}}

$ybx =& new yb_Xhwlay();
$ybx->pageFlow = $pageFlow;
$ybx->need_login = true;
$ybx->roles = array('sys', 'acl');
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
