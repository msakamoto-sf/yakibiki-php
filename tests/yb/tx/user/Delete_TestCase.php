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

require_once('yb/tx/user/Delete.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_user_Delete_TestCase extends yb_tx_UnitTestCaseBase
{
    function test_go()
    {
        $data = array(
            "mail" => "create01@test.com",
            "name" => "testCreate_User1",
            "password" => "buzzword",
            "status" => YB_USER_STATUS_DISABLED,
            "role" => array('group'),
            );
        $dao_user =& yb_dao_Factory::get('user');
        $id = $dao_user->create($data);

        $this->assertTrue(yb_tx_user_Delete::go($id));
        $users = $dao_user->find_by_id($id);
        $this->assertEqual(count($users), 0);
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
