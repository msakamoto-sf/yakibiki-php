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
 *
 */

require_once('grain/Config.php');
require_once('grain/Raw.php');

class grain_Raw_TestCase extends UnitTestCase
{
    // {{{ test_cud()

    function test_cud()
    {
        $d = dirname(__FILE__) . '/Raw_TestCud';
        $r =& new grain_Raw($d, 3);

        // save()
        $this->assertTrue($r->save(1, 'data1'));
        $this->assertTrue($r->save(2, 'data2'));
        $this->assertTrue($r->save(3, 'data3'));
        $this->assertTrue($r->save(4, 'data4'));
        $this->assertTrue($r->save(5, 'data5'));
        $this->assertTrue($r->save(6, 'data6'));
        $this->assertTrue($r->save(20, 'data20'));

        // filename()
        $this->assertEqual($r->filename(1), realpath($d . '/3/1'));
        $this->assertEqual($r->filename(2), realpath($d . '/3/2'));
        $this->assertEqual($r->filename(3), realpath($d . '/3/3'));
        $this->assertEqual($r->filename(4), realpath($d . '/6/4'));
        $this->assertEqual($r->filename(5), realpath($d . '/6/5'));
        $this->assertEqual($r->filename(6), realpath($d . '/6/6'));
        $this->assertEqual($r->filename(20), realpath($d . '/21/20'));
        $this->assertEqual(file_get_contents($r->filename(1)), 'data1');
        $this->assertEqual(file_get_contents($r->filename(2)), 'data2');
        $this->assertEqual(file_get_contents($r->filename(3)), 'data3');
        $this->assertEqual(file_get_contents($r->filename(4)), 'data4');
        $this->assertEqual(file_get_contents($r->filename(5)), 'data5');
        $this->assertEqual(file_get_contents($r->filename(6)), 'data6');
        $this->assertEqual(file_get_contents($r->filename(20)), 'data20');

        // overwrite test
        $this->assertTrue($r->save(1, 'data1_2'));
        $this->assertEqual(file_get_contents($r->filename(1)), 'data1_2');

        // delete()
        $this->assertTrue($r->delete(1));
        $this->assertFalse($r->delete(1));
        $this->assertNull($r->filename(1));

        // clean up temporary index directory.
        System::rm(" -rf " . $d);
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
