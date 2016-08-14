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
require_once('wiki/Parser.php');

class wiki_Parser_TestCase extends UnitTestCase
{
    var $_old_wiki_plugin_dir = "";

    // {{{ setUp()

    function setUp()
    {
        $this->_old_wiki_plugin_dir = _YB('dir.plugin.wiki', 
            dirname(__FILE__) . '/test_Html_Plugins');
    }

    // }}}
    // {{{ tearDown()

    function tearDown()
    {
        _YB('dir.plugin.wiki', $this->_old_wiki_plugin_dir);
    }

    // }}}
    // {{{ test_wiki_t_url()

    function test_wiki_t_url()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $r =& new wiki_T_URL("source text", $wctx);
        $this->assertEqual($r->_source, $src);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);

        // getdistance()
        // {{{ #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_URL::getdistance($src, $wctx));
        // }}}
        // {{{ #2
        $src = "abc http://sample.com/ test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_URL::getdistance($src, $wctx));
        // }}}
        // {{{ #3
        $src = "";
        $this->assertEqual(0, 
            wiki_T_URL::getdistance($src, $wctx));
        // }}}

        // parse
        // {{{ #1
        $src = "http://sample.com/ test\ntext\n";
        $r =& wiki_T_URL::parse($src, $wctx);
        $this->assertEqual($r->geturl(), "http://sample.com/");
        $this->assertEqual($r->_source, "http://sample.com/");
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, " test\ntext\n");
        // }}}
        // {{{ #2
        $src = "http://sample.com/test\ntext\n";
        $r =& wiki_T_URL::parse($src, $wctx);
        $this->assertEqual($r->geturl(), "http://sample.com/test");
        $this->assertEqual($r->_source, "http://sample.com/test");
        $this->assertEqual($src, "\ntext\n");
        // }}}
        // {{{ #3
        $src = "abc http://sample.com/test\ntext\n";
        $r =& wiki_T_URL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc http://sample.com/test\ntext\n");
        // }}}
        // {{{ #4
        $src = "test\ntext\n";
        $r =& wiki_T_URL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "test\ntext\n");
        // }}}
    }

    // }}}
    // {{{ test_wiki_t_mail()

    function test_wiki_t_mail()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $r =& new wiki_T_Mail("source text", $wctx);
        $this->assertEqual($r->_source, $src);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);

        // getdistance()
        // {{{ #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Mail::getdistance($src, $wctx));
        // }}}
        // {{{ #2
        $src = "abc user@sample.com test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_Mail::getdistance($src, $wctx));
        // }}}
        // {{{ #3
        $src = "";
        $this->assertEqual(0, 
            wiki_T_Mail::getdistance($src, $wctx));
        // }}}

        // parse
        // {{{ #1
        $src = "user@sample.com test\ntext\n";
        $r =& wiki_T_Mail::parse($src, $wctx);
        $this->assertEqual($r->getaddress(), "user@sample.com");
        $this->assertEqual($r->_source, "user@sample.com");
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, " test\ntext\n");
        // }}}
        // {{{ #2
        $src = "user@sample.com.test\ntext\n";
        $r =& wiki_T_Mail::parse($src, $wctx);
        $this->assertEqual($r->getaddress(), "user@sample.com.test");
        $this->assertEqual($r->_source, "user@sample.com.test");
        $this->assertEqual($src, "\ntext\n");
        // }}}
        // {{{ #3
        $src = "abc user@sample.com test\ntext\n";
        $r =& wiki_T_Mail::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc user@sample.com test\ntext\n");
        // }}}
        // {{{ #4
        $src = "test\ntext\n";
        $r =& wiki_T_Mail::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "test\ntext\n");
        // }}}
    }

    // }}}
    // {{{ test_wiki_t_bracket()

    function test_wiki_t_bracket()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $pagename = "Link Page Name";
        $alias = "Link Alias";
        $r =& new wiki_T_BracketName($src, $wctx, $pagename, $alias);
        $this->assertEqual($r->_source, $src);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($r->getpagename(), $pagename);
        $this->assertEqual($r->getalias(), $alias);

        // {{{ getdistance()
        // #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #2
        $src = "abc [[BracketName]] test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #3
        $src = "abc [[BracketName] test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #4
        $src = "abc [BracketName]] test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #5
        $src = "abc [BracketName] test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #6
        $src = "";
        $this->assertEqual(0, 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #7
        $src = "abc [[BracketName1]] [[BracketName2]] test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_BracketName::getdistance($src, $wctx));

        // #8
        $src = "abc [[PageName>AliasName]] [[BracketName2]] test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_BracketName::getdistance($src, $wctx));

        // }}}

        // {{{ parse()
        // #1
        $src = "[[Bracket Name]] test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Bracket Name");
        $this->assertEqual($r->getalias(), "");
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, " test\ntext\n");

        // #2
        $src = "[[Bracket Name1]][[Bracket Name2]]test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Bracket Name1");
        $this->assertEqual($r->getalias(), "");
        $this->assertEqual($src, "[[Bracket Name2]]test\ntext\n");

        // #3
        $src = "abc [[BracketName]] test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc [[BracketName]] test\ntext\n");

        // #4
        $src = "test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "test\ntext\n");

        // #5
        $src = "[[Alias Name > Page Name]] test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Page Name");
        $this->assertEqual($r->getalias(), "Alias Name");
        $this->assertEqual($src, " test\ntext\n");

        // #5-2
        $src = "[[Alias Name>Page Name]] test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Page Name");
        $this->assertEqual($r->getalias(), "Alias Name");
        $this->assertEqual($src, " test\ntext\n");

        // #6
        $src = "[[Alias Name > Page Name]][[Bracket Name]]test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Page Name");
        $this->assertEqual($r->getalias(), "Alias Name");
        $this->assertEqual($src, "[[Bracket Name]]test\ntext\n");

        // #6-2
        $src = "[[Alias Name>Page Name]][[Bracket Name]]test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Page Name");
        $this->assertEqual($r->getalias(), "Alias Name");
        $this->assertEqual($src, "[[Bracket Name]]test\ntext\n");

        // #7
        $src = "[[Bracket1 [[Bracket2]] ]] test\ntext\n";
        $r =& wiki_T_BracketName::parse($src, $wctx);
        $this->assertEqual($r->getpagename(), "Bracket1 [[Bracket2");
        $this->assertEqual($r->getalias(), "");
        $this->assertEqual($src, " ]] test\ntext\n");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_string()

    function test_wiki_t_string()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $r =& new wiki_T_String($src, $wctx);
        $this->assertEqual($r->getstring(), $src);
        $this->assertEqual($r->_source, $src);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);

        // {{{ getdistance()
        // #1
        $src = "test\ntext\n";
        $this->assertEqual(0, 
            wiki_T_String::getdistance($src, $wctx));

        // #2
        $src = "";
        $this->assertEqual(0, 
            wiki_T_String::getdistance($src, $wctx));

        // }}}

        // {{{ parse()
        // #1
        $src = "test\ntext\n";
        $r =& wiki_T_String::parse($src, $wctx);
        $this->assertEqual($r->getstring(), "test\ntext\n");
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, "");

        // #2
        $src = "";
        $r =& wiki_T_String::parse($src, $wctx);
        $this->assertEqual($r->getstring(), "");
        $this->assertEqual($src, "");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_strong()

    function test_wiki_t_strong()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $level = 3;
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_Strong($src2, $wctx, $s, $level);
        $this->assertEqual($r->_source, $src2);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($r->getlevel(), $level);
        $el = $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ getdistance()
        // #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Strong::getdistance($src, $wctx));

        // #2
        $src = "abc *StrongText* test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #3
        $src = "abc **StrongText**";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #4
        $src = "abc *StrongText**\t";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #5
        $src = "abc **StrongText*\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #6
        $src = "";
        $this->assertEqual(0, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #7
        $src = "abc *StrongText1* *StrongText2* test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #8
        $src = "abc * StrongText* test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #9
        $src = "abc *StrongText * test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #10
        $src = "abc *Strong Text* test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // #11
        $src = "abc ** test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Strong::getdistance($src, $wctx));

        // #12
        $src = "abc **** test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Strong::getdistance($src, $wctx));

        // }}}

        // {{{ parse()
        // #1
        $src = "* Strong Text * test\ntext\n";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 1);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, "test\ntext\n");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), " Strong Text ");

        // #2
        $src = "**StrongText**";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "StrongText");

        // #3
        $src = "*StrongText**\t";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 1);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "StrongText*");

        // #4
        $src = "**StrongText*\n";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 1);
        $this->assertEqual($src, "\n");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "*StrongText");

        // #5
        $src = "**StrongText* ";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 1);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "*StrongText");

        // #6
        $src = "abc **StrongText** test\ntext\n";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc **StrongText** test\ntext\n");

        // #7
        $src = "test\ntext\n";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "test\ntext\n");

        // #8
        $src = "**";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "**");

        // #9
        $src = "****";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 1);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "**");

        // #10
        $src = "***StrongText***";
        $r =& wiki_T_Strong::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "*StrongText*");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_italic()

    function test_wiki_t_italic()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $level = 3;
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_Italic($src2, $wctx, $s, $level);
        $this->assertEqual($r->_source, $src2);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($r->getlevel(), $level);
        $el = $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ getdistance()
        // #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Italic::getdistance($src, $wctx));

        // #2
        $src = "abc ''StrongText'' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #3
        $src = "abc '''StrongText'''";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #4
        $src = "abc ''StrongText'''\t";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #5
        $src = "abc '''StrongText''\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #6
        $src = "";
        $this->assertEqual(0, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #7
        $src = "abc ''StrongText1'' ''StrongText2'' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #8
        $src = "abc '' StrongText'' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #9
        $src = "abc ''StrongText '' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #10
        $src = "abc ''Strong Text'' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #11
        $src = "abc ''' test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Italic::getdistance($src, $wctx));

        // #12
        $src = "abc '''''' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #13
        $src = "abc''Strong Text'' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // #14
        $src = "abc'''Strong Text''' test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Italic::getdistance($src, $wctx));

        // }}}

        // {{{ parse()
        // #1
        $src = "'' Strong Text '' test\ntext\n";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, "test\ntext\n");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), " Strong Text ");

        // #2
        $src = "'''StrongText'''";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 3);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "StrongText");

        // #3
        $src = "''StrongText'''\t";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "StrongText'");

        // #4
        $src = "'''StrongText''\n";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($src, "\n");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "'StrongText");

        // #5
        $src = "'''StrongText'' ";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "'StrongText");

        // #6
        $src = "abc '''StrongText''' test\ntext\n";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc '''StrongText''' test\ntext\n");

        // #7
        $src = "test\ntext\n";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "test\ntext\n");

        // #8
        $src = "''''";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "''''");

        // #9
        $src = "''''''";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "''");

        // #10
        $src = "''''StrongText''''";
        $r =& wiki_T_Italic::parse($src, $wctx);
        $this->assertEqual($r->getlevel(), 3);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "'StrongText'");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_del()

    function test_wiki_t_del()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_Del($src2, $wctx, $s);
        $this->assertEqual($r->_source, $src2);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $el = $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ getdistance()
        // #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Del::getdistance($src, $wctx));

        // #2
        $src = "abc %%StrongText%% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #3
        $src = "abc %%%StrongText%%%";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #4
        $src = "abc %%StrongText%%%\t";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #5
        $src = "abc %%%StrongText%%\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #6
        $src = "";
        $this->assertEqual(0, 
            wiki_T_Del::getdistance($src, $wctx));

        // #7
        $src = "abc %%StrongText1%% %%StrongText2%% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #8
        $src = "abc %% StrongText%% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #9
        $src = "abc %%StrongText %% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #10
        $src = "abc %%Strong Text%% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #11
        $src = "abc %%% test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Del::getdistance($src, $wctx));

        // #12
        $src = "abc %%%% test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_Del::getdistance($src, $wctx));

        // #13
        $src = "abc%%Strong Text%% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // #14
        $src = "abc%%%Strong Text%%% test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_Del::getdistance($src, $wctx));

        // }}}

        // {{{ parse()
        // #1
        $src = "%% Strong Text %% test\ntext\n";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertEqual($r->_context->did, $wctx->did);
        $this->assertEqual($r->_context->pagename, $wctx->pagename);
        $this->assertEqual($src, "test\ntext\n");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), " Strong Text ");

        // #2
        $src = "%%StrongText%%";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "StrongText");

        // #3
        $src = "%%%StrongText%%\t";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "%StrongText");

        // #4
        $src = "%%%StrongText%%%\n";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertEqual($src, "\n");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "%StrongText%");

        // #5
        $src = "abc %%StrongText%% test\ntext\n";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc %%StrongText%% test\ntext\n");

        // #6
        $src = "test\ntext\n";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "test\ntext\n");

        // #7
        $src = "%%%%";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "%%%%");

        // #8
        $src = "%%%%%%";
        $r =& wiki_T_Del::parse($src, $wctx);
        $this->assertEqual($src, "");
        $child = $r->getelem();
        $this->assertEqual($child->getstring(), "%%");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_line()

    function test_wiki_t_line()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_Line($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse() (no terminator specified)
        // #1
        $src = "abc * Strong Text * "
            . "def [[Bracket Name]] "
            . "ghi ''Italic Text'' "
            . "jkl %%Deleted Text%% "
            . "test\ntext\n";
        $backup = $src;
        $r =& wiki_T_Line::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def ");
        $e =& $els[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_bracketname");
        $this->assertEqual($e->getsource(), "[[Bracket Name]]");
        $e =& $els[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " ghi");
        $e =& $els[5];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_italic");
        $this->assertEqual($e->getsource(), " ''Italic Text'' ");
        $e =& $els[6];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "jkl");
        $e =& $els[7];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_del");
        $this->assertEqual($e->getsource(), " %%Deleted Text%% ");
        $e =& $els[8];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "test\ntext\n");

        // #2
        $src = "test\ntext\n";
        $backup = $src;
        $r =& wiki_T_Line::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "test\ntext\n");

        // #3
        $src = "";
        $backup = $src;
        $r =& wiki_T_Line::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");
        $els = $r->getelements();
        $this->assertEqual(count($els), 0);

        // #4
        $src = "abc * Strong Text * def "
            . "[[Bracket Name]] test "
            . "((footnote1)) text "
            . "&p01 (arg1, arg2) { strings } xyz... ";
        $backup = $src;
        $r =& wiki_T_Line::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def ");
        $e =& $els[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_bracketname");
        $this->assertEqual($e->getsource(), "[[Bracket Name]]");
        $e =& $els[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " test ");
        $e =& $els[5];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_footnote");
        $this->assertEqual($e->getsource(), "((footnote1))");

        $e =& $e->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $e =& $e->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "footnote1");

        $e =& $els[6];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " text ");

        $e =& $els[7];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_inlineplugin");
        $this->assertEqual($e->getsource(), "&p01 (arg1, arg2) { strings }");
        $this->assertEqual($e->getpluginname(), "p01");
        $this->assertEqual($e->getparam1(), "arg1, arg2");
        $this->assertEqual($e->getparam2(), " strings ");

        $e =& $els[8];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " xyz... ");

        // }}}
        // {{{ parse() (with terminator specified)
        // #1
        $src = "abc * Strong Text * def, [[Bracket Name]] test\ntext\n";
        $r =& wiki_T_Line::parse($src, $wctx, ",");
        $this->assertEqual($r->getsource(), "abc * Strong Text * def");
        $this->assertEqual($src, ", [[Bracket Name]] test\ntext\n");
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def");

        // #2
        $src = "test\ntext\n";
        $backup = $src;
        $r =& wiki_T_Line::parse($src, $wctx, "\n");
        $this->assertEqual($r->getsource(), "test");
        $this->assertEqual($src, "\ntext\n");
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "test");

        // #3
        $src = "";
        $backup = $src;
        $r =& wiki_T_Line::parse($src, $wctx, "\n");
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");
        $els = $r->getelements();
        $this->assertEqual(count($els), 0);

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_footnote()

    function test_wiki_t_footnote()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_Footnote($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ getdistance()
        $backup = $src = "test ((abc * DEF * ghi)) test\ntext\n";
        $this->assertEqual(5, 
            wiki_T_Footnote::getdistance($src, $wctx));
        $this->assertEqual($src, $backup);

        $backup = $src = "test ((abc * DEF * ghi)) )) test\ntext\n";
        $this->assertEqual(5, 
            wiki_T_Footnote::getdistance($src, $wctx));
        $this->assertEqual($src, $backup);

        $backup = $src = "test abc * DEF * ghi)) test\ntext\n";
        $this->assertEqual(strlen($backup), 
            wiki_T_Footnote::getdistance($src, $wctx));
        $this->assertEqual($src, $backup);

        $backup = $src = "test ((abc * DEF * ghi test\ntext\n";
        $this->assertEqual(strlen($backup), 
            wiki_T_Footnote::getdistance($src, $wctx));
        $this->assertEqual($src, $backup);

        $backup = $src = "test ((abc (( ABC * DEF * ghi test\ntext\n";
        $this->assertEqual(strlen($backup), 
            wiki_T_Footnote::getdistance($src, $wctx));
        $this->assertEqual($src, $backup);

        // }}}

        // {{{ parse()
        // #1
        $src = "(abc";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "(abc");

        // #2
        $src = "abc";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "abc");

        // #3
        $src = "((abc * Strong Text * def [[Bracket Name]] ghi "
            . "''Italic Text'' jkl %%Deleted Text%% test\ntext\n";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, 
            "((abc * Strong Text * def [[Bracket Name]] ghi "
            . "''Italic Text'' jkl %%Deleted Text%% test\ntext\n");

        // #3
        $src = 
            "((abc * Strong Text * def ''Italic Text'' ghi %%Deleted Text%% ))"
           . " [[Bracket Name]] test\ntext\n";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "((abc * Strong Text * def ''Italic Text'' ghi %%Deleted Text%% ))"
            );
        $this->assertEqual($src, " [[Bracket Name]] test\ntext\n");
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), 
            "abc * Strong Text * def ''Italic Text'' ghi %%Deleted Text%% "
            );
        $els2 = $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els2[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els2[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def");
        $e =& $els2[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_italic");
        $this->assertEqual($e->getsource(), " ''Italic Text'' ");
        $e =& $els2[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "ghi");
        $e =& $els2[5];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_del");
        $this->assertEqual($e->getsource(), " %%Deleted Text%% ");

        // #4
        $src = "((abc * Strong Text * def)) [[Bracket Name]] "
            ."((ghi jkl)) test\ntext\n";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "((abc * Strong Text * def))");
        $this->assertEqual($src, " [[Bracket Name]] ((ghi jkl)) test\ntext\n");
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "abc * Strong Text * def");
        $els2 = $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els2[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els2[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def");

        // #5 (1)
        $src = "((abc * Strong Text * def ((ghi [[Bracket Name1]] jkl)) mno)) "
            . "[[Bracket Name2]] ((pqr stu)) test\ntext\n";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "((abc * Strong Text * def ((ghi [[Bracket Name1]] jkl)) mno))");
        $this->assertEqual($src, 
            " [[Bracket Name2]] ((pqr stu)) test\ntext\n");
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), 
            "abc * Strong Text * def ((ghi [[Bracket Name1]] jkl)) mno");
        $els2 = $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els2[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els2[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def ");
        $e =& $els2[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_footnote");
        $this->assertEqual($e->getsource(), "((ghi [[Bracket Name1]] jkl))");

        $els3 = $e->getelements();
        $e =& $els3[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "ghi [[Bracket Name1]] jkl");

        $els4 = $e->getelements();
        $e =& $els4[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "ghi ");
        $e =& $els4[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_bracketname");
        $this->assertEqual($e->getsource(), "[[Bracket Name1]]");
        $e =& $els4[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " jkl");

        $e =& $els2[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " mno");

        // #5 (2)
        $src = " mno)) [[Bracket Name2]] ((pqr stu)) test\ntext\n";
        $r =& wiki_T_Footnote::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual(strlen(" mno)) [[Bracket Name2]] "), 
            wiki_T_Footnote::getdistance($src, $wctx));

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_inlineplugin()

    function test_wiki_t_inlineplugin()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $pluginname = "pluginname";
        $param1 = "param1";
        $param2 = "param2";
        $r =& new wiki_T_InlinePlugin(
            $src, $wctx, $pluginname, $param1, $param2);
        $this->assertEqual($r->getsource(), $src);
        $this->assertEqual($r->getpluginname(), $pluginname);
        $this->assertEqual($r->getparam1(), $param1);
        $this->assertEqual($r->getparam2(), $param2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ getdistance()
        // #1
        $src = "test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #2
        $src = "abc& (def) test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #3
        $src = "abc&(def) test\ntext\n";
        $this->assertEqual(strlen($src), 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #4
        $src = "abc&1(def) test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #5
        $src = "abc&p () test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #6
        $src = "";
        $this->assertEqual(0, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #7
        $src = "abc&p\t() test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #8
        $src = "abc&p ( ) def &q () test\ntext\n";
        $this->assertEqual(3, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #9
        $src = "abc &p ( &q ( def ) ) test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // #10
        $src = "abc &p (( &q ( def ) )) test\ntext\n";
        $this->assertEqual(4, 
            wiki_T_InlinePlugin::getdistance($src, $wctx));

        // }}}

        // {{{ parse()
        // #1
        $src = "&p01 (a, b,c) xyz...";
        $r =& wiki_T_InlinePlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "&p01 (a, b,c)");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "a, b,c");
        $this->assertEqual($r->getparam2(), "");
        $this->assertEqual($src, " xyz...");

        // #2
        $src = "&p01 (a, b,c) { def } xyz...";
        $r =& wiki_T_InlinePlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "&p01 (a, b,c) { def }");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "a, b,c");
        $this->assertEqual($r->getparam2(), " def ");
        $this->assertEqual($src, " xyz...");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_empty()

    function test_wiki_t_empty()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $r =& new wiki_T_Empty($src, $wctx);
        $this->assertEqual($r->getsource(), $src);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse()
        // #1
        $src = "\t \ntest\ntext\n";
        $r =& wiki_T_Empty::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "\t \n");
        $this->assertEqual($src, "test\ntext\n");

        // #2
        $src = "";
        $r =& wiki_T_Empty::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "");
        $this->assertEqual($src, "");

        // #3
        $src = "abc\t \ntest\ntext\n";
        $r =& wiki_T_Empty::parse($src, $wctx);
        $this->assertNull($r);

        // #4
        $src = "\t \n \t \ntext\n";
        $r =& wiki_T_Empty::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "\t \n");
        $this->assertEqual($src, " \t \ntext\n");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_block()

    function test_wiki_t_block()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $r =& new wiki_T_Block($src, $wctx);
        $this->assertEqual($r->getsource(), $src);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse()
        // #1
        $src = "";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // #2
        $backup = $src = "abc\t \ntest\ntext\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($backup, $src);

        // heading
        $src = "* heading1\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_heading");
        $this->assertEqual($r->getstring(), "heading1");
        $this->assertEqual($src, "abc\n");

        // horizon
        $src = "---- \nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_horizon");
        $this->assertEqual($r->getsource(), "---- \n");
        $this->assertEqual($src, "abc\n");

        // pre
        $src = " pre1\n pre2\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_pre");
        $this->assertEqual($r->gettext(), "pre1\npre2\n");
        $this->assertEqual($src, "abc\n");

        // block quote
        $src = ">text1\n>text2\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_blockquote");
        $this->assertEqual($r->getsource(), ">text1\n>text2\n");
        $this->assertEqual($src, "abc\n");

        // ul
        $src = "- item1\n- item2\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_ul");
        $this->assertEqual($r->getsource(), "- item1\n- item2\n");
        $this->assertEqual($src, "abc\n");

        // ol
        $src = "+ item1\n+ item2\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_ol");
        $this->assertEqual($r->getsource(), "+ item1\n+ item2\n");
        $this->assertEqual($src, "abc\n");

        // dl
        $src = ":dt1:dd1\n:dt2:\ndd2\n\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_dl");
        $this->assertEqual($r->getsource(), ":dt1:dd1\n:dt2:\ndd2\n");
        $this->assertEqual($src, "\nabc\n");

        // table
        $src = "|d1|d2|d3|\n|d4|d5|d6|\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_table");
        $this->assertEqual($r->getsource(), "|d1|d2|d3|\n|d4|d5|d6|\n");
        $this->assertEqual($src, "abc\n");

        // block plugin
        $src = "#p01 |params|>\nabc\n||< def\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_blockplugin");
        $this->assertEqual($r->getsource(), "#p01 |params|>\nabc\n||< ");
        $this->assertEqual($src, "def\n");

        // comment
        $src = "//comment\nabc\n";
        $r =& wiki_T_Block::parse($src, $wctx);
        $this->assertEqual(strtolower(get_class($r)), "wiki_t_comment");
        $this->assertEqual($r->getsource(), "//comment\n");
        $this->assertEqual($src, "abc\n");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_heading()

    function test_wiki_t_heading()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $level = 3;
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_Heading($src2, $wctx, $level, $s);
        $this->assertEqual($r->getsource(), $src2);
        $this->assertEqual($r->getlevel(), $level);
        $this->assertEqual($r->getstring(), $src1);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse()
        // #1
        $backup = $src = "** heading ** strong text ** title \t \n** abc\n";
        $r =& wiki_T_Heading::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "** heading ** strong text ** title \t \n");
        $this->assertEqual($src, "** abc\n");
        $this->assertEqual($r->getlevel(), 2);
        $this->assertEqual($r->getstring(), "heading ** strong text ** title");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $els2 =& $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "heading");
        $e =& $els2[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " ** strong text ** ");
        $e =& $els2[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "title");

        // #2
        $backup = $src = "heading ** strong text ** \t \n** abc\n";
        $r =& wiki_T_Heading::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // #3
        $src = "***** heading ** strong text ** \t \n** abc\n";
        $r =& wiki_T_Heading::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "***** heading ** strong text ** \t \n");
        $this->assertEqual($src, "** abc\n");
        $this->assertEqual($r->getlevel(), 4);
        $this->assertEqual($r->getstring(), "* heading ** strong text **");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $els2 =& $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), "* heading ** ");
        $e =& $els2[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "strong text **");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_horizon()

    function test_wiki_t_horizon()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $r =& new wiki_T_Horizon($src, $wctx);
        $this->assertEqual($r->getsource(), $src);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse()
        // #1
        $backup = $src = "----";
        $r =& wiki_T_Horizon::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");

        // #2
        $backup = $src = "---";
        $r =& wiki_T_Horizon::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($backup, $src);

        // #3
        $backup = $src = "----- \t\nabc \n";
        $r =& wiki_T_Horizon::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "----- \t\n");
        $this->assertEqual($src, "abc \n");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_pre()

    function test_wiki_t_pre()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text1";
        $text= "source text2";
        $r =& new wiki_T_Pre($src, $wctx, $text);
        $this->assertEqual($r->getsource(), $src);
        $this->assertEqual($r->gettext(), $text);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse()
        // #1
        $backup = $src = " pre text line1\n"
            . "\tpre text line2\n"
            . " pre text line3\n"
            . "abc\n";
        $r =& wiki_T_Pre::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            " pre text line1\n"
            . "\tpre text line2\n"
            . " pre text line3\n");
        $this->assertEqual($r->gettext(), 
            "pre text line1\n"
            . "pre text line2\n"
            . "pre text line3\n");
        $this->assertEqual($src, "abc\n");

        // #2
        $backup = $src = "test\ntext\n";
        $r =& wiki_T_Pre::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // #3
        $backup = $src = "  pre text line1\n"
            . "\t pre text line2\n"
            . "  pre text line3";
        $r =& wiki_T_Pre::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "  pre text line1\n"
            . "\t pre text line2\n"
            . "  pre text line3");
        $this->assertEqual($r->gettext(), 
            " pre text line1\n"
            . " pre text line2\n"
            . " pre text line3");
        $this->assertEqual($src, "");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_comment()

    function test_wiki_t_comment()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text1";
        $r =& new wiki_T_Comment($src, $wctx);
        $this->assertEqual($r->getsource(), $src);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse()
        // #1
        $backup = $src = "//comment\nabc\n";
        $r =& wiki_T_Comment::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "//comment\n");
        $this->assertEqual($src, "abc\n");

        // #2
        $backup = $src = "test\ntext\n";
        $r =& wiki_T_Comment::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // #3
        $backup = $src = "//comment";
        $r =& wiki_T_Comment::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "//comment");
        $this->assertEqual($src, "");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_paragraph()

    function test_wiki_t_paragraph()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_Paragraph($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // parse (inline -> block(horizon))
        $backup = $src = "inline text1\ninline text2\n----\n";
        $r =& wiki_T_Paragraph::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "inline text1\ninline text2\n");
        $this->assertEqual($src, "----\n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "inline text1\n");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "inline text2\n");

        // parse (inline -> empty)
        $backup = $src = "inline text1\ninline text2\n \n";
        $r =& wiki_T_Paragraph::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "inline text1\ninline text2\n");
        $this->assertEqual($src, " \n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "inline text1\n");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "inline text2\n");

        // parse (empty -> inline)
        $backup = $src = "\t\ninline text1\ninline text2\n";
        $r =& wiki_T_Paragraph::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "");
        $this->assertEqual($src, "\t\ninline text1\ninline text2\n");

    }

    // }}}
    // {{{ test_wiki_t_body()

    function test_wiki_t_body()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_Body($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_Body::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");

        $els =& $r->getelements();
        $this->assertEqual(count($els), 0);

        // }}}
        // {{{ parse #1

        $backup = $src = " \n";
        $r =& wiki_T_Body::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");

        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_empty");
        $this->assertEqual($e->getsource(), " \n");

        // }}}
        // {{{ parse #2

        $backup = $src = <<<TEXT
* Heading text1

abcdef *Strong Text* ghi [[Bracket Name]]
paragraph line2.
\t 
---- 
 pre line1
\tpre line2
 pre line3

** Heading Level 2

paragraph line3.
// comment1
// comment2
//
TEXT;
        $r =& wiki_T_Body::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");

        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_heading");
        $this->assertEqual($e->getsource(), "* Heading text1\n");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_empty");
        $this->assertEqual($e->getsource(), "\n");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_paragraph");
        $this->assertEqual($e->getsource(), 
            "abcdef *Strong Text* ghi [[Bracket Name]]\nparagraph line2.\n");
        $e =& $els[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_empty");
        $this->assertEqual($e->getsource(), "\t \n");
        $e =& $els[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_horizon");
        $this->assertEqual($e->getsource(), "---- \n");
        $e =& $els[5];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_pre");
        $this->assertEqual($e->gettext(), "pre line1\npre line2\npre line3\n");
        $e =& $els[6];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_empty");
        $this->assertEqual($e->getsource(), "\n");
        $e =& $els[7];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_heading");
        $this->assertEqual($e->getsource(), "** Heading Level 2\n");
        $e =& $els[8];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_empty");
        $this->assertEqual($e->getsource(), "\n");
        $e =& $els[9];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_paragraph");
        $this->assertEqual($e->getsource(), "paragraph line3.\n");
        $e =& $els[10];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_comment");
        $this->assertEqual($e->getsource(), "// comment1\n");
        $e =& $els[11];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_comment");
        $this->assertEqual($e->getsource(), "// comment2\n");
        $e =& $els[12];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_comment");
        $this->assertEqual($e->getsource(), "//");

        // }}}

    }

    // }}}
    // {{{ test_wiki_t_blockquote()

    function test_wiki_t_blockquote()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_BlockQuote($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_BlockQuote::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // }}}
        // {{{ parse #1

        $backup = $src = "abc\n> line1\n> line2\ndef\n";
        $r =& wiki_T_BlockQuote::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = ">inline * Strong Text * text.\n"
            . ">abc [[Bracket Name]] def\n"
            . ">ghi\n"
            . "inline\n";
        $r =& wiki_T_BlockQuote::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            ">inline * Strong Text * text.\n"
            . ">abc [[Bracket Name]] def\n"
            . ">ghi\n");
        $this->assertEqual($src, "inline\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_body");
        $this->assertEqual($e->getsource(), 
            "inline * Strong Text * text.\n"
            . "abc [[Bracket Name]] def\n"
            . "ghi\n");
        $els =& $e->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_paragraph");
        $this->assertEqual($e->getsource(), 
            "inline * Strong Text * text.\n"
            . "abc [[Bracket Name]] def\n"
            . "ghi\n");
        $lines =& $e->getelements();
        $e =& $lines[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "inline * Strong Text * text.\n");
        $e =& $lines[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "abc [[Bracket Name]] def\n");
        $e =& $lines[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "ghi\n");

        // }}}

    }

    // }}}
    // {{{ test_wiki_t_ul()

    function test_wiki_t_ul()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_UL($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_UL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // }}}
        // {{{ parse #1

        $backup = $src = "abc\n- item1\n- item2\ndef\n";
        $r =& wiki_T_UL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = "abc\n+ item1\n+ item2\ndef\n";
        $r =& wiki_T_UL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #3

        $backup = $src = "- item1\n- item2\n-- item2_1\nabc\n";
        $r =& wiki_T_UL::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "- item1\n- item2\n-- item2_1\n");
        $this->assertEqual($src, "abc\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_list");
        $this->assertEqual($e->getsource(), " item1\n item2\n- item2_1\n");
        $els =& $e->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item1\n");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item2\n");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_ul");
        $this->assertEqual($e->getsource(), "- item2_1\n");
        $e =& $e->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_list");
        $this->assertEqual($e->getsource(), " item2_1\n");
        $els2 =& $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item2_1\n");

        // }}}
        // {{{ parse #4

        $backup = $src = "+ item1\n+ item2\n++ item2_1\nabc\n";
        $r =& wiki_T_UL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}

    }

    // }}}
    // {{{ test_wiki_t_ol()

    function test_wiki_t_ol()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_OL($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_OL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // }}}
        // {{{ parse #1

        $backup = $src = "abc\n- item1\n- item2\ndef\n";
        $r =& wiki_T_OL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = "abc\n+ item1\n+ item2\ndef\n";
        $r =& wiki_T_OL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #3

        $backup = $src = "- item1\n- item2\n-- item2_1\nabc\n";
        $r =& wiki_T_OL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #4

        $backup = $src = "+ item1\n+ item2\n++ item2_1\nabc\n";
        $r =& wiki_T_OL::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "+ item1\n+ item2\n++ item2_1\n");
        $this->assertEqual($src, "abc\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_list");
        $this->assertEqual($e->getsource(), " item1\n item2\n+ item2_1\n");
        $els =& $e->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item1\n");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item2\n");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_ol");
        $this->assertEqual($e->getsource(), "+ item2_1\n");
        $e =& $e->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_list");
        $this->assertEqual($e->getsource(), " item2_1\n");
        $els2 =& $e->getelements();
        $e =& $els2[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item2_1\n");

        // }}}

    }

    // }}}
    // {{{ test_wiki_t_li()

    function test_wiki_t_li()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_LI($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_LI::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "");
        $this->assertEqual($src, "");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "");

        // }}}
        // {{{ parse #1

        $backup = $src = " item1\n item2\n";
        $r =& wiki_T_LI::parse($src, $wctx);
        $this->assertEqual($r->getsource(), " item1\n");
        $this->assertEqual($src, " item2\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), " item1");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_list()

    function test_wiki_t_list()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_List($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_List::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");

        $els =& $r->getelements();
        $this->assertEqual(count($els), 0);

        // }}}
        // {{{ parse #1

        $backup = $src = <<<TEXT
 item1
 item2
- item2_1
- item2_2
 item3
+ item3_1
+ item3_2
TEXT;
        $r =& wiki_T_List::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, "");

        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item1\n");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item2\n");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_ul");
        $this->assertEqual($e->getsource(), "- item2_1\n- item2_2\n");
        $e =& $els[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_li");
        $this->assertEqual($e->getsource(), " item3\n");
        $e =& $els[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_ol");
        $this->assertEqual($e->getsource(), "+ item3_1\n+ item3_2");

        // }}}

    }

    // }}}
    // {{{ test_wiki_t_dt()

    function test_wiki_t_dt()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_DT($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // }}}
        // {{{ parse #1

        $backup = $src = "abc:test label:\ndef";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = ":\nabc:\n";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #3

        $backup = $src = ":test label \n";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #4

        $backup = $src = ":test label:\t\nabc\n";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertEqual($r->getsource(), ":test label:\t\n");
        $this->assertEqual($src, "abc\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "test label");

        // }}}
        // {{{ parse #5

        $backup = $src = ":test label1:test label2\t\nabc\n";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertEqual($r->getsource(), ":test label1:");
        $this->assertEqual($src, "test label2\t\nabc\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "test label1");

        // }}}
        // {{{ parse #6

        $backup = $src = ":test label1:test label2:\t\nabc\n";
        $r =& wiki_T_DT::parse($src, $wctx);
        $this->assertEqual($r->getsource(), ":test label1:");
        $this->assertEqual($src, "test label2:\t\nabc\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "test label1");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_dd()

    function test_wiki_t_dd()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $r =& new wiki_T_DD($src2, $wctx, $s);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_DD::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // }}}
        // {{{ parse #1

        $backup = $src = "\nabc\n";
        $r =& wiki_T_DD::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = ":def label:\nabc\n";
        $r =& wiki_T_DD::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #3

        $backup = $src = ">line1\n>line2\n:def label2:\nline3\n";
        $r =& wiki_T_DD::parse($src, $wctx);
        $this->assertEqual($r->getsource(), ">line1\n>line2\n");
        $this->assertEqual($src, ":def label2:\nline3\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_blockquote");
        $this->assertEqual($e->getsource(), ">line1\n>line2\n");

        // }}}
        // {{{ parse #4

        $backup = $src = "line1\nline2\n:def label2\n- item1\n";
        $r =& wiki_T_DD::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "line1\n");
        $this->assertEqual($src, "line2\n:def label2\n- item1\n");

        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), "line1");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_dl()

    function test_wiki_t_dl()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_DL($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_DL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #1

        $backup = $src = ">bq1\nline2\n";
        $r =& wiki_T_DL::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = <<<TEXT
:dt1:dd1
:dt2:
>dd2_1_bq1
>dd2_1_bq2
:dt3:
dd3_1
dd3_2
:dt4:dd4_1
dd4_2

:dt5:dd5
TEXT;
        $d = "\n:dt5:dd5";
        $r =& wiki_T_DL::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
        _substr($backup, 0, strlen($backup) - strlen($d)));
        $this->assertEqual($src, $d);

        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dt");
        $this->assertEqual($e->getsource(), ":dt1:");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dd");
        $this->assertEqual($e->getsource(), "dd1\n");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dt");
        $this->assertEqual($e->getsource(), ":dt2:\n");
        $e =& $els[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dd");
        $this->assertEqual($e->getsource(), ">dd2_1_bq1\n>dd2_1_bq2\n");
        $e =& $els[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dt");
        $this->assertEqual($e->getsource(), ":dt3:\n");
        $e =& $els[5];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dd");
        $this->assertEqual($e->getsource(), "dd3_1\n");
        $e =& $els[6];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dd");
        $this->assertEqual($e->getsource(), "dd3_2\n");
        $e =& $els[7];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dt");
        $this->assertEqual($e->getsource(), ":dt4:");
        $e =& $els[8];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dd");
        $this->assertEqual($e->getsource(), "dd4_1\n");
        $e =& $els[9];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_dd");
        $this->assertEqual($e->getsource(), "dd4_2\n");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_td()

    function test_wiki_t_td()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        // {{{ constructor (no options)
        $o = array();
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        $this->assertNull($r->getalign());
        $this->assertFalse($r->isheader());
        $this->assertNull($r->getbgcolor());
        // }}}
        // {{{ constructor ('h', 'L', '')
        $o = array('h', 'L', '');
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $this->assertEqual($r->getalign(), 'left');
        $this->assertTrue($r->isheader());
        $this->assertNull($r->getbgcolor());
        // }}}
        // {{{ constructor ('LEFT', 'BGCOLOR(...)')
        $o = array('LEFT', 'BGCOLOR(xxx)');
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $this->assertEqual($r->getalign(), 'left');
        $this->assertFalse($r->isheader());
        $this->assertEqual($r->getbgcolor(), 'xxx');
        // }}}
        // {{{ constructor ('r')
        $o = array('r');
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $this->assertEqual($r->getalign(), 'right');
        $this->assertFalse($r->isheader());
        $this->assertNull($r->getbgcolor());
        // }}}
        // {{{ constructor ('right')
        $o = array('right');
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $this->assertEqual($r->getalign(), 'right');
        $this->assertFalse($r->isheader());
        $this->assertNull($r->getbgcolor());
        // }}}
        // {{{ constructor ('c')
        $o = array('c');
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $this->assertEqual($r->getalign(), 'center');
        $this->assertFalse($r->isheader());
        $this->assertNull($r->getbgcolor());
        // }}}
        // {{{ constructor ('CENTER')
        $o = array('CENTER');
        $r =& new wiki_T_TD($src2, $wctx, $s, $o);
        $this->assertEqual($r->getsource(), $src2);

        $this->assertEqual($r->getalign(), 'center');
        $this->assertFalse($r->isheader());
        $this->assertNull($r->getbgcolor());
        // }}}

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_TD::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, "");

        // }}}
        // {{{ parse #1

        $backup = $src = "abc";
        $r =& wiki_T_TD::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = "LEFT: data1 |RIGHT: data2 |";
        $r =& wiki_T_TD::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "LEFT: data1 ");
        $this->assertEqual($r->getalign(), "left");
        $this->assertEqual($src, "RIGHT: data2 |");
        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), " data1 ");

        // }}}
        // {{{ parse #3

        $backup = $src = " data1 |RIGHT: data2 |";
        $r =& wiki_T_TD::parse($src, $wctx, array("H"));
        $this->assertEqual($r->getsource(), " data1 ");
        $this->assertEqual($r->isheader(), true);
        $this->assertEqual($src, "RIGHT: data2 |");
        $e =& $r->getelem();
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_line");
        $this->assertEqual($e->getsource(), " data1 ");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_tr()

    function test_wiki_t_tr()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_TR($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $this->assertEqual($r->getcols(), 1);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #1

        $backup = $src = "abc|def\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = "|abc|def|HR\ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "|abc|def|HR\n");
        $this->assertEqual($r->getcols(), 2);
        $this->assertEqual($src, "test\n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "abc");
        $this->assertEqual($e->getalign(), "right");
        $this->assertTrue($e->isheader());
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "def");
        $this->assertEqual($e->getalign(), "right");
        $this->assertTrue($e->isheader());

        // }}}
        // {{{ parse #3

        $backup = $src = "|\tabc | def\t|  \ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "|\tabc | def\t|  \n");
        $this->assertEqual($src, "test\n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "\tabc ");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), " def\t");

        // }}}
        // {{{ parse #4

        $backup = $src = "|\tabc \ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #5

        $backup = $src = "|BGCOLOR(black):\tabc |H:def\t|C  \ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "|BGCOLOR(black):\tabc |H:def\t|C  \n");
        $this->assertEqual($src, "test\n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "BGCOLOR(black):\tabc ");
        $this->assertEqual($e->getalign(), "center");
        $this->assertEqual($e->getbgcolor(), "black");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "H:def\t");
        $this->assertEqual($e->getalign(), "center");
        $this->assertFalse($e->isheader());

        // }}}
        // {{{ parse #6

        $backup = $src = "|abc|\ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "|abc|\n");
        $this->assertEqual($r->getcols(), 1);
        $this->assertEqual($src, "test\n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "abc");

        // }}}
        // {{{ parse #7

        $backup = $src = "||\ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "||\n");
        $this->assertEqual($r->getcols(), 1);
        $this->assertEqual($src, "test\n");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "");

        // }}}
        // {{{ parse #8

        $backup = $src = "|\ntest\n";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #9

        $backup = $src = "|abc|def|ghi|";
        $r =& wiki_T_TR::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "|abc|def|ghi|");
        $this->assertEqual($r->getcols(), 3);
        $this->assertEqual($src, "");
        $els =& $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "def");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "ghi");

        // }}}
    }

    // }}}
    // {{{ test_wiki_t_table()

    function test_wiki_t_table()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src1 = "source text1";
        $src2 = "source text2";
        $s =& new wiki_T_String($src1, $wctx);
        $els = array(&$s);
        $r =& new wiki_T_Table($src2, $wctx, $els);
        $this->assertEqual($r->getsource(), $src2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);
        $el =& $r->getelem();
        $this->assertEqual($el->getstring(), $src1);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_Table::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #1

        $backup = $src = "line1|abc|\n";
        $r =& wiki_T_Table::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = <<<TEXT
