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
require_once('yb/tx/acl/Update.php');
require_once('yb/mdl/acl/FormBuilder.php');

/**
 * YakiBiki acl module update page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Update.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_acl_Update
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form_datas = $form->build();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL Update'));
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        $bookmark->set('manager_backto', 'update');

        return "theme:modules/acl/update_input_tpl.html";
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
        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form->accept('post');
        $virtual = $form->export();
        $bookmark->set('virtual', $virtual);

        // {{{ check acl name duplication

        // We now use DAO's find_all(), NOT TX's find_all().
        // because, "name" duplication check needs ALL acl datas
        // not limited by user context's role or ids.

        $errors = $renderer->get('validate_errors');
        $dao_acl =& yb_dao_Factory::get('acl');
        $all_acls = $dao_acl->find_all();
        foreach ($all_acls as $a) {
            $_id = $a['id'];
            $_n = $a['name'];
            if ($_n == $virtual['name'] && $_id != $virtual['id']) {
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
        $renderer->setTitle(t('ACL Update') . ' (' . t('Confirm') . ')');
        $renderer->set('form', $form_datas);
        $renderer->set('virtual', $virtual);

        return "theme:modules/acl/update_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $data = $bookmark->get('virtual');
        unset($data['owner']); // not modified field.

        yb_tx_acl_Update::go($data['id'], $data);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf(
            'acl update success: id=%d, ser=%s',
            $data['id'], serialize($data)));

        // clear ACL Cache
        yb_AclCache::clean();

        return "success";
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        $id = $virtual['id'];
        $acls = yb_tx_acl_Finder::by_id($id);

        $form =& yb_mdl_acl_FormBuilder::singleton();
        $form->setDefaults($acls[0]);
        $form_datas = $form->freeze();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL Update') . ' (' . t('Complete') . ')');
        $renderer->set('virtual', $acls[0]);
        $renderer->set('form', $form_datas);

        return "theme:modules/acl/update_finish_tpl.html";
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
