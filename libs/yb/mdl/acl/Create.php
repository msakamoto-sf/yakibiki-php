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
require_once('yb/tx/acl/Create.php');
require_once('yb/mdl/acl/FormBuilder.php');

/**
 * YakiBiki acl module create page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Create.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_acl_Create
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        if (!$virtual) {
            $virtual = array(
                "name" => "",
                "policy" => YB_ACL_POLICY_POSI,
                "perms" => array(),
            );
            $bookmark->set('virtual', $virtual);
        }

        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->build();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL Create'));
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        $bookmark->set('manager_backto', 'create');

        return "theme:modules/acl/create_input_tpl.html";
    }

    // }}}
    // {{{ on_temporary_save()

    function on_temporary_save(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form->accept('post');
        $virtual = $form->export();
        $bookmark->set('virtual', $virtual);

        return "success";
    }

    // }}}
    // {{{ on_confirm()

    function on_confirm(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $form =& new yb_mdl_acl_FormBuilder();
        $form->setDefaults($virtual);
        $form->accept('post');
        $virtual = $form->export();
        $bookmark->set('virtual', $virtual);

        // {{{ check acl name duplication

        $errors = $renderer->get('validate_errors');
        $dao_acl =& yb_dao_Factory::get('acl');
        $all_acls = $dao_acl->find_all();
        foreach ($all_acls as $a) {
            $_n = $a['name'];
            if ($_n == $virtual['name']) {
                $errors[] = t('Given acl name (%name) has been already used.', 
                    array('name' => $_n));
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
        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL Create') . ' (' . t('Confirm') . ')');
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/acl/create_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        $uc = yb_Session::user_context();
        $virtual['owner'] = $uc['id'];

        $result = yb_tx_acl_Create::go($virtual);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf('acl create success: id=%d, ser=%s', 
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
        $acls = yb_tx_acl_Finder::by_id($id);

        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($acls[0]);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL Create') . ' (' . t('Complete') . ')');
        $renderer->set('virtual', $acls[0]);
        $renderer->set('form', $form_datas);

        return "theme:modules/acl/create_finish_tpl.html";
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
