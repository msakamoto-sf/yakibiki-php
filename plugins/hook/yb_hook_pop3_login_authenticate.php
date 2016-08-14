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

require_once('Net/POP3.php');
require_once('yb/tx/user/Login.php');

/**
 * YakiBiki POP3 Login Authentication EXAMPLE hook.
 *
 * How To Use : add this line in your libs/config.php
 * _YB('hook.convert.login_authenticate', 'pop3_login_authenticate');
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_hook_pop3_login_authenticate.php 514 2009-01-07 07:35:20Z msakamoto-sf $
 * @param string user id/name
 * @param string password
 * @return mixed user infor. null when login failure.
 */
function yb_hook_pop3_login_authenticate($user, $password)
{
    $ret = null;
    // $failback_users use YakiBiki default login when failed POP3 login.
    $failback_users = array('user01');
    $pop3 =& new Net_POP3();

    // change POP3 server's hostname, port.
    $r = $pop3->connect('192.168.1.1', 110);
    if (PEAR::isError($r)) {
        dlog($r);
        return $ret;
    }

    $r = $pop3->login($user, $password, 'USER'); // plain USER login
    //$r = $pop3->login($user, $password, 'APOP'); // APOP login
    if (PEAR::isError($r)) {
        // failback to yakibiki authentication
        if (in_array($user, $failback_users)) {
            $ret = yb_tx_user_Login::go($user, $password);
        }
    } else {
        // POP3 login success
        $dao =& yb_dao_Factory::get('user');
        $users = $dao->find_all();
        foreach ($users as $u) {
            if ($u['name'] == $user) {
                $ret = $u;
                break;
            }
        }
    }

    $pop3->disconnect();

    return $ret;
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
