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
require_once('grain/Grain.php');

class grain_Grain_TestCase extends UnitTestCase
{
    // {{{ test_find_data_files()

    function test_find_data_files()
    {
        // Grain_Test00 : empty directory, 0 count.
        $d = dirname(__FILE__) . '/Grain_Test00';
        $g =& new grain_Grain($d, 10);
        $r = $g->find_data_files();
        $this->assertEqual(count($r), 0);

        // Grain_Test01 : 1 data file should be found.
        $d = dirname(__FILE__) . '/Grain_Test01';
        $g =& new grain_Grain($d, 10);
        $r = $g->find_data_files();
        $this->assertEqual(count($r), 1);
        $this->assertEqual($r[0], realpath($d . '/10.dat'));

        // Grain_Test02 : 2 data files should be found.
        $d = dirname(__FILE__) . '/Grain_Test02';
        $g =& new grain_Grain($d, 10);
        $r = $g->find_data_files();
        $this->assertEqual(count($r), 2);
        $this->assertEqual($r[0], realpath($d . '/10.dat'));
        $this->assertEqual($r[1], realpath($d . '/20.dat'));

    }

    // }}}
    // {{{ test_data_filename()

    function test_data_filename()
    {
        $dir = dirname(__FILE__) . '/Grain_Test00';
        $g =& new grain_Grain($dir, 10);
        $this->assertEqual($g->data_filename(1), $dir. '/10.dat');
        $this->assertEqual($g->data_filename(9), $dir. '/10.dat');
        $this->assertEqual($g->data_filename(10), $dir. '/10.dat');
        $this->assertEqual($g->data_filename(11), $dir. '/20.dat');
        $this->assertEqual($g->data_filename(19), $dir. '/20.dat');
        $this->assertEqual($g->data_filename(20), $dir. '/20.dat');
        $this->assertEqual($g->data_filename(21), $dir. '/30.dat');

        $g =& new grain_Grain($dir, 1);
        $this->assertEqual($g->data_filename(1), $dir. '/1.dat');
        $this->assertEqual($g->data_filename(9), $dir. '/9.dat');
        $this->assertEqual($g->data_filename(10), $dir. '/10.dat');
        $this->assertEqual($g->data_filename(11), $dir. '/11.dat');
        $this->assertEqual($g->data_filename(19), $dir. '/19.dat');
        $this->assertEqual($g->data_filename(20), $dir. '/20.dat');
        $this->assertEqual($g->data_filename(21), $dir. '/21.dat');

    }

    // }}}
    // {{{ test_find()

