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
 * YakiBiki: template module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 358 2008-09-19 10:40:06Z msakamoto-sf $
 */

require_once('yb/Xhwlay.php');
require_once('yb/smarty/Renderer.php');
// {{{ Bookmark Container and Page Flow (Story) Configurations

$pageFlow = array(
    'story' => array(
        'name' => 'template.default',
        'bookmark' => 'on',
        ),
    'page' => array(
        // {{{ create page
        'create_input' => array(
            'class' => 'yb_mdl_template_Create',
            'method' => 'input_page',
            'event' => array(
                'onBackToList' => null,
                'onCreate_Confirm' => 'onValidate',
                ),
            ),
        'create_confirm' => array(
            'class' => 'yb_mdl_template_Create',
            'method' => 'confirm_page',
            'event' => array(
                'onBackToList' => null,
                'onCreate_Input' => null,
                'onCreate_Save' => null,
                ),
            ),
        'create_finish' => array(
            'class' => 'yb_mdl_template_Create',
            'method' => 'finish_page',
            'bookmark' => 'last',
            ),
        // }}}
        // {{{ update page
        'update_input' => array(
            'class' => 'yb_mdl_template_Update',
            'method' => 'input_page',
            'event' => array(
                'onBackToList' => null,
                'onUpdate_Confirm' => 'onValidate',
                ),
            ),
        'update_confirm' => array(
            'class' => 'yb_mdl_template_Update',
            'method' => 'confirm_page',
            'event' => array(
                'onBackToList' => null,
                'onUpdate_Input' => null,
                'onUpdate_Save' => null,
                ),
            ),
        'update_finish' => array(
            'class' => 'yb_mdl_template_Update',
            'method' => 'finish_page',
            'bookmark' => 'last',
            ),
        // }}}
        // {{{ delete page
        'delete_confirm' => array(
            'class' => 'yb_mdl_template_Delete',
            'method' => 'confirm_page',
            'event' => array(
                'onBackToList' => null,
                'onDelete_Save' => null,
                ),
            ),
        'delete_finish' => array(
            'class' => 'yb_mdl_template_Delete',
            'method' => 'finish_page',
            'bookmark' => 'last',
            ),
        // }}}
        // {{{ (default list page)
        '*' => array(
            'class' => 'yb_mdl_template_List',
            'method' => 'list_page',
            'event' => array(
                'onList_Create' => 'onSelectTemplateDataType',
                'onList_Update' => 'onSelectTemplate',
                'onList_Delete' => 'onSelectTemplate',
                ),
            ),
        // }}}
        ),
    'event' => array(
        // {{{ create event
        'onCreate_Confirm' => array(
            'class' => 'yb_mdl_template_Create',
            'method' => 'on_confirm',
            'transit' => array(
                'success' => 'create_confirm',
                'errorback' => 'create_input',
                ),
            ),
        'onCreate_Input' => array(
            'class' => 'yb_Xhwlay',
            'method' => 'onAlwaysSuccessEvent',
            'transit' => array(
                'success' => 'create_input'
                ),
            ),
        'onCreate_Save' => array(
            'class' => 'yb_mdl_template_Create',
            'method' => 'on_save',
            'transit' => array(
                'success' => 'create_finish'
                ),
            ),
        // }}}
        // {{{ update event
        'onUpdate_Confirm' => array(
            'class' => 'yb_mdl_template_Update',
            'method' => 'on_confirm',
            'transit' => array(
                'success' => 'update_confirm',
                'errorback' => 'update_input',
                ),
            ),
        'onUpdate_Input' => array(
            'class' => 'yb_Xhwlay',
            'method' => 'onAlwaysSuccessEvent',
            'transit' => array(
                'success' => 'update_input'
                ),
            ),
        'onUpdate_Save' => array(
            'class' => 'yb_mdl_template_Update',
            'method' => 'on_save',
            'transit' => array(
                'success' => 'update_finish'
                ),
            ),
        // }}}
        // {{{ delete event
        'onDelete_Save' => array(
            'class' => 'yb_mdl_template_Delete',
            'method' => 'on_save',
            'transit' => array(
                'success' => 'delete_finish'
                ),
            ),
        // }}}
        // {{{ (default list event)
        'onBackToList' => array(
            'class' => 'yb_mdl_template_List',
            'method' => 'on_backtolist',
            'transit' => array(
                'success' => '*'
                ),
            ),
        'onList_Create' => array(
            'class' => 'yb_Xhwlay',
            'method' => 'onAlwaysSuccessEvent',
            'transit' => array(
                'success' => 'create_input'
                ),
            ),
        'onList_Update' => array(
            'class' => 'yb_Xhwlay',
            'method' => 'onAlwaysSuccessEvent',
            'transit' => array(
                'success' => 'update_input'
                ),
            ),
        'onList_Delete' => array(
            'class' => 'yb_Xhwlay',
            'method' => 'onAlwaysSuccessEvent',
            'transit' => array(
                'success' => 'delete_confirm'
                ),
            ),
        // }}}
        ),
    'guard' => array(
        'onSelectTemplateDataType' => array(
            'class' => 'yb_mdl_template_Guards',
            'method' => 'guard_on_select_template_datatype',
            ),
        'onSelectTemplate' => array(
            'class' => 'yb_mdl_template_Guards',
            'method' => 'guard_on_select_template',
            ),
        ),
    );

// }}}

$ybx =& new yb_Xhwlay();
$ybx->pageFlow = $pageFlow;
$ybx->need_login = true;
$ybx->roles = array('sys', 'template');
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