|d1_1|d2_1|d3_1|
|d1_2|d2_2|d3_2|

line1
TEXT;
        $d = "\nline1";
        $r =& wiki_T_Table::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
        _substr($backup, 0, strlen($backup) - strlen($d)));
        $this->assertEqual($src, $d);

        $els =& $r->getelements();
        $this->assertEqual(count($els), 2);
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_tr");
        $this->assertEqual($e->getsource(), "|d1_1|d2_1|d3_1|\n");
        $this->assertEqual($e->getcols(), 3);
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_tr");
        $this->assertEqual($e->getsource(), "|d1_2|d2_2|d3_2|\n");
        $this->assertEqual($e->getcols(), 3);

        // }}}
        // {{{ parse #3

        $backup = $src = <<<TEXT
|d1_1|d2_1|d3_1|
|d1_2|d2_2|d3_2|
|d1_3|d2_3|

line1
TEXT;
        $d = "|d1_3|d2_3|\n\nline1";
        $r =& wiki_T_Table::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
        _substr($backup, 0, strlen($backup) - strlen($d)));
        $this->assertEqual($src, $d);

        $els =& $r->getelements();
        $this->assertEqual(count($els), 2);
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_tr");
        $this->assertEqual($e->getsource(), "|d1_1|d2_1|d3_1|\n");
        $this->assertEqual($e->getcols(), 3);
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_tr");
        $this->assertEqual($e->getsource(), "|d1_2|d2_2|d3_2|\n");
        $this->assertEqual($e->getcols(), 3);

        // }}}
        // {{{ parse #3

        $backup = $src = "|d1_1|d2_1|d3_1|";
        $r =& wiki_T_Table::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $backup);
        $this->assertEqual($src, '');

        $els =& $r->getelements();
        $this->assertEqual(count($els), 1);
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_tr");
        $this->assertEqual($e->getsource(), "|d1_1|d2_1|d3_1|");
        $this->assertEqual($e->getcols(), 3);

        $els = $e->getelements();
        $this->assertEqual(count($els), 3);
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "d1_1");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "d2_1");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_td");
        $this->assertEqual($e->getsource(), "d3_1");

        // }}}

    }

    // }}}
    // {{{ test_wiki_t_blockplugin()

    function test_wiki_t_blockplugin()
    {
        $wctx =& new wiki_Context(100, 'Test Page');

        // constructor
        $src = "source text";
        $pluginname = "pluginname";
        $param1 = "param1";
        $param2 = "param2";
        $r =& new wiki_T_BlockPlugin(
            $src, $wctx, $pluginname, $param1, $param2);
        $this->assertEqual($r->getsource(), $src);
        $this->assertEqual($r->getpluginname(), $pluginname);
        $this->assertEqual($r->getparam1(), $param1);
        $this->assertEqual($r->getparam2(), $param2);
        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $wctx->did);
        $this->assertEqual($ctx->pagename, $wctx->pagename);

        // {{{ parse #0

        $backup = $src = "";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #1

        $backup = $src = "abc\n#p01|params|>...||<\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertNull($r);
        $this->assertEqual($src, $backup);

        // }}}
        // {{{ parse #2

        $backup = $src = "#p01 |params|\nabc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "#p01 |params|\n");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "params");
        $this->assertEqual($r->getparam2(), "");
        $this->assertEqual($src, "abc\n");

        // }}}
        // {{{ parse #2-2

        $backup = $src = "#p01|params|\nabc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "#p01|params|\n");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "params");
        $this->assertEqual($r->getparam2(), "");
        $this->assertEqual($src, "abc\n");

        // }}}
        // {{{ parse #2-3

        $backup = $src = "#p01||\nabc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "#p01||\n");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "");
        $this->assertEqual($r->getparam2(), "");
        $this->assertEqual($src, "abc\n");

        // }}}
        // {{{ parse #2-4

        $backup = $src = "#p01 ||\nabc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), "#p01 ||\n");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "");
        $this->assertEqual($r->getparam2(), "");
        $this->assertEqual($src, "abc\n");

        // }}}
        // {{{ parse #3

        $block_text = <<<TEXT
