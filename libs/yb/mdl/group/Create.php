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
require_once('yb/tx/group/Create.php');
require_once('yb/mdl/group/UserPager.php');
require_once('yb/mdl/group/FormBuilder.php');

/**
 * YakiBiki group module create page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Create.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_group_Create
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $virtual = $bookmark->get('virtual');
        if (!$virtual) {
            $virtual = array(
                "name" => "",
                "mates" => array(),
            );
            $bookmark->set('virtual', $virtual);
        }
        $_mates = $virtual['mates'];
        $mates = array();
        if (count($_mates) > 0) {
            $mates = $dao_user->find_by_id($_mates);
        }

        $form =& yb_mdl_group_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->build();

        $navi =& new yb_mdl_group_UserPager();
        $params = $navi->setup();
        $users = $dao_user->find_all($params['sb'], $params['ob']);
        $navi->itemData($users);
        $navi_datas = $navi->build();
        $pager = $navi_datas['pager'];
        $navi_datas['links'] = $pager->getLinks();
        $page_datas = $pager->getPageData();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Group Create'));
        $renderer->set('mates', $mates);
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);
        $renderer->set('navi', $navi_datas);
        $renderer->set('users', $page_datas);
        $renderer->set('roles_display_names', 
            yb_Util::user_roles_displaynames());

        return "theme:modules/group/create_input_tpl.html";
    }

    // }}}
    // {{{ on_temporary_save()

    function on_temporary_save(&$runner, $event, &$bookmark, $params)
    {
        $form =& yb_mdl_group_FormBuilder::singleton();
        // already setDefaults(), accept() were called 
        // in Guard action.
        $bookmark->set('virtual', $form->export());
        return "success";
    }

    // }}}
    // {{{ on_add_user()

    function on_add_user(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $uid = yb_Var::request('uid');

        if (in_array($uid, $virtual['mates'])) {
            $errors = $renderer->get('validate_errors');
            $errors[] = t('Given user (ID=%id) is already added to group.', 
                array('id' => $uid));
            $renderer->set('validate_errors', $errors);
            return "success";
        }

        $virtual['mates'][] = $uid;
        $bookmark->set('virtual', $virtual);

        return "success";
    }

    // }}}
    // {{{ on_remove_user()

    function on_remove_user(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $uid = yb_Var::request('uid');

        if (!in_array($uid, $virtual['mates'])) {
            $errors = $renderer->get('validate_errors');
            $errors[] = t('Given user (ID=%id) does not belong to group.', 
                array('id' => $uid));
            $renderer->set('validate_errors', $errors);
            return "success";
        }

        $mates = $virtual['mates'];
        $new_mates = array();
        foreach ($mates as $m) {
            if ($uid == $m) {
                continue;
            }
            $new_mates[] = $m;
        }
        $virtual['mates'] = $new_mates;

        $bookmark->set('virtual', $virtual);

        return "success";
    }

    // }}}
    // {{{ on_confirm()

    function on_confirm(&$runner, $event, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_group_FormBuilder::singleton();
        // already setDefaults(), accept() were called 
        // in Guard action.
        $virtual = $form->export();
        $bookmark->set('virtual', $virtual);
        return "success";
    }

    // }}}
    // {{{ confirm_page()

    function confirm_page(&$runner, $page, &$bookmark, $params)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $virtual = $bookmark->get('virtual');
        $_mates = $virtual['mates'];
        $mates = array();
        if (count($_mates) > 0) {
            $mates = $dao_user->find_by_id($_mates);
        }

        $form =& yb_mdl_group_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Group Create') . ' (' . t('Confirm') . ')');
        $renderer->set('mates', $mates);
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/group/create_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        $user_context = yb_Session::user_context();
        $virtual['owner'] = $user_context['id'];

        $result = yb_tx_group_Create::go($virtual);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf('group create success: id=%d, ser=%s', 
            $result['id'], serialize($virtual)));

        // clear ACL Cache
        yb_AclCache::clean();

        $bookmark->set('id', $result['id']);
        return "success";
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $id = $bookmark->get('id');
        $groups = yb_tx_group_Finder::by_id($id);

        $dao_user =& yb_dao_Factory::get('user');
        $_mates = $groups[0]['mates'];
        $mates = array();
        if (count($_mates) > 0) {
            $mates = $dao_user->find_by_id($_mates);
        }

        $form =& yb_mdl_group_FormBuilder::singleton();
        $form->setDefaults($groups[0]);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Group Create') . ' (' . t('Complete') . ')');
        $renderer->set('mates', $mates);
        $renderer->set('virtual', $groups[0]);
        $renderer->set('form', $form_datas);

        return "theme:modules/group/create_finish_tpl.html";
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
