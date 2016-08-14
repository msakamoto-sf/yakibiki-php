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
 * required
 */
require_once('yb/datatype/Utils.php');

/**
 * YakiBiki template module update page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Update.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_template_Update
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Template Update'));
        $renderer->set('virtual', $virtual);

        return "theme:modules/template/update_input_tpl.html";
    }

    // }}}
    // {{{ on_confirm()

    function on_confirm(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $virtual = $bookmark->get('virtual');
        $dtplugin =& yb_Util::factoryDataType($virtual['type']);
        $errors = array();

        // name
        $name = trim(yb_Var::request('name'));
        if (empty($name)) {
            $errors[] = t('%label is required.', 
                array('label' => t('Template name')));
        } else {
            $virtual['name'] = $name;
        }

        // title
        $title = trim(yb_Var::request('title'));
        if (empty($title)) {
            $errors[] = t('%label is required.', 
                array('label' => t('Template title')));
        } else {
            $virtual['title'] = $title;
        }

        $dao =& yb_dao_Factory::get('template');
        $alls = $dao->find_all();
        foreach ($alls as $t) {
            if ($t['id'] == $virtual['id']) {
                continue;
            }
            if ($t['name'] == $name) {
                $errors[] = t(
                    'Given template name (%name) has been already used.',
                    array('name' => $name));
            }
            if ($t['title'] == $title) {
                $errors[] = t(
                    'Given template title (%title) has been already used.',
                    array('title' => $title));
            }
        }

        // acl
        if (yb_datatype_Utils::validate_acl(
            $errors, yb_Var::request('acl'))) {
            $virtual['acl'] = yb_Var::request('acl');
        }

        // categories
        $cs = array();
        if (yb_datatype_Utils::validate_categories(
            $errors, yb_Var::request('categories'), $cs)) {
            $virtual['categories'] = $cs;
        }

        // is_{version|comments}_moderated
        $virtual['is_versions_moderated'] = 
            (yb_Var::request('is_versions_moderated') == '1');
        $virtual['is_comments_moderated'] = 
            (yb_Var::request('is_comments_moderated') == '1');

        // datatype-specific template data
        $dtplugin->validate_template_data($errors, $virtual);

        $bookmark->set('virtual', $virtual);

        if (count($errors) > 0) {
            $renderer->set('validate_errors', $errors);
            return 'errorback';
        }

        return 'success';
    }

    // }}}
    // {{{ confirm_page()

    function confirm_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Template Update') . ' (' . t('Confirm') . ')');
        $renderer->set('virtual', $virtual);

        return "theme:modules/template/update_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        $dtplugin =& yb_Util::factoryDataType($virtual['type']);
        $id = $bookmark->get('id');

        $data = $dtplugin->update_template_data($id, $virtual);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf(
            'template update success: id=%d, name=%s',
            $id, $data['name']));

        $bookmark->set('virtual', $data);

        return 'success';
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Template Update') . ' (' . t('Complete') . ')');
        $renderer->set('virtual', $virtual);

        return "theme:modules/template/update_finish_tpl.html";
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
