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
 * YakiBiki Acl Transactions : Create
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Create.php 356 2008-09-17 16:23:37Z msakamoto-sf $
 */
class yb_tx_acl_Create
{
    /**
     * @static
     * @access public
     * @param array acl data
     * @return array created acl data.
     */
    function go( $data)
    {
        $user =& yb_dao_Factory::get('user');
        $acl =& yb_dao_Factory::get('acl');

        if (!isset($data['perms']) || !is_array($data['perms'])) {
            $data['perms'] = array();
        }

        $id = $acl->create(array(
            'owner' => $data['owner'],
            'name' => $data['name'],
            'policy' => $data['policy'],
            'perms' => $data['perms'],
        ));

        $results = $acl->find_by_id($id);
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