line1 *Strong Text*
 pre1
 pre2

paragraph1_1
paragraph1_2

paragraph1_3
TEXT;
        $backup = $src = "#p01 |p1,p2, p3|>{$block_text}||<\ttest abc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "#p01 |p1,p2, p3|>{$block_text}||<\t");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "p1,p2, p3");
        $this->assertEqual($r->getparam2(), $block_text);
        $this->assertEqual($src, "test abc\n");

        // }}}
        // {{{ parse #3-2

        $backup = $src = "#p01|p1,p2, p3|>{$block_text}||<\ttest abc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "#p01|p1,p2, p3|>{$block_text}||<\t");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "p1,p2, p3");
        $this->assertEqual($r->getparam2(), $block_text);
        $this->assertEqual($src, "test abc\n");

        // }}}
        // {{{ parse #3-3

        $backup = $src = "#p01||>{$block_text}||<\ttest abc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "#p01||>{$block_text}||<\t");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "");
        $this->assertEqual($r->getparam2(), $block_text);
        $this->assertEqual($src, "test abc\n");

        // }}}
        // {{{ parse #3-4

        $backup = $src = "#p01 || >{$block_text}||<\ttest abc\n";
        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), 
            "#p01 || >{$block_text}||<\t");
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "");
        $this->assertEqual($r->getparam2(), $block_text);
        $this->assertEqual($src, "test abc\n");

        // }}}
        // {{{ parse #4
        $block_text = <<<TEXT
