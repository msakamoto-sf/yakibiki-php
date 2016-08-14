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
 * requires
 */
require_once('yb/tx/user/Create.php');
require_once('yb/mdl/user/FormBuilder.php');

/**
 * YakiBiki user module create page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Create.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_user_Create
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        if (!$virtual) {
            $virtual = array(
                "name" => "",
                "mail" => "",
                "password" => "",
                "status" => YB_USER_STATUS_OK,
                "role" => array(),
            );
            $bookmark->set('virtual', $virtual);
        }

        $form =& yb_mdl_user_FormBuilder::singleton();
        $form->setOpts('mode', 'create');
        $form->setDefaults($virtual);
        $form_datas = $form->build();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('User Create'));
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/user/create_input_tpl.html";
    }

    // }}}
    // {{{ on_confirm()

    function on_confirm(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_user_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form->accept('post');
        $virtual = $form->export();
        $bookmark->set('virtual', $virtual);

        // {{{ check user-name or mail-address duplication

        $errors = $renderer->get('validate_errors');
        $dao_user =& yb_dao_Factory::get('user');
        $all_users = $dao_user->find_all();
        foreach ($all_users as $u) {
            $_n = $u['name'];
            $_m = $u['mail'];
            if ($_n == $virtual['name']) {
                $errors[] = 
                    t('Given user name (%name) has been already used.', 
                        array('name' => $_n));
                $renderer->set('validate_errors', $errors);
                return "errorback";
            }
            if ($_m == $virtual['mail']) {
                $errors[] = 
                    t('Given mail address (%mail) has been already used.', 
                        array('mail' => $_m));
                $renderer->set('validate_errors', $errors);
                return "errorback";
            }
        }

        // }}}

        return "success";
    }

    // }}}
    // {{{ confirm_page()

    function confirm_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $form =& yb_mdl_user_FormBuilder::singleton();
        $form->setOpts('mode', 'create');
        $form->setDefaults($virtual);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('User Create') . ' (' . t('Confirm') . ')');
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/user/create_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        unset($virtual['change_password']);

        $result = yb_tx_user_Create::go($virtual);

        $log =& yb_Log::get_logger();
        $log->info(sprintf("user create success: id=%d, name=%s",
            $result['id'], $result['name']));

        $bookmark->set('virtual', $result);
        $bookmark->set('id', $result['id']);
        return "success";
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $id = $bookmark->get('id');
        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_by_id($id);

        $form =& yb_mdl_user_FormBuilder::singleton();
        $form->setDefaults($users[0]);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('User Create') . ' (' . t('Complete') . ')');
        $renderer->set('virtual', $users[0]);
        $renderer->set('form', $form_datas);

        return "theme:modules/user/create_finish_tpl.html";
    }

    // }}}
}


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
