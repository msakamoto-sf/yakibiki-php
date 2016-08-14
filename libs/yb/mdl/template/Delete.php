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
 * YakiBiki template module delete page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Delete.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_template_Delete
{
    // {{{ confirm_page()

    function confirm_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Template Delete') . ' (' . t('Confirm') . ')');
        $renderer->set('virtual', $virtual);

        return "theme:modules/template/delete_confirm_tpl.html";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');
        $dtplugin =& yb_Util::factoryDataType($virtual['type']);
        $id = $bookmark->get('id');

        $dtplugin->delete_template_data($id, $virtual);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf('template delete success: id=%d, name=%s', 
            $id, $virtual['name']));

        return 'success';
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $virtual = $bookmark->get('virtual');

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Template Delete') . ' (' . t('Complete') . ')');
        $renderer->set('virtual', $virtual);

        return "theme:modules/template/delete_finish_tpl.html";
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
