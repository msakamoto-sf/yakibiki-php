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
 * YakiBiki User Transactions : Create
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Create.php 347 2008-09-15 08:44:03Z msakamoto-sf $
 */
class yb_tx_user_Create
{
    /**
     * @static
     * @access public
     * @param array new user data
     * @return array created account data.
     */
    function go($data)
    {
        $dao =& yb_dao_Factory::get('user');
        $data['password'] = yb_Util::hash_password($data['password']);
        $id = $dao->create($data);
        if (empty($id)) {
            return null;
        }
        $results = $dao->find_by_id($id);
        return $results[0];
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
