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
require_once('grain/Sequence.php');

class grain_Sequence_TestCase extends UnitTestCase
{
    // {{{ test_sequence()

    function test_sequence()
    {
        $GLOBALS[FACTORY_ZONE] = (string)mt_rand();

        $seq_dir = dirname(__FILE__) . '/tmp';
        mkdir($seq_dir);
        $_old = grain_Config::set('grain.dir.sequence', $seq_dir);

        $s1 =& grain_Sequence::factory('class1');
        $this->assertEqual($s1->next(), 1);
        $this->assertEqual($s1->next(), 2);
        $this->assertEqual($s1->next(), 3);
        $this->assertEqual($s1->current(), 3);
        $this->assertEqual($s1->next(), 4);
        $s1->dummy = true;

        $s2 =& grain_Sequence::factory('class2');
        $this->assertEqual($s2->next(), 1);
        $this->assertEqual($s2->next(), 2);
        $this->assertEqual($s2->next(), 3);
        $this->assertEqual($s2->current(), 3);
        $this->assertEqual($s2->next(), 4);

        $this->assertTrue($s1->set(10));
        $this->assertEqual($s1->current(), 10);
        $this->assertEqual($s1->next(), 11);

        $this->assertTrue($s2->set(0));
        $this->assertEqual($s2->current(), 0);
        $this->assertEqual($s2->next(), 1);

        $GLOBALS[FACTORY_ZONE] = (string)mt_rand();
        $s1 =& grain_Sequence::factory('class1');
        $this->assertFalse(isset($s1->dummy));

        System::rm(" -rf " . grain_Config::set('grain.dir.sequence', $_old));
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
