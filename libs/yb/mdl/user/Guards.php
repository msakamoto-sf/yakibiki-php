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
 * requires
 */
require_once('yb/mdl/user/FormBuilder.php');

/**
 * YakiBiki user module Xhwlay guard class
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Guards.php 504 2009-01-06 02:43:56Z msakamoto-sf $
 */
class yb_mdl_user_Guards
{
    // {{{ guard_on_validate()

    function guard_on_validate(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_user_FormBuilder::singleton();
        if ($event == 'onCreate_Confirm') {
            $form->setOpts('mode', 'create');
        }
        $form->setDefaults($virtual);
        $form->accept('post');
        if (!$form->validate($errors)) {
            $renderer->set('validate_errors', $errors);
            return false;
        }
        return true;
    }

    // }}}
    // {{{ guard_on_select_user()

    function guard_on_select_user(&$runner, $event, &$bookmark, $params)
    {
        $user_context = yb_Session::user_context();
        $renderer =& $runner->getRenderer();
        $errors = array();

        $uid = (integer)yb_Var::request('id');
        if ($uid < 1) {
            $errors[] = t("Invalid User ID.", null, 'user');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        if ($event == 'onList_Delete' && $uid == $user_context['id']) {
            $errors[] = 
                t("You can't delete your current login user (ID=%id).", 
                    array('id' => $uid));
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_by_id($uid);
        if (count($users) != 1) {
            $errors[] = t("Invalid User ID.", null, 'user');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $p = (boolean)_YB('disable.user.physical_delete');
        if ('onList_Delete' == $event && $p) {
            $errors[] = t("Sorry, YakiBiki doesn't allow user deletion.", 
                null, 'user');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $bookmark->set('id', $uid);
        // copy user data to virtual record.
        $_user = $users[0];
        unset($_user['password']); // for update form
        $bookmark->set('virtual', $_user);

        return true;
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