    function test_find()
    {
        // Grain_Test00
        $d = dirname(__FILE__) . '/Grain_Test00';
        $g =& new grain_Grain($d, 10);
        $r = $g->find();
        $this->assertEqual(count($r), 0);

        // Grain_Test01
        $d = dirname(__FILE__) . '/Grain_Test01';
        $g =& new grain_Grain($d, 10);
        $r = $g->find();
        $this->assertEqual(count($r), 2);
        $this->assertEqual(count($r[1]), 3);
        $this->assertEqual($r[1]['column1'], 'ABC');
        $this->assertEqual($r[1]['column2'], 'DEF');
        $this->assertEqual($r[1]['column3'], 'GHI');
        $this->assertEqual(count($r[5]), 4);
        $this->assertEqual($r[5]['column1'], 'abc');
        $this->assertEqual($r[5]['column2'], 'def');
        $this->assertEqual($r[5]['column3'], 'ghi');
        $this->assertEqual($r[5]['column4'], 'jkl');

        // Grain_Test02
        $d = dirname(__FILE__) . '/Grain_Test02';
        $g =& new grain_Grain($d, 10);
        $r = $g->find(1);
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[1]), 3);
        $this->assertEqual($r[1]['column1'], 'ABC');
        $this->assertEqual($r[1]['column2'], 'DEF');
        $this->assertEqual($r[1]['column3'], 'GHI');
        $r = $g->find(10);
        $this->assertEqual(count($r[10]), 4);
        $this->assertEqual($r[10]['column1'], 'abc');
        $this->assertEqual($r[10]['column2'], 'def');
        $this->assertEqual($r[10]['column3'], 'ghi');
        $this->assertEqual($r[10]['column4'], 'jkl');
        $r = $g->find(11);
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[11]), 3);
        $this->assertEqual($r[11]['column1'], 'ABC');
        $this->assertEqual($r[11]['column2'], 'DEF');
        $this->assertEqual($r[11]['column3'], 'GHI');
        $r = $g->find(19);
        $this->assertEqual(count($r[19]), 4);
        $this->assertEqual($r[19]['column1'], 'abc');
        $this->assertEqual($r[19]['column2'], 'def');
        $this->assertEqual($r[19]['column3'], 'ghi');
        $this->assertEqual($r[19]['column4'], 'jkl');

        // illegal big box number
        $r = $g->find(1000);
        $this->assertEqual(count($r), 0);
    }

    // }}}
    // {{{ test_cud()

    function test_cud()
    {
        $d = dirname(__FILE__) . '/Grain_TestCud';
        $g =& new grain_Grain($d, 10);

        $this->assertTrue($g->destroy());

        // {{{ save()

        $b1 = array(
            'column1' => 'ABC',
            'column2' => 'DEF' . GRAIN_DATA_RS . 'GHI',
            'column3 foo' => 'JKL',
        );
        $this->assertTrue($g->save(1, $b1));

        $b2 = array(
            'column1' => 'abc',
            'column2' => 'def' . GRAIN_DATA_GS . 'ghi',
            'column3 foo' => 'jkl',
            'column4' . GRAIN_DATA_RS . ' bar' => 'mno',
        );
        $this->assertTrue($g->save(11, $b2));

        $b3 = array(
            'column1' => 100,
            'column2' => 200,
            'column3' => 300,
        );
        $this->assertTrue($g->save(12, $b3));

        $r = $g->find();
        $this->assertEqual(count($r), 3);
        $this->assertEqual(count($r[1]), 3);
        $this->assertEqual($r[1]['column1'], 'ABC');
        $this->assertEqual($r[1]['column2'], 'DEFGHI');
        $this->assertEqual($r[1]['column3 foo'], 'JKL');
        $this->assertEqual(count($r[11]), 4);
        $this->assertEqual($r[11]['column1'], 'abc');
        $this->assertEqual($r[11]['column2'], 'def' . GRAIN_DATA_GS . 'ghi');
        $this->assertEqual($r[11]['column3 foo'], 'jkl');
        $this->assertEqual($r[11]['column4 bar'], 'mno');
        $this->assertEqual(count($r[12]), 3);
        $this->assertEqual($r[12]['column1'], 100);
        $this->assertEqual($r[12]['column2'], 200);
        $this->assertEqual($r[12]['column3'], 300);

        // }}}
        // {{{ delete()

        $this->assertTrue($g->delete(1));
        $this->assertFalse($g->delete(2));
        $this->assertTrue($g->delete(11));

        $r = $g->find(1);
        $this->assertEqual(count($r), 0);
        $r = $g->find(11);
        $this->assertEqual(count($r), 0);

        // }}}
        // {{{ overwrite operation
        $b1 = array(
            'column1' => 'foo bar',
            'column2' => 'hoge bohe',
            'column3' => '20080101235959',
        );
        $b2 = array(
            'column1' => 100,
            'column2' => 101,
            'column3' => 102,
            'column4' => 103,
        );
        $b3 = array(
            'column1' => 'ABC',
            'column2' => 'DEF',
        );
        $b4 = array(
            'column1' => 'abc',
            'column2' => 'def',
        );

        $this->assertTrue($g->save(2, $b1));
        $r = $g->find(2);
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[2]), 3);
        $this->assertEqual($r[2]['column1'], $b1['column1']);
        $this->assertEqual($r[2]['column2'], $b1['column2']);
        $this->assertEqual($r[2]['column3'], $b1['column3']);

        $this->assertTrue($g->save(2, $b2));
        $r = $g->find(2);
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[2]), 4);
        $this->assertEqual($r[2]['column1'], $b2['column1']);
        $this->assertEqual($r[2]['column2'], $b2['column2']);
        $this->assertEqual($r[2]['column3'], $b2['column3']);
        $this->assertEqual($r[2]['column4'], $b2['column4']);

        // If column is deleted, you must delete and re-save it.
        $this->assertTrue($g->save(2, $b3));
        $r = $g->find(2);
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[2]), 4); // old columns are remaind.
        $this->assertEqual($r[2]['column1'], $b3['column1']);
        $this->assertEqual($r[2]['column2'], $b3['column2']);
        $this->assertEqual($r[2]['column3'], $b2['column3']); // old
        $this->assertEqual($r[2]['column4'], $b2['column4']); // old

        // delete it.
        $this->assertTrue($g->delete(2));
        // save it.
        $this->assertTrue($g->save(2, $b4));
        $r = $g->find(2);
        $this->assertEqual(count($r), 1);
        $this->assertEqual(count($r[2]), 2); // new columns
        $this->assertEqual($r[2]['column1'], $b4['column1']);
        $this->assertEqual($r[2]['column2'], $b4['column2']);

        // }}}

        $this->assertTrue($g->destroy());

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
