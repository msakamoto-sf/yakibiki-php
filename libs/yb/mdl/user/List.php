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
 * requires
 */
require_once('yb/mdl/user/Pager.php');

/**
 * YakiBiki user module list page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: List.php 348 2008-09-15 15:53:54Z msakamoto-sf $
 */
class yb_mdl_user_List
{
    // {{{ list_page()

    function list_page(&$runner, $page, &$bookmark, $params)
    {
        $navi =& new yb_mdl_user_Pager();
        $params = $navi->setup();

        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_all($params['sb'], $params['ob']);
        $navi->itemData($users);

        $navi_datas= $navi->build();

        $pager = $navi_datas['pager'];
        $navi_datas['links'] = $pager->getLinks();
        $page_datas = $pager->getPageData();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('User List'));
        $renderer->set('navi', $navi_datas);
        $renderer->set('users', $page_datas);
        $renderer->set('roles_display_names', 
            yb_Util::user_roles_displaynames());

        return "theme:modules/user/list_tpl.html";
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
