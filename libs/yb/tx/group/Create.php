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
 * YakiBiki Group Transactions : Create
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Create.php 350 2008-09-16 06:28:04Z msakamoto-sf $
 */
class yb_tx_group_Create
{
    /**
     * @static
     * @access public
     * @param array user context
     * @param array group data
     * @return array created group data.
     */
    function go($data)
    {
        $user =& yb_dao_Factory::get('user');
        $group =& yb_dao_Factory::get('group');

        if (!isset($data['mates']) || !is_array($data['mates'])) {
            $data['mates'] = array();
        }

        $id = $group->create($data);

        $results = $group->find_by_id($id);
        $result = $results[0];

        $users = $user->find_by_id($result['owner']);
        $result['owner'] = (count($users) == 0) ? false : $users[0];

        return $result;
    }
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
