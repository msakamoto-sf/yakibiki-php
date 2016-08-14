<?php
/*
 *   Copyright (c) 2009 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
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

$_dir = _YB('dir.plugin.wiki');
require_once(realpath($_dir . '/yb_plugin_wiki_amazon.php'));

class plugins_wiki_amazon_TestCase extends UnitTestCase
{
    // {{{ test_parse_text()

    function test_parse_text()
    {
        $src = <<<HTML
<A Href="url1" onclick="aabb">bookname</A>
<IMG Src="url2" width="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'text');
        $this->assertEqual($r['attr']['a_href'], 'url1');
        $this->assertEqual($r['attr']['text'], 'bookname');
        $this->assertEqual($r['attr']['img_src'], 'url2');

        $src = <<<HTML
<a href="url1" onclick="aabb">bookname</a>
<img src="url2" width="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'text');
        $this->assertEqual($r['attr']['a_href'], 'url1');
        $this->assertEqual($r['attr']['text'], 'bookname');
        $this->assertEqual($r['attr']['img_src'], 'url2');

        $src = <<<HTML
<a href=""url1"">book<b>name</b><script>alert(0);</script></a>
<img src="url2" width="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'text');
        $this->assertEqual($r['attr']['a_href'], '');
        $this->assertEqual($r['attr']['text'], 'book');
        $this->assertEqual($r['attr']['img_src'], 'url2');

        $src = <<<HTML
<a href="url1">book<b>name</b><script>alert(0);</script></a>
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertNull($r);

        $src = <<<HTML
<a href="url1">book <img src="url2" width="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
</a>
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertNull($r);

        $src = <<<HTML
<A HREF="url1">book<script>name</A>
<IMG SRC="url2" WIDTH="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'text');
        $this->assertEqual($r['attr']['a_href'], 'url1');
        $this->assertEqual($r['attr']['text'], 'book');
        $this->assertEqual($r['attr']['img_src'], 'url2');
    }

    // }}}
    // {{{ test_parse_iframe()

    function test_parse_iframe()
    {
        $src = <<<HTML
<iframe src="url" style="width:120px;height:240px;" 
scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'iframe');
        $this->assertEqual($r['attr']['src'], 'url');

        $src = <<<HTML
<IFrame Src="url" style="width:120px;height:240px;" 
scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'iframe');
        $this->assertEqual($r['attr']['src'], 'url');

        $src = <<<HTML
<iframe src="url" style="width:120px;height:240px;" 
scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>
<a href=""url1"">book<b>name</b><script>alert(0);</script></a>
<img src="url2" width="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'iframe');
        $this->assertEqual($r['attr']['src'], 'url');

        $src = <<<HTML
<iframe src="url" style="width:120px;height:240px;" 
onClick="hogehoge" 
scrolling="no" marginwidth="0" marginheight="0" frameborder="0">
foobar
</iframe>
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertEqual($r['type'], 'iframe');
        $this->assertEqual($r['attr']['src'], 'url');

        $src = <<<HTML
<iframe src="url" style="width:120px;height:240px;" 
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertNull($r);

        $src = <<<HTML
<iframe src="url" style="width:120px;height:240px;">
HTML;
        $r = _yb_plugin_wiki_amazon_parse($src);
        $this->assertNull($r);
    }

    // }}}
    // {{{ test_weak_url_check()

    function test_weak_url_check()
    {
        $r = _yb_plugin_wiki_amazon_weak_url_check(
            'http://www.amazon.com');
        $this->assertTrue($r);

        $r = _yb_plugin_wiki_amazon_weak_url_check(
            'http://www.foo-amazon.com');
        $this->assertTrue($r);

        $r = _yb_plugin_wiki_amazon_weak_url_check(
            'http://foo-amazon.com');
        $this->assertTrue($r);

        $r = _yb_plugin_wiki_amazon_weak_url_check(
            'https://foo-amazon.com');
        $this->assertFalse($r);

        $r = _yb_plugin_wiki_amazon_weak_url_check(
            'http://www.google.com/');
        $this->assertFalse($r);

        $r = _yb_plugin_wiki_amazon_weak_url_check(
            'javascript:alert(0)');
        $this->assertFalse($r);
    }

    // }}}
    // {{{ test_format()

    function test_format()
    {
        $r = _yb_plugin_wiki_amazon_format(null);
        $this->assertEqual($r, '');

        $d = array();
        $r = _yb_plugin_wiki_amazon_format(null);
        $this->assertEqual($r, '');

        $d = array('type' => 'foobar');
        $r = _yb_plugin_wiki_amazon_format(null);
        $this->assertEqual($r, '');

        $d = array(
            'type' => 'text',
            'attr' => array(
                'a_href' => 'http://www.amazon.com/foo&lt;&gt;',
                'text' => '&#039;bookname',
                'img_src' => 'http://www.amazon.com/bar&quot;',
            ));
        $r = _yb_plugin_wiki_amazon_format($d);
        $c = '<a href="http://www.amazon.com/foo&lt;&gt;">&#039;bookname</a><img src="http://www.amazon.com/bar&quot;" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';
        $this->assertEqual($r, $c);

        // url is not amazon
        $d = array(
            'type' => 'text',
            'attr' => array(
                'a_href' => 'http://www.google.com/', 
                'text' => '&#039;bookname',
                'img_src' => 'http://www.amazon.com/bar&quot;',
            ));
        $r = _yb_plugin_wiki_amazon_format($d);
        $this->assertEqual($r, '');

        // url is not amazon
        $d = array(
            'type' => 'text',
            'attr' => array(
                'a_href' => 'http://www.amazon.com/foo&lt;&gt;',
                'text' => '&#039;bookname',
                'img_src' => 'http://www.google.com/', 
            ));
        $r = _yb_plugin_wiki_amazon_format($d);
        $this->assertEqual($r, '');

        $d = array(
            'type' => 'iframe',
            'attr' => array(
                'src' => 'http://www.amazon.com/foo&lt;&gt;&#039;&quot;',
            ));
        $r = _yb_plugin_wiki_amazon_format($d);
        $c = '<iframe src="http://www.amazon.com/foo&lt;&gt;&#039;&quot;" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>';
        $this->assertEqual($r, $c);

        // url is not amazon
        $d = array(
            'type' => 'iframe',
            'attr' => array(
                'src' => 'http://www.google.com/',
            ));
        $r = _yb_plugin_wiki_amazon_format($d);
        $this->assertEqual($r, '');

    }

    // }}}
    // {{{ test_textlink()

    function test_textlink()
    {
        $wctx =& new wiki_Context(100, 'Test Page');
        $p1 = '';
        $p2 = <<<HTML
<a href="http://www.amazon.com/foobar">bookname</a>
<img src="http://www.assoc-amazon.com/barbuz" width="1" height="1" 
border="0" alt="" style="border:none !important; margin:0px !important;" />
HTML;
        $c = '<a href="http://www.amazon.com/foobar">bookname</a><img src="http://www.assoc-amazon.com/barbuz" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';

        $r = yb_plugin_wiki_amazon_invoke_inline($p1, $p2, $wctx);
        $this->assertEqual($r, $c);
        $r = yb_plugin_wiki_amazon_invoke_block($p1, $p2, $wctx);
        $this->assertEqual($r, $c);
    }

    // }}}
    // {{{ test_iframelink()

    function test_iframelink()
    {
        $wctx =& new wiki_Context(100, 'Test Page');
        $p1 = '';
        $p2 = <<<HTML
<iframe src="http://rcm-jp.amazon.co.jp/e/foobar" 
style="width:120px;height:240px;" 
scrolling="no" marginwidth="0" marginheight="0" frameborder="0">
</iframe>
HTML;
        $c = '<iframe src="http://rcm-jp.amazon.co.jp/e/foobar" style="width:120px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe>';
        $r = yb_plugin_wiki_amazon_invoke_inline($p1, $p2, $wctx);
        $this->assertEqual($r, $c);
        $r = yb_plugin_wiki_amazon_invoke_block($p1, $p2, $wctx);
        $this->assertEqual($r, $c);
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
