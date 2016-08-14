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

require_once('System.php');

class yb_tx_UnitTestCaseBase extends UnitTestCase
{
    var $_backups_grains;
    var $_backups_YB;

    // {{{ setUp()

    function setUp()
    {
        $GLOBALS[FACTORY_ZONE] = mt_rand();

        $root_dir = realpath(dirname(__FILE__) . '/tmp');
        $grain_dir = $root_dir . '/grain';
        $index_dir = $root_dir . '/index';
        $seq_dir = $root_dir . '/seq';
        $raw_dir = $root_dir . '/raw';

        $this->assertTrue(mkdir($grain_dir) && mkdir($index_dir) && 
            mkdir($seq_dir) && mkdir($raw_dir));

        $backups = array(
            'grain.dir.grain' => $grain_dir, 
            'grain.dir.index' => $index_dir, 
            'grain.dir.sequence' => $seq_dir, 
            'grain.dir.raw' => $raw_dir,
        );

        foreach ($backups as $k => $v) {
            $this->_backups_grains[$k] = grain_Config::set($k, $v);
        }

        $cache_options = _YB('cache.options');
        $cache_options['caching'] = false;
        $this->_backups_YB = array(
            'cache_options' => _YB('cache.options', $cache_options),
        );

        ob_end_flush();
    }

    // }}}
    // {{{ tearDown()

    function tearDown()
    {
        // clean ups and restores.
        foreach ($this->_backups_grains as $k => $v) {
            $d = grain_Config::set($k, $v);
            System::rm(" -rf {$d}");
        }

        _YB('cache.options', $this->_backups_YB['cache_options']);

        ob_start();
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
