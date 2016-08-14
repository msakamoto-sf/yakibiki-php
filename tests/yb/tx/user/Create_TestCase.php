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

require_once('yb/tx/user/Create.php');
require_once('yb/tx/UnitTestCaseBase.php');

class yb_tx_user_Create_TestCase extends yb_tx_UnitTestCaseBase
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
        $results = yb_tx_user_Create::go($data);
        $this->assertEqual($results['id'], 1);
        $this->assertEqual($results['mail'], $data['mail']);
        $this->assertEqual($results['name'], $data['name']);
        $this->assertEqual($results['password'], 
            yb_Util::hash_password($data['password']));
        $this->assertEqual($results['status'], YB_USER_STATUS_DISABLED);
        $this->assertEqual($results['role'][0], 'group');
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
