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

require_once('grain/Config.php');

class grain_Config_TestCase extends UnitTestCase
{
    // {{{ test_config()

    function test_config()
    {
        $curr = grain_Config::get("a.b.c");
        $this->assertNull($curr);
        $old = grain_Config::set("a.b.c", "new_value");
        $this->assertNull($old);
        $curr = grain_Config::get("a.b.c");
        $this->assertEqual($curr, "new_value");

        grain_Config::set("d.e.f", array(1, 2, 3, 4, 5));
        $curr = grain_Config::get("d.e.f");
        $this->assertEqual(count($curr), 5);
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
