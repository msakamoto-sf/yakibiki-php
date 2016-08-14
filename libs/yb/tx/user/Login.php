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
 * YakiBiki User Transactions : Login
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Login.php 345 2008-09-15 07:03:41Z msakamoto-sf $
 */
class yb_tx_user_Login
{
    /**
     * @static
     * @access public
     * @param string mail address or user name
     * @param string password
     * @return mixed If login ok, return login user data. 
     *               If failure, return null.
     */
    function go($mail_name, $password)
    {
        $mail_name = (string)$mail_name;
        $password = yb_Util::hash_password((string)$password);

        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_all();
        foreach ($users as $user) {
            $_mail = (string)$user['mail'];
            $_name = (string)$user['name'];
            $_password = (string)$user['password'];
            if ($user['status'] != YB_USER_STATUS_OK) {
                continue;
            }
            if (($_password === $password) &&
                (
                    ($_mail === $mail_name) || 
                    ($_name === $mail_name)
                )) {
                return $user;
            }
        }
        return null;
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
