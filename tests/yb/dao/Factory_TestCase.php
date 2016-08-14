<?php
/*
 *   Copyright (c) 2008 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
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

class yb_dao_Factory_test_dao1
{
    var $v1 = 1;
}

class yb_dao_Factory_test_dao2
{
    var $v2 = 1;
}

class yb_dao_Factory_TestCase extends UnitTestCase
{
    // {{{ test_get()

    function test_get()
    {
        $o1 =& yb_dao_Factory::get('factory_test_dao1');
        $o1->v1 = 100;
        $o2 =& yb_dao_Factory::get('factory_test_dao2');
        $o2->v2 = "abc";
        $o3 =& yb_dao_Factory::get('factory_test_dao3');
        $this->assertNull($o3);

        $o1 =& yb_dao_Factory::get('factory_test_dao1');
        $this->assertEqual($o1->v1, 100);
        $o1->v1 = 200;
        $o2 =& yb_dao_Factory::get('factory_test_dao2');
        $this->assertEqual($o2->v2, "abc");
        $o2->v2 = "def";

        $o1 =& yb_dao_Factory::get('factory_test_dao1');
        $this->assertEqual($o1->v1, 200);
        $o2 =& yb_dao_Factory::get('factory_test_dao2');
        $this->assertEqual($o2->v2, "def");

        $GLOBALS[FACTORY_ZONE] = mt_rand();
        $o1 =& yb_dao_Factory::get('factory_test_dao1');
        $this->assertEqual($o1->v1, 1);
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
