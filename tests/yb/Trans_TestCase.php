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

require_once('yb/Trans.php');

class yb_Trans_TestCase extends UnitTestCase
{
    // {{{ test_available()

    function test_available()
    {
        $backup = _YB('dir.locale', dirname(__FILE__) . '/test_Trans');

        $availables = yb_Trans::availableLocales();
        $this->assertEqual(count($availables), 3);
        $this->assertTrue(in_array('test.locale1', $availables));
        $this->assertTrue(in_array('test.locale2', $availables));
        $this->assertTrue(in_array('foobar', $availables));

        _YB('dir.locale', $backup);
    }

    // }}}
    // {{{ test_factory()

    function test_factory()
    {
        $backup = _YB('dir.locale', dirname(__FILE__) . '/test_Trans');

        $ybt =& yb_Trans::factory('test.locale1');
        $this->assertEqual($ybt->locale(), 'test.locale1');

        $ybt =& yb_Trans::factory('test.locale2');
        $this->assertEqual($ybt->locale(), 'test.locale2');

        $ybt =& yb_Trans::factory('test.locale4');
        $this->assertNull($ybt);

        $ybt =& yb_Trans::factory('test.locale1');
        $this->assertEqual($ybt->locale(), 'test.locale1');

        _YB('dir.locale', $backup);
    }

    // }}}
    // {{{ test_t_without_domain_specified()

    function test_t_without_domain_specified()
    {
        $backup = _YB('dir.locale', dirname(__FILE__) . '/test_Trans');

        $ybt =& yb_Trans::factory('test.locale1');

        // {{{ domain1
        $this->assertEqual($ybt->t('ABC'), 'Translated ABC');

        $this->assertEqual($ybt->t('ABC', array()), 'Translated ABC');

        $this->assertEqual($ybt->t('ABC', array('key1' => 1)), 
            'Translated ABC');

        $this->assertEqual($ybt->t('DEF %key1 %key2'), 
            'Translated %key1 , %key2');

        $this->assertEqual(
            $ybt->t('DEF %key1 %key2', array('key1' => 'val1')), 
            'Translated val1 , %key2');

        $this->assertEqual(
            $ybt->t('DEF %key1 %key2', 
                array('key1' => 'val1', 'key2' => 123)), 
            'Translated val1 , 123');

        $this->assertEqual(
            $ybt->t('DEF %key1 %key2', 
                array('key1' => 'val - %key1 / %key2 ;', 'key2' => 123)), 
            'Translated val - %key1 / 123 ; , 123');

        $this->assertEqual(
            $ybt->t('DEF %key1 %key2', 
                array('key1' => 123, 'key2' => 'val - %key1 / %key2 ;')), 
            'Translated 123 , val - %key1 / %key2 ;');

        $this->assertEqual(
            $ybt->t("GHI\n\nJKL"), 
            "Translated GHI.\r\n\r\nTranslated JKL.");

        $this->assertEqual(
            $ybt->t("\r\nGHI\r\n\r\nJKL\r\n"), 
            "Translated GHI.\r\n\r\nTranslated JKL.");

        $this->assertEqual($ybt->t('MNO'), 'MNO');
        // }}}
        // {{{ domain2
        $this->assertEqual($ybt->t('line1'), 'line1');
        $this->assertEqual($ybt->t('line2'), 'Translated line 2');
        // }}}

        _YB('dir.locale', $backup);
    }

    // }}}
    // {{{ test_t_without_domain_specify()

    function test_t_without_domain_specify()
    {
        $backup = _YB('dir.locale', dirname(__FILE__) . '/test_Trans');

        $ybt =& yb_Trans::factory('test.locale1');

        // {{{ domain1
        $this->assertEqual($ybt->t('ABC', null, 'domain1'), 'Translated ABC');

        $this->assertEqual(
            $ybt->t('DEF %key1 %key2', 
                array('key1' => 'val1', 'key2' => 123), 
                'domain1'), 
            'Translated val1 , 123');

        $this->assertEqual($ybt->t('line1', null, 'domain1'), 'line1');
        $this->assertEqual($ybt->t('line2', null, 'domain1'), 'line2');

        // }}}
        // {{{ domain2
        $this->assertEqual($ybt->t('ABC', null, 'domain2'), 'ABC');
        $this->assertEqual(
            $ybt->t('DEF %key1 %key2', 
                array('key1' => 'val1', 'key2' => 123), 
                'domain2'), 
            'DEF val1 123');

        $this->assertEqual($ybt->t('line1', null, 'domain2'), 'line1');
        $this->assertEqual($ybt->t('line2', null, 'domain2'), 
            'Translated line 2');
        // }}}

        _YB('dir.locale', $backup);
    }

    // }}}
    // {{{ test_t_with_multi_locale()

    function test_t_with_multi_locale()
    {
        $backup = _YB('dir.locale', dirname(__FILE__) . '/test_Trans');

        $yb1 =& yb_Trans::factory('test.locale1');
        $yb2 =& yb_Trans::factory('test.locale2');


        $this->assertEqual($yb1->t('ABC'), 'Translated ABC');
        $this->assertEqual($yb2->t('ABC'), 'Translated ABC with locale 2.');

        $this->assertEqual(
            $yb1->t('DEF %key1 %key2', 
                array('key1' => 'val1', 'key2' => 123)), 
            'Translated val1 , 123');

        $this->assertEqual(
            $yb2->t('DEF %key1 %key2', 
                array('key1' => 'val1', 'key2' => 123)), 
            'Translated val1 , 123 in locale2.');


        _YB('dir.locale', $backup);
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
