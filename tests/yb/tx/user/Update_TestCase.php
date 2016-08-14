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

require_once('yb/tx/user/Update.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_user_Update_TestCase extends yb_tx_UnitTestCaseBase
{
    function test_go()
    {
        $dao_user =& yb_dao_Factory::get('user');
        $data = array(
            'mail' => 'create01@test.com',
            'name' => 'testCreate_User1',
            'password' => yb_Util::hash_password('buzzword'),
            'status' => YB_USER_STATUS_OK,
            'role' => array(),
        );
        $id = $dao_user->create($data);
        $users = $dao_user->find_by_id($id);
        $results = $users[0];

        $data = array(
            "name" => "testUpdate_User1",
            "mail" => "update01@test.com",
            "status" => YB_USER_STATUS_DISABLED,
            "role" => array('group'),
            );
        $this->assertTrue(yb_tx_user_Update::go($results['id'], $data));

        $users = $dao_user->find_by_id($results['id']);
        $this->assertEqual($users[0]['id'], $results['id']);
        $this->assertEqual($users[0]['name'], $data['name']);
        $this->assertEqual($users[0]['mail'], $data['mail']);
        $this->assertEqual($users[0]['status'], $data['status']);
        $this->assertEqual(count($users[0]['role']), 1);
        $this->assertTrue(in_array('group', $users[0]['role']));
        $this->assertEqual($users[0]['password'], $results['password']);

        $data = array(
            "password" => "newpassword",
            "role" => array(),
            );
        $this->assertTrue(yb_tx_user_Update::go($results['id'], $data));
        $users2 = $dao_user->find_by_id($results['id']);
        $this->assertEqual($users2[0]['id'], $results['id']);
        $this->assertEqual($users2[0]['name'], $users[0]['name']);
        $this->assertEqual($users2[0]['mail'], $users[0]['mail']);
        $this->assertEqual($users2[0]['status'], $users[0]['status']);
        $this->assertEqual(count($users2[0]['role']), 0);
        $this->assertEqual($users2[0]['password'], 
            yb_Util::hash_password($data['password']));
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
