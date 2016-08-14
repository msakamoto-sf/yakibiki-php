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
require_once('yb/tx/acl/Finder.php');
require_once('yb/mdl/acl/FormBuilder.php');

/**
 * YakiBiki acl module Xhwlay guard class
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Guards.php 501 2009-01-05 08:46:22Z msakamoto-sf $
 */
class yb_mdl_acl_Guards
{
    // {{{ guard_on_validate()

    function guard_on_validate(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_acl_FormBuilder::singleton();
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
        $renderer =& $runner->getRenderer();
        $errors = array();

        $uid = (integer)yb_Var::request('uid');
        if ($uid < 1) {
            $errors[] = t("Invalid User ID.", null, 'acl');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_by_id($uid);
        if (count($users) != 1) {
            $errors[] = t("Invalid User ID.", null, 'acl');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        return true;
    }

    // }}}
    // {{{ guard_on_select_group()

    function guard_on_select_group(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();
        $user_context = yb_Session::user_context();
        $id = (integer)yb_Var::request('gid');
        if ($id < 1) {
            $errors[] = t("Invalid Group ID.", null, 'acl');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        $dao =& yb_dao_Factory::get('group');
        $groups = $dao->find_by_id($id);
        if (count($groups) != 1) {
            $errors[] = t("Invalid Group ID.", null, 'acl');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        return true;
    }

    // }}}
    // {{{ guard_on_select_acl()

    function guard_on_select_acl(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();
        $id = (integer)yb_Var::request('id');
        if ($id < 1) {
            $errors[] = t("Invalid ACL ID.", null, 'acl');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        $acls = yb_tx_acl_Finder::by_id($id);
        if (count($acls) != 1) {
            $errors[] = t("ACL Not Found. (ID=%id)", 
                array('id' => $id), 'acl');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        $acl = $acls[0];
        if ('onList_Delete' == $event) {
            $idx =& grain_Factory::index('pair', 'acl_to_data');
            if ($idx->count_for($id) > 0) {
                $errors[] = t("There're more than one pages using \"%acl\" ACL(ID=%id).", 
                    array('acl' => $acl['name'], 'id' => $id), 'acl');
                $renderer->set('validate_errors', $errors);
                return false;
            }
        }

        $bookmark->set('id', $id);

        // copy acl data to virtual record.
        $bookmark->set('virtual', $acl);

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
