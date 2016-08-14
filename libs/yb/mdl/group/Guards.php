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
require_once('yb/tx/group/Finder.php');
require_once('yb/mdl/group/FormBuilder.php');

/**
 * YakiBiki group module Xhwlay guard class
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Guards.php 503 2009-01-06 01:08:12Z msakamoto-sf $
 */
class yb_mdl_group_Guards
{
    // {{{ guard_on_validate()

    function guard_on_validate(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_group_FormBuilder::singleton();
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
            $errors[] = t("Invalid User ID.", null, 'group');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_by_id($uid);
        if (count($users) != 1) {
            $errors[] = t("Invalid User ID.", null, 'group');
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
        $id = (integer)yb_Var::request('id');
        if ($id < 1) {
            $errors[] = t("Invalid Group ID.", null, 'group');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        $groups = yb_tx_group_Finder::by_id($id);
        if (count($groups) != 1) {
            $errors[] = t("Group Not Found. (ID=%id)", 
                array('id' => $id), 'group');
            $renderer->set('validate_errors', $errors);
            return false;
        }
        if (count($user_context['role']) == 1 &&
            in_array('group', $user_context['role'])) {
            if ($groups[0]['owner']['id'] != $user_context['id']) {
                $errors[] = t("Permission(Role) Denied for group id %id.", 
                    array('id' => $id), "group");
                $renderer->set('validate_errors', $errors);
                return false;
            }
        }

        if ('onList_Delete' == $event) {
            $dao_acl =& yb_dao_Factory::get('acl');
            $acls = $dao_acl->find_all();
            foreach ($acls as $acl) {
                $perms = $acl['perms'];
                foreach ($perms as $p) {
                    if (YB_ACL_TYPE_GROUP != $p['type']) {
                        continue;
                    }
                    if ($id != $p['id']) {
                        continue;
                    }
                    $errors[] = t(
                        "There're more than one ACLs using \"%name\" group(ID=%id).",
                        array('id' => $id, 'name' => $groups[0]['name']), 
                        "group");
                    $renderer->set('validate_errors', $errors);
                    return false;
                }
            }
        }

        $bookmark->set('id', $id);

        // copy group data to virtual record.
        $bookmark->set('virtual', $groups[0]);

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
