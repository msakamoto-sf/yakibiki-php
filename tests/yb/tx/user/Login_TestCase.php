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
 *   limitations under the License.*
 */

require_once('yb/tx/user/Login.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_user_Login_TestCase extends yb_tx_UnitTestCaseBase
{
    // {{{ test_go()

    function test_go()
    {
        $dao_user =& yb_dao_Factory::get('user');

        $data1 = array(
            'mail' => 'create01@test.com',
            'name' => 'testCreate_User1',
            'password' => yb_Util::hash_password('buzzword1'),
            'status' => YB_USER_STATUS_OK,
            'role' => array(),
            );
        $data2 = array(
            'mail' => 'create02@test.com',
            'name' => 'testCreate_User2',
            'password' => yb_Util::hash_password('buzzword2'),
            'status' => YB_USER_STATUS_OK,
            'role' => array(),
            );
        $dao_user->create($data1);
        $dao_user->create($data2);

        $this->assertNull(
            yb_tx_user_Login::go('create01@test.com', 'buzzword'));
        $this->assertNull(
            yb_tx_user_Login::go('create03@test.com', 'buzzword2'));

        // login success (1) : mail-address and password
        $user1 = yb_tx_user_Login::go('create01@test.com', 'buzzword1');
        $user2 = yb_tx_user_Login::go('create02@test.com', 'buzzword2');

        $this->assertEqual($user1['id'], 1);
        $this->assertEqual($user1['name'], $data1['name']);
        $this->assertEqual($user1['mail'], $data1['mail']);

        $this->assertEqual($user2['id'], 2);
        $this->assertEqual($user2['name'], $data2['name']);
        $this->assertEqual($user2['mail'], $data2['mail']);

        // login success (2) : user-name and password
        $user1 = yb_tx_user_Login::go('testCreate_User1', 'buzzword1');
        $user2 = yb_tx_user_Login::go('testCreate_User2', 'buzzword2');

        $this->assertEqual($user1['id'], 1);
        $this->assertEqual($user1['name'], $data1['name']);
        $this->assertEqual($user1['mail'], $data1['mail']);

        $this->assertEqual($user2['id'], 2);
        $this->assertEqual($user2['name'], $data2['name']);
        $this->assertEqual($user2['mail'], $data2['mail']);

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