line1 *Strong Text*
 pre1
 pre2

foobar |abc|> ddd ||<

paragraph1_1
paragraph1_2

paragraph1_3
TEXT;
        $backup = $src = "#p01 |p1,p2, p3|>{$block_text}||<\ttest abc\n";
        $result_text = <<<TEXT
#p01 |p1,p2, p3|>line1 *Strong Text*
 pre1
 pre2

foobar |abc|> ddd ||<

TEXT;
        $param2_text = <<<TEXT
line1 *Strong Text*
 pre1
 pre2

foobar |abc|> ddd 
TEXT;
        $remained_src = <<<TEXT

paragraph1_1
paragraph1_2

paragraph1_3||<\ttest abc

TEXT;

        $r =& wiki_T_BlockPlugin::parse($src, $wctx);
        $this->assertEqual($r->getsource(), $result_text);
        $this->assertEqual($r->getpluginname(), "p01");
        $this->assertEqual($r->getparam1(), "p1,p2, p3");
        $this->assertEqual($r->getparam2(), $param2_text);
        $this->assertEqual($src, $remained_src);

        // }}}
    }

    // }}}
    // {{{ test_parse_inline()

    function test_parse_inline()
    {
        $backup = $src = 
            "abc * Strong Text * def [[Bracket Name]] test\r\ntext\r\n";
        $did = 100;
        $pagename = "Test Page";

        $r =& wiki_Parser::parse_inline($src, $did, $pagename);

        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $did);
        $this->assertEqual($ctx->pagename, $pagename);

        $this->assertEqual($r->getsource(), 
            str_replace("\r\n", "\n", $backup));

        $this->assertEqual($src, $backup); // must not be modified.

        $els = $r->getelements();
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "abc");
        $e =& $els[1];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_strong");
        $this->assertEqual($e->getsource(), " * Strong Text * ");
        $e =& $els[2];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), "def ");
        $e =& $els[3];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_bracketname");
        $this->assertEqual($e->getsource(), "[[Bracket Name]]");
        $e =& $els[4];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_string");
        $this->assertEqual($e->getsource(), " test\ntext\n");
    }

    // }}}
    // {{{ test_parse_block()

    function test_parse_block()
    {
        $backup = $src = 
            "abc * Strong Text * def [[Bracket Name]] test\r\ntext\r\n";
        $did = 100;
        $pagename = "Test Page";

        $r =& wiki_Parser::parse_block($src, $did, $pagename);

        $ctx =& $r->getcontext();
        $this->assertEqual($ctx->did, $did);
        $this->assertEqual($ctx->pagename, $pagename);

        $this->assertEqual($r->getsource(), 
            str_replace("\r\n", "\n", $backup));

        $this->assertEqual($src, $backup); // must not be modified.

        $els = $r->getelements();
        $this->assertEqual(count($els), 1);
        $e =& $els[0];
        $this->assertEqual(strtolower(get_class($e)), "wiki_t_paragraph");
        $this->assertEqual($e->getsource(), 
            str_replace("\r\n", "\n", $backup));
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
