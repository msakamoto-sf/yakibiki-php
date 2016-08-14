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
require_once('yb/mdl/acl/AclPager.php');

/**
 * YakiBiki acl module list page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: List.php 357 2008-09-18 04:43:46Z msakamoto-sf $
 */
class yb_mdl_acl_List
{
    // {{{ list_page()

    function list_page(&$runner, $page, &$bookmark, $params)
    {
        $navi =& new yb_mdl_acl_AclPager();
        $params = $navi->setup();

        $acls = yb_tx_acl_Finder::all($params['sb'], $params['ob']);
        $navi->itemData($acls);

        $navi_datas = $navi->build();

        $pager = $navi_datas['pager'];
        $navi_datas['links'] = $pager->getLinks();
        $page_datas = $pager->getPageData();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('ACL List'));
        $renderer->set('navi', $navi_datas);
        $renderer->set('acls', $page_datas);

        return "theme:modules/acl/list_tpl.html";
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
