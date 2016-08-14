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
require_once('yb/mdl/group/GroupPager.php');

/**
 * YakiBiki group module list page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: List.php 351 2008-09-16 07:14:48Z msakamoto-sf $
 */
class yb_mdl_group_List
{
    // {{{ list_page()

    function list_page(&$runner, $page, &$bookmark, $params)
    {
        $navi =& new yb_mdl_group_GroupPager();
        $params = $navi->setup();

        $groups = yb_tx_group_Finder::all($params['sb'], $params['ob']);
        $navi->itemData($groups);

        $navi_datas = $navi->build();

        $pager = $navi_datas['pager'];
        $navi_datas['links'] = $pager->getLinks();
        $page_datas = $pager->getPageData();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Group List'));
        $renderer->set('navi', $navi_datas);
        $renderer->set('groups', $page_datas);

        return "theme:modules/group/list_tpl.html";
    }

    // }}}
    // {{{ on_backtolist()

    function on_backtolist(&$runner, $event, &$bookmark, $params)
    {
        // clear temporary virtual record.
        $bookmark->remove('virtual');

        return "success";
    }

    // }}}
    // {{{ guard_on_select_group()

    function guard_on_select_group(&$runner, $event, &$bookmark, $params)
    {
        $id = (integer)yb_Var::get('id');
        if ($id < 1) {
            return false;
        }
        $groups = yb_tx_group_Finder::by_id($id);
        if (count($groups) != 1) {
            return false;
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
