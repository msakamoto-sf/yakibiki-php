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
 * YakiBiki Smarty Plugin : dao find_by_id short cut
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_dao_find_by.php 545 2009-07-14 03:10:36Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_dao_find_by($params, &$smarty)
{
    $dao = @$params['dao'];
    $ids = @$params['id'];
    $print = @$params['print'];
    if (empty($dao) || empty($ids) || empty($print)) {
        return '';
    }

    $dao =& yb_dao_Factory::get($dao);
    $r = $dao->find_by_id($ids);

    $prints = array();
    foreach ($r as $_r) {
        if (isset($_r[$print])) {
            $prints[] = h($_r[$print]);
        }
    }

    return implode(',&nbsp;', $prints);
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
