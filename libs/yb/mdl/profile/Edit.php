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
require_once('yb/tx/user/Update.php');
require_once('yb/mdl/profile/FormBuilder.php');

/**
 * YakiBiki profile edit module update page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Edit.php 511 2009-01-06 07:11:30Z msakamoto-sf $
 */
class yb_mdl_profile_Edit
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $uc = yb_Session::user_context();
        $virtual = $bookmark->get('virtual');
        if (!$virtual) {
            $dao_user =& yb_dao_Factory::get('user');
            $users = $dao_user->find_by_id($uc['id']);
            $virtual = $users[0];
            unset($virtual['password']);
            $bookmark->set('virtual', $virtual);
        }

        $form =& yb_mdl_profile_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->build();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Edit User Profile'));
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/profile/edit_input_tpl.html";
    }

    // }}}
    // {{{ on_confirm()

    function on_confirm(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_profile_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form->accept('post');
        $virtual = $form->export();
        unset($virtual['old_password']);
        unset($virtual['password2']);
        $bookmark->set('virtual', $virtual);

        // {{{ check user-name or mail-address duplication

        $errors = $renderer->get('validate_errors');
        $dao_user =& yb_dao_Factory::get('user');
        $all_users = $dao_user->find_all();
        foreach ($all_users as $u) {
            $_id = $u['id'];
            $_n = $u['name'];
            $_m = $u['mail'];
            if ($_n == $virtual['name'] && $_id != $virtual['id']) {
                $errors[] = 
                    t('Given user name (%name) has been already used.', 
                        array('name' => $_n));
                $renderer->set('validate_errors', $errors);
                return "errorback";
            }
            if ($_m == $virtual['mail'] && $_id != $virtual['id']) {
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

        $form =& yb_mdl_profile_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Edit User Profile') . ' (' . t('Confirm') . ')');
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/profile/edit_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $uc = yb_Session::user_context();
        $virtual = $bookmark->get('virtual');

        if (empty($virtual['change_password'])) {
            unset($virtual['password']);
        }

        yb_tx_user_Update::go($uc['id'], $virtual);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf('profile update success: id=%d', $uc['id']));

        return "success";
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $uc = yb_Session::user_context();
        $id = $uc['id'];
        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_by_id($id);
        // update session user context.
        yb_Session::user_context($users[0]);

        $form =& yb_mdl_profile_FormBuilder::singleton();
        $form->setDefaults($users[0]);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Edit User Profile') . ' (' . t('Complete') . ')');
        $renderer->set('virtual', $users[0]);
        $renderer->set('form', $form_datas);

        return "theme:modules/profile/edit_finish_tpl.html";
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
