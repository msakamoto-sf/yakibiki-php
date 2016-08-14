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

require_once('yb/Util.php');

class yb_Util_TestCase extends UnitTestCase
{

    // {{{ test_hook()

    function test_hook()
    {
        $_old_dir = _YB('dir.plugin.hook', 
            dirname(__FILE__) . '/test_Hook_Plugins');

        // 'test' hook returns $arg1 * $arg2.
        // $arg3 is set to $arg1 + $arg2.
        $ans = 0;
        $s = yb_Util::hook('test', array(10, 20, &$ans));
        $this->assertEqual($s, 200);
        $this->assertEqual($ans, 30);

        // 'my_test' hook returns $arg1 + $arg2.
        // $arg3 is set to $arg1 * $arg2.
        $_old_conv = _YB('hook.convert.test', 'my_test');
        $ans = 0;
        $s = yb_Util::hook('test', array(10, 20, &$ans));
        $this->assertEqual($s, 30);
        $this->assertEqual($ans, 200);

        _YB('dir.plugin.hook', $_old_dir);
        _YB('hook.convert.test', $_old_conv);
    }

    // }}}
    // {{{ test_make_url()

    function test_make_url()
    {
        $base = _YB('url');
        $_old_index_file = _YB('index_file');
        _YB('index_file', '');
        $default_mdl = _YB('default.module');

        $q = array();
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base);
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base);

        _YB('index_file', 'foobar.html');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . 'foobar.html');
        _YB('index_file', '');

        $q = array('key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . h('?key1=123&key2=a%2Fb%20c'));
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . h('?key1=123&key2=a/b c'));

        _YB('index_file', 'index.php');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . h('index.php?key1=123&key2=a%2Fb%20c'));
        _YB('index_file', '');

        $q = array('mdl' => 'user');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . '?mdl=user');
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . '?mdl=user');

        $q = array('mdl' => 'user', 'key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . h('?mdl=user&key1=123&key2=a%2Fb%20c'));
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . h('?mdl=user&key1=123&key2=a/b c'));

        $q = array('mdl' => $default_mdl);
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base);
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base);

        $q = array('mdl' => 'view', 
            'key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . h('?key1=123&key2=a%2Fb%20c'));
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . h('?key1=123&key2=a/b c'));

        $q = array('mdl' => $default_mdl, 'id' => 1234);
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . '?id=1234');
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . '?id=1234');

        $q = array('mdl' => $default_mdl, 'id' => 1234,
            'key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . h('?id=1234&key1=123&key2=a%2Fb%20c'));
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . h('?id=1234&key1=123&key2=a/b c'));

        $q = array('mdl' => 'raw', 'id' => '1234');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . '?mdl=raw&amp;id=1234');
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . '?mdl=raw&amp;id=1234');

        /** NOT IN USE
        $q = array('mdl' => 'yakibiki', 'id' => '1234.html',
            'key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . '1234.html/?key1=123&key2=a%2Fb%20c');
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . '1234.html/?key1=123&key2=a/b c');
        **/

        $q = array('mdl' => $default_mdl, 'id' => 1234, 'action' => 'info');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . '?id=1234&amp;action=info');
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . '?id=1234&amp;action=info');

        $q = array('mdl' => $default_mdl, 'id' => 1234, 'action' => 'info',
            'key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::make_url($q);
        $this->assertEqual($url, $base . h('?id=1234&action=info&key1=123&key2=a%2Fb%20c'));
        $url = yb_Util::make_url($q, false);
        $this->assertEqual($url, $base . h('?id=1234&action=info&key1=123&key2=a/b c'));

        _YB('index_file', $_old_index_file);
    }

    // }}}
    // {{{ test_redirect_url()

    function test_redirect_url()
    {
        $base = _YB('url');
        $_old_index_file = _YB('index_file');
        _YB('index_file', '');
        $default_mdl = _YB('default.module');

        $q = array();
        $url = yb_Util::redirect_url($q);
        $this->assertEqual($url, $base);
        $url = yb_Util::redirect_url($q, false);
        $this->assertEqual($url, $base);

        _YB('index_file', 'foobar.html');
        $url = yb_Util::redirect_url($q);
        $this->assertEqual($url, $base . 'foobar.html');
        _YB('index_file', '');

        $q = array('key1' => 123, 'key2' => 'a/b c');
        $url = yb_Util::redirect_url($q);
        $this->assertEqual($url, $base . '?key1=123&key2=a%2Fb%20c');
        $url = yb_Util::redirect_url($q, false);
        $this->assertEqual($url, $base . '?key1=123&key2=a/b c');

        _YB('index_file', 'index.php');
        $url = yb_Util::redirect_url($q);
        $this->assertEqual($url, $base . 'index.php?key1=123&key2=a%2Fb%20c');
        _YB('index_file', '');

        _YB('index_file', $_old_index_file);
    }

    // }}}
    // {{{ test_array_remove_empty_string()

    function test_array_remove_empty_string()
    {
        $arr = array("", '', 0, "0", "", null, -1);
        $result = yb_Util::array_remove_empty_string($arr);
        $this->assertEqual(count($result), 4);
        $this->assertIdentical($result[0], 0);
        $this->assertIdentical($result[1], "0");
        $this->assertIdentical($result[2], null);
        $this->assertIdentical($result[3], -1);

    }

    // }}}
    // {{{ test_array_or()

    function test_array_or()
    {
        $a1 = array(1, 2);
        $a2 = array(3, 4);
        $a3 = array(1, 4);
        $a4 = array(2, 3);
        $a5 = array(1, 5);
        $result = yb_Util::array_or($a1, $a2, $a3, $a4, $a5);

        sort($result);
        $this->assertEqual(count($result), 5);
        $this->assertIdentical($result[0], 1);
        $this->assertIdentical($result[1], 2);
        $this->assertIdentical($result[2], 3);
        $this->assertIdentical($result[3], 4);
        $this->assertIdentical($result[4], 5);

        $result = yb_Util::array_or();
        $this->assertTrue(is_array($result));
        $this->assertEqual(count($result), 0);

        $a1 = array(1, 4, 5);
        $result = yb_Util::array_or($a1);
        $this->assertTrue(is_array($result));
        $this->assertEqual(count($result), 3);
        $this->assertTrue(in_array(1, $result));
        $this->assertTrue(in_array(4, $result));
        $this->assertTrue(in_array(5, $result));
    }

    // }}}
    // {{{ test_array_and()

    function test_array_and()
    {
        $a1 = array(1, 2, 3, 4, 6, 8, 10);
        $a2 = array(2, 3, 5, 7, 8, 9);
        $a3 = array(2, 4, 5, 8, 10);
        $result = yb_Util::array_and($a1, $a2, $a3);
        sort($result);
        $this->assertEqual(count($result), 2);
        $this->assertIdentical($result[0], 2);
        $this->assertIdentical($result[1], 8);

        $result = yb_Util::array_and();
        $this->assertTrue(is_array($result));
        $this->assertEqual(count($result), 0);

        $a1 = array(1, 4, 5);
        $result = yb_Util::array_and($a1);
        $this->assertTrue(is_array($result));
        $this->assertEqual(count($result), 3);
        $this->assertTrue(in_array(1, $result));
        $this->assertTrue(in_array(4, $result));
        $this->assertTrue(in_array(5, $result));
    }

    // }}}
    // {{{ test_encode_ctrl_char()

    function test_encode_ctrl_char()
    {
        $data = 'ABC';
        $this->assertEqual(yb_Util::encode_ctrl_char($data), $data);

        $data = 'A B C' . "\r";
        $this->assertEqual(yb_Util::encode_ctrl_char($data), 
            'A B C\r');

        $data = ' ABC ' . "\n";
        $this->assertEqual(yb_Util::encode_ctrl_char($data), 
            ' ABC \n');

        $data = ' A B C ' . "\r\n";
        $this->assertEqual(yb_Util::encode_ctrl_char($data), 
            ' A B C \r\n');

        $data = 'ABC' . "\t" . 'DEF';
        $this->assertEqual(yb_Util::encode_ctrl_char($data), 
            'ABC\tDEF');

        $data = 'ABC' . "\\\t" . 'DEF';
        $this->assertEqual(yb_Util::encode_ctrl_char($data), 
            'ABC' . "\\" . '\tDEF');

        $data = "\\" . "\t" . "\r\n" . 'A"C' . "\\\t" . "D'F" . "\\\r\n";
        $this->assertEqual(yb_Util::encode_ctrl_char($data), 
            "\\" . '\t\r\nA"C' . "\\" . '\tD\'F' . "\\" . '\r\n');


    }

    // }}}
    // {{{ test_decode_ctrl_char()

    function test_decode_ctrl_char()
    {
        $data = 'ABC';
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

        $data = 'A B C' . "\r";
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

        $data = ' ABC ' . "\n";
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

        $data = ' A B C ' . "\r\n";
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

        $data = 'ABC' . "\t" . 'DEF';
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

        $data = 'ABC' . "\\\t" . 'DEF';
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

        $data = "\\" . "\t" . "\r\n" . 'A"C' . "\\\t" . "D'F" . "\\\r\n";
        $this->assertEqual($data, yb_Util::decode_ctrl_char(
            yb_Util::encode_ctrl_char($data)));

    }

    // }}}
    // {{{ test_url_parse()

    function test_url_parse()
    {
        $url = 'http://www.sample.net/';
        $p = yb_Util::url_parse($url);
        $this->assertEqual($p['scheme'], 'http');
        $this->assertEqual($p['host'], 'www.sample.net');
        $this->assertEqual($p['port'], 80);
        $this->assertEqual($p['user'], '');
        $this->assertEqual($p['pass'], '');
        $this->assertEqual($p['path'], '/');
        $this->assertEqual($p['query'], '');
        $this->assertEqual($p['fragment'], '');

    }

    // }}}
    // {{{ test_hash_password()

    function test_hash_password()
    {
        $old_f = _YB('password.hash.func', 'md5');
        $old_s = _YB('password.salt', 'test');

        // md5
        $this->assertEqual(yb_Util::hash_password('abc'), md5('tsetabctest'));

        // sha1
        _YB('password.hash.func', 'sha1');
        $this->assertEqual(yb_Util::hash_password('def'), sha1('tsetdeftest'));

        // (undefined)
        _YB('password.hash.func', 'undefined_func');
        $this->assertEqual(yb_Util::hash_password('tsetghitest'), 'tsetghitest');

        _YB('password.hash.func', $old_f);
        _YB('password.salt', $old_s);
    }

    // }}}
    // {{{ test_resolvepath()

    function test_resolvepath()
    {
        $s = 'hoge';
        $r = 'hoge';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = '/hoge';
        $r = 'hoge';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = '/./hoge';
        $r = 'hoge';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = '/./hoge/bohe';
        $r = 'hoge/bohe';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = './hoge/bohe';
        $r = 'hoge/bohe';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = './hoge/bohe/../mohe';
        $r = 'hoge/mohe';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = './hoge/bohe/../mohe/../moga';
        $r = 'hoge/moga';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = './hoge/bohe/../mohe/../moga/./././';
        $r = 'hoge/moga';
        $this->assertEqual(yb_Util::resolvepath($s), $r);

        $s = './hoge/bohe';
        $b = '/foo/';
        $r = 'foo/hoge/bohe';
        $this->assertEqual(yb_Util::resolvepath($s, $b), $r);

        $s = '../hoge///../../bohe';
        $b = '/foo/./bar/baz/../abc/./././////';
        $r = 'foo/bohe';
        $this->assertEqual(yb_Util::resolvepath($s, $b), $r);
    }

    // }}}
    // {{{ test_extract_dataname_with_re()

    function test_extract_dataname_with_re()
    {
        $s = 'ABC';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, $s);

        $s = 'Re:';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'Re:');

        $s = '(1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, '(1)');

        $s = 're:(1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, '(1)');

        $s = 're:  (1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, ' ');

        $s = "re:\t(1)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, "\t");

        $s = "re:\t\t\t(1)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, "\t");

        $s = "re:\t \t(1)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, "\t");

        $s = "re:\t \t \t \t(1)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, "\t");

        $s = 're:ABC';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 'Re: ABC';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = "rE: \t  ABC";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 'RE:ABC(1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 're:ABC(111)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 'Re:ABC (1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 'rE:ABC (11)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = "RE:ABC \t (11)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 're: ABC(1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = "Re: \t ABC(111)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = 're: ABC (1)';
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = "Re: \t ABC \t (11)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, 'ABC');

        $s = "Re: \t ABC \t DEF \t (11)";
        $base = yb_Util::extract_dataname_with_re($s);
        $this->assertEqual($base, "ABC \t DEF");
    }

    // }}}
    // {{{ test_checkdatetime()

    function test_checkdatetime()
    {
        $this->assertFalse(yb_Util::checkdatetime(
            "1999", "02", "29"));
        $this->assertTrue(yb_Util::checkdatetime(
            "2000", "02", "29"));
        $this->assertFalse(yb_Util::checkdatetime(
            "2001", "02", "29"));
        $this->assertTrue(yb_Util::checkdatetime(
            "2004", "02", "29"));
        $this->assertTrue(yb_Util::checkdatetime(
            "2000", "02", "29", "00", "00", "00"));
        $this->assertTrue(yb_Util::checkdatetime(
            "2000", "02", "28", "23", "59", "59"));
        $this->assertFalse(yb_Util::checkdatetime(
            "2000", "02", "28", "23", "59", "60"));
        $this->assertFalse(yb_Util::checkdatetime(
            "2000", "02", "28", "23", "60", "60"));
        $this->assertFalse(yb_Util::checkdatetime(
            "2000", "02", "28", "24", "60", "60"));
    }

    // }}}
    // {{{ test_explode_for_autols()

    function test_explode_for_autols()
    {
        $r = yb_Util::explode_for_autols('');
        $this->assertEqual(count($r), 0);

        $r = yb_Util::explode_for_autols("/");
        $this->assertEqual($r[0][0], '/');
        $this->assertEqual($r[0][1], '/');

        $r = yb_Util::explode_for_autols("abc");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');

        $r = yb_Util::explode_for_autols("//");
        $this->assertEqual($r[0][0], '/');
        $this->assertEqual($r[0][1], '/');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], '//');

        $r = yb_Util::explode_for_autols("///");
        $this->assertEqual($r[0][0], '/');
        $this->assertEqual($r[0][1], '/');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], '//');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], '///');

        $r = yb_Util::explode_for_autols("abc/");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');

        $r = yb_Util::explode_for_autols("/abc");
        $this->assertEqual($r[0][0], '/');
        $this->assertEqual($r[0][1], '/');
        $this->assertEqual($r[1][0], 'abc');
        $this->assertEqual($r[1][1], '/abc');

        $r = yb_Util::explode_for_autols("abc/d");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');
        $this->assertEqual($r[2][0], 'd');
        $this->assertEqual($r[2][1], 'abc/d');

        $r = yb_Util::explode_for_autols("/abc/d");
        $this->assertEqual($r[0][0], '/');
        $this->assertEqual($r[0][1], '/');
        $this->assertEqual($r[1][0], 'abc');
        $this->assertEqual($r[1][1], '/abc');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], '/abc/');
        $this->assertEqual($r[3][0], 'd');
        $this->assertEqual($r[3][1], '/abc/d');

        $r = yb_Util::explode_for_autols("abc/d/efg");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');
        $this->assertEqual($r[2][0], 'd');
        $this->assertEqual($r[2][1], 'abc/d');
        $this->assertEqual($r[3][0], '/');
        $this->assertEqual($r[3][1], 'abc/d/');
        $this->assertEqual($r[4][0], 'efg');
        $this->assertEqual($r[4][1], 'abc/d/efg');

        $r = yb_Util::explode_for_autols(" abc\t/ d\t /\t efg");
        $this->assertEqual($r[0][0], " abc\t");
        $this->assertEqual($r[0][1], " abc\t");
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], " abc\t/");
        $this->assertEqual($r[2][0], " d\t ");
        $this->assertEqual($r[2][1], " abc\t/ d\t ");
        $this->assertEqual($r[3][0], '/');
        $this->assertEqual($r[3][1], " abc\t/ d\t /");
        $this->assertEqual($r[4][0], "\t efg");
        $this->assertEqual($r[4][1], " abc\t/ d\t /\t efg");

        $r = yb_Util::explode_for_autols("abc//");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], 'abc//');

        $r = yb_Util::explode_for_autols("abc//d");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], 'abc//');
        $this->assertEqual($r[3][0], 'd');
        $this->assertEqual($r[3][1], 'abc//d');

        $r = yb_Util::explode_for_autols("abc///");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], 'abc//');
        $this->assertEqual($r[3][0], '/');
        $this->assertEqual($r[3][1], 'abc///');

        $r = yb_Util::explode_for_autols("abc///de");
        $this->assertEqual($r[0][0], 'abc');
        $this->assertEqual($r[0][1], 'abc');
        $this->assertEqual($r[1][0], '/');
        $this->assertEqual($r[1][1], 'abc/');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], 'abc//');
        $this->assertEqual($r[3][0], '/');
        $this->assertEqual($r[3][1], 'abc///');
        $this->assertEqual($r[4][0], 'de');
        $this->assertEqual($r[4][1], 'abc///de');

        $r = yb_Util::explode_for_autols("/abc//d");
        $this->assertEqual($r[0][0], '/');
        $this->assertEqual($r[0][1], '/');
        $this->assertEqual($r[1][0], 'abc');
        $this->assertEqual($r[1][1], '/abc');
        $this->assertEqual($r[2][0], '/');
        $this->assertEqual($r[2][1], '/abc/');
        $this->assertEqual($r[3][0], '/');
        $this->assertEqual($r[3][1], '/abc//');
        $this->assertEqual($r[4][0], 'd');
        $this->assertEqual($r[4][1], '/abc//d');

    }

    // }}}
    // {{{ test_check_css_color()

    function test_check_css_color()
    {
        $r = yb_Util::check_css_color('');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_color('   ');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_color('Red');
        $this->assertEqual('Red', $r);

        $r = yb_Util::check_css_color(" re\r\nd ");
        $this->assertEqual('red', $r);

        $r = yb_Util::check_css_color(' WindowText ');
        $this->assertEqual('WindowText', $r);

        $colors = array('#012345', '#6789AB', '#CDEFab', '#cdef00');
        foreach ($colors as $c) {
            $r = yb_Util::check_css_color($c);
            $this->assertEqual($c, $r);
        }

        $r = yb_Util::check_css_color(' #cdefgh ');
        $this->assertEqual('', $r);

        $colors = array(
            '#012', '#345', '#678', '#9AB', '#CDE', '#FFF', '#abc', '#def');
        foreach ($colors as $c) {
            $r = yb_Util::check_css_color($c);
            $this->assertEqual($c, $r);
        }

        $r = yb_Util::check_css_color(' #fgh ');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_color(' #ffff ');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_color(" RGB(\r\n0,  12,  999)  ");
        $this->assertEqual('rgb(0,12,999)', $r);

        $colors = array('rgb(0,0,0)', 'rgb(255,255,255)');
        foreach ($colors as $c) {
            $r = yb_Util::check_css_color($c);
            $this->assertEqual($c, $r);
        }

        $r = yb_Util::check_css_color('rgb(0x39, #x932, aaa)');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_color(" RGB(\r\n0%,  12%,  999%)  ");
        $this->assertEqual('rgb(0%,12%,999%)', $r);

        $colors = array('rgb(0%,0%,0%)', 'rgb(100%,100%,100%)');
        foreach ($colors as $c) {
            $r = yb_Util::check_css_color($c);
            $this->assertEqual($c, $r);
        }

        $r = yb_Util::check_css_color('rgb(0x39%, #x932%, aaa%)');
        $this->assertEqual('', $r);

    }

    // }}}
    // {{{ test_check_css_size()

    function test_check_css_size()
    {
        $r = yb_Util::check_css_size('');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_size('   ');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_size("  xx-\r\n large ");
        $this->assertEqual('xx-large', $r);

        $r = yb_Util::check_css_size('MediUm');
        $this->assertEqual('medium', $r);

        $keywords = array(
            'xx-large',
            'x-large',
            'large',
            'medium',
            'small',
            'x-small',
            'xx-small',
            'larger',
            'smaller',
        );
        foreach ($keywords as $k) {
            $r = yb_Util::check_css_size($k);
            $this->assertEqual($k, $r);
        }

        $r = yb_Util::check_css_size(' small(); ');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_size(' alphabet ');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_size(' small; abc');
        $this->assertEqual('', $r);

        $r = yb_Util::check_css_size('10PT');
        $this->assertEqual('10pt', $r);

        $r = yb_Util::check_css_size("  1\r0\n 0 mm");
        $this->assertEqual('100mm', $r);

        $units = array('em', 'ex', 'px', 'pt', 'pc', 'mm', 'cm', 'in', '%');
        foreach ($units as $u) {
            $r = yb_Util::check_css_size('100.0'.$u);
            $this->assertEqual('100.0'.$u, $r);
        }

        $r = yb_Util::check_css_size(' 10px; abc');
        $this->assertEqual('', $r);

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
