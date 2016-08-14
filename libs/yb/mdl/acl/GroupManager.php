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
require_once('yb/mdl/acl/FormBuilder.php');
require_once('yb/mdl/acl/GroupPager.php');

/**
 * YakiBiki acl module group manager page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: GroupManager.php 357 2008-09-18 04:43:46Z msakamoto-sf $
 */
class yb_mdl_acl_GroupManager
{
    // {{{ list_page()

    function list_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $form =& new yb_mdl_acl_FormBuilder();
        $form->setDefaults($virtual);
        $form_datas = $form->freeze_groupmanager();

        $navi =& new yb_mdl_acl_GroupPager();
        $params = $navi->setup();
        $dao_group =& yb_dao_Factory::get('group');
        $groups = $dao_group->find_all($params['sb'], $params['ob']);
        $navi->itemData($groups);
        $navi_datas = $navi->build();
        $pager = $navi_datas['pager'];
        $navi_datas['links'] = $pager->getLinks();
        $page_datas = $pager->getPageData();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL Group Permission Manager'));
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);
        $renderer->set('navi', $navi_datas);
        $renderer->set('groups', $page_datas);

        return "theme:modules/acl/group_manager_tpl.html";
    }

    // }}}
    // {{{ on_add()

    function on_add(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $gid = yb_Var::request('gid');
        $perm = yb_Var::request('perm');

        $valid_perms = array(
            YB_ACL_PERM_NONE, YB_ACL_PERM_READ, YB_ACL_PERM_READWRITE);

        if (!in_array($perm, $valid_perms)) {
            $errors = $renderer->get('validate_errors');
            $errors[] = t('Invalid Permission.', null, 'acl');
            $renderer->set('validate_errors', $errors);
            return "success";
        }

        $perms = $virtual['perms'];
        $_gids = array();
        foreach ($perms as $p) {
            if ($p['type'] == YB_ACL_TYPE_GROUP) {
                $_gids[] = $p['id'];
            }
        }

        if (in_array($gid, $_gids)) {
            $errors = $renderer->get('validate_errors');
            $errors[] = t('Given group (ID=%id) is already added to acl.', 
                array('id' => $gid), 'acl');
            $renderer->set('validate_errors', $errors);
            return "success";
        }

        $virtual['perms'][] = array(
            'id' => $gid,
            'type' => YB_ACL_TYPE_GROUP,
            'perm' => $perm,
        );

        $bookmark->set('virtual', $virtual);

        return "success";
    }

    // }}}
    // {{{ on_remove()

    function on_remove(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $gid = yb_Var::request('gid');

        $perms = $virtual['perms'];
        $_gids = array();
        foreach ($perms as $p) {
            if ($p['type'] == YB_ACL_TYPE_GROUP) {
                $_gids[] = $p['id'];
            }
        }

        if (!in_array($gid, $_gids)) {
            $errors = $renderer->get('validate_errors');
            $errors[] = t('Given group (ID=%id) does not belong to acl.', 
                array('id' => $gid), 'acl');
            $renderer->set('validate_errors', $errors);
            return "success";
        }

        $new_perms = array();
        foreach ($perms as $p) {
            if ($p['type'] == YB_ACL_TYPE_GROUP &&
                $p['id'] == $gid) {
                continue;
            }
            $new_perms[] = $p;
        }
        $virtual['perms'] = $new_perms;

        $bookmark->set('virtual', $virtual);

        return "success";
    }

    // }}}
    // {{{ on_update()

    function on_update(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $update_perms = yb_Var::request('g');

        $perms = $virtual['perms'];
        $new_perms = array();
        foreach ($perms as $p) {
            $id = $p['id'];
            if ($p['type'] == YB_ACL_TYPE_GROUP &&
                isset($update_perms[$id])) {
                $p['perm'] = $update_perms[$id];
            }
            $new_perms[] = $p;
        }

        $virtual['perms'] = $new_perms;
        $bookmark->set('virtual', $virtual);

        return "success";
    }

    // }}}
    // {{{ on_backto()

    function on_backto(&$runner, $event, &$bookmark, $params)
    {
        $backto = $bookmark->get('manager_backto');
        if ($backto == 'create') {
            return "to_create";
        } else {
            return "to_update";
        }
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
