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

require_once('yb/DataContext.php');
require_once('yb/Html.php');

class yb_Html_TestCase extends UnitTestCase
{
    // {{{ test_convert()

    function test_convert()
    {
        $old = _YB('dir.plugin.html', 
            dirname(__FILE__) . '/test_Html_Plugins');
        $ctx =& new yb_DataContext(array('id' => 100, 'name' => 'test'));
        $readmore_url = yb_Util::make_url(array(
            'mdl' => 'view', 'id' => $ctx->get('id')));
        $readmore_link = sprintf(
                '<a href="%s" class="readmore_link">%s</a>', 
                $readmore_url, t('(show all text)'));

        // {{{ #1 (list mode)(a1)
        $src = <<<EOS
foo <yb_dummy p1/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p1,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(a2)
        $src = <<<EOS
foo <yb_dummy2 p1/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy2,p1,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(b)
        $src = <<<EOS
foo
<yb_dummy p1/>
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo
!dummy,p1,,100,test!
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(c)
        $src = <<<EOS
foo <yb_dummy p1>p2</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p1,p2,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(d)
        $src = <<<EOS
foo 
<yb_dummy p1>p2</yb_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2,100,test! 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(e)
        $src = <<<EOS
foo 
<yb_dummy p1>
p2
</yb_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,
p2
,100,test! 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(f)(duplicated closing tag)
        $src = <<<EOS
foo 
<yb_dummy p1>p2 </yb_dummy> </yb_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2 ,100,test! </yb_dummy> 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(g)(lack of closing tag)
        $src = <<<EOS
foo 
<yb_dummy p1>p2 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
p2 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(h)(nested tag)
        $src = <<<EOS
foo 
<yb_dummy p1>p2 <yb_dummy p3>p4</yb_dummy> </yb_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2 <yb_dummy p3>p4,100,test! </yb_dummy> 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(i)(nested tag, duplicated closing tag)
        $src = <<<EOS
foo 
<yb_dummy p1>p2 <yb_dummy p3>p4</yb_dummy> </yb_dummy> 
</yb_dummy>
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2 <yb_dummy p3>p4,100,test! </yb_dummy> 
</yb_dummy>
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(j)(nested tag, lack of closing tag)
        $src = <<<EOS
foo 
<yb_dummy p1>p2 <yb_dummy p3>p4 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
p2 p4 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(k)(<yb_xxxx attr/></yb_xxxx>)
        $src = <<<EOS
foo 
<yb_dummy p1/>p2 </yb_dummy>p4 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,,100,test!p2 </yb_dummy>p4 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(l)(<yb_xxxx attr/ ></yb_xxxx>)
        $src = <<<EOS
foo 
<yb_dummy p1/ >p2 </yb_dummy>p4 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1/,p2 ,100,test!p4 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(a)
        $src = <<<EOS
foo <yb_dummy p2a/> bar
foo <yb_dummy p2b/> bar
foo <yb_dummy p2c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,,100,test! bar
foo !dummy,p2b,,100,test! bar
foo !dummy,p2c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(b)(pair-tag pattern)
        $src = <<<EOS
foo <yb_dummy p2a>pp2a</yb_dummy> bar
foo 
<yb_dummy p2b>pp2b</yb_dummy>
 bar
foo 
<yb_dummy p2c>
pp2c
pp2c_2
</yb_dummy>
 bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,pp2a,100,test! bar
foo 
!dummy,p2b,pp2b,100,test!
 bar
foo 
!dummy,p2c,
pp2c
pp2c_2
,100,test!
 bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(c)(pair-tag nested pattern)
        $src = <<<EOS
foo <yb_dummy p2a>pp2a
<yb_dummy p2a_2>pp2a
</yb_dummy>
</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,pp2a
<yb_dummy p2a_2>pp2a
,100,test!
</yb_dummy> bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(d)(pair-tag lack of closing tag pattern)
        $src = <<<EOS
foo <yb_dummy p2a>pp2a
<yb_dummy p2a_2>pp2a
</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,pp2a
<yb_dummy p2a_2>pp2a
,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(e)(pair-tag lack of closing 2 tag pattern)
        $src = <<<EOS
foo <yb_dummy p2a>pp2a
<yb_dummy p2a_2>pp2a
 bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo pp2a
pp2a
 bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(f)(cross nested : yb_dummy, yb_dummy2)
        $src = <<<EOS
foo <yb_dummy p2a>pp2a
<yb_dummy2 p2a1>pp2a1
</yb_dummy2>
</yb_dummy>
 bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,pp2a
<yb_dummy2 p2a1>pp2a1
</yb_dummy2>
,100,test!
 bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(f)(cross nested : strange closing tag pattern)
        $src = <<<EOS
foo <yb_dummy p2a>pp2a
<yb_dummy2 p2a1>pp2a1
</yb_dummy>
</yb_dummy2>
 bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,pp2a
<yb_dummy2 p2a1>pp2a1
,100,test!
</yb_dummy2>
 bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(a)
        $src = <<<EOS
foo <yb_dummy p3a/> bar
<yb_more />
foo <yb_dummy p3b/> bar
foo <yb_dummy p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p3a,,100,test! bar
{$readmore_link}
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(b)
        $src = <<<EOS
<yb_more />
foo <yb_dummy p3a/> bar
foo <yb_dummy p3b/> bar
foo <yb_dummy p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
{$readmore_link}
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(c)
        $src = <<<EOS
foo 
<yb_dummy p3a>
hoge
</yb_dummy>
 bar
<yb_more />
foo <yb_dummy p3b/> bar
foo <yb_dummy p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p3a,
hoge
,100,test!
 bar
{$readmore_link}
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(d) (<yb_more> internal block)
        $src = <<<EOS
foo 
<yb_dummy p3a>
<yb_more />
hoge
</yb_dummy>
 bar
foo <yb_dummy p3b/> bar
foo <yb_dummy p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p3a,
<yb_more />
hoge
,100,test!
 bar
foo !dummy,p3b,,100,test! bar
foo !dummy,p3c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #4 (detail mode with <yb_more>)(a)
        $src = <<<EOS
foo <yb_dummy p4a/> bar
<yb_more />
foo <yb_dummy p4b/> bar
foo <yb_dummy p4c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p4a,,100,test! bar

foo !dummy,p4b,,100,test! bar
foo !dummy,p4c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #4 (detail mode with <yb_more>)(b)
        $src = <<<EOS
foo <yb_dummy p4a>p4a2</yb_dummy> bar
<yb_more />
foo <yb_dummy p4b>p4b2</yb_dummy> bar
foo <yb_dummy p4c>p4c2</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p4a,p4a2,100,test! bar

foo !dummy,p4b,p4b2,100,test! bar
foo !dummy,p4c,p4c2,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #4 (detail mode with <yb_more>)(c)
        $src = <<<EOS
foo <yb_dummy p4a>p4a2
<yb_more />
</yb_dummy> bar
foo <yb_dummy p4b>p4b2</yb_dummy> bar
foo <yb_dummy p4c>p4c2</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p4a,p4a2
<yb_more />
,100,test! bar
foo !dummy,p4b,p4b2,100,test! bar
foo !dummy,p4c,p4c2,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #5 (detail mode)
        $src = <<<EOS
foo <yb_dummy p5a/> bar
foo <yb_dummy p5b/> bar
foo <yb_dummy p5c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p5a,,100,test! bar
foo !dummy,p5b,,100,test! bar
foo !dummy,p5c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}

        _YB('dir.plugin.html', $old);
    }

    // }}}
    // {{{ test_convert_with_large_case()

    function test_convert_with_large_case()
    {
        $old = _YB('dir.plugin.html', 
            dirname(__FILE__) . '/test_Html_Plugins');
        $ctx =& new yb_DataContext(array('id' => 100, 'name' => 'test'));
        $readmore_url = yb_Util::make_url(array(
            'mdl' => 'view', 'id' => $ctx->get('id')));
        $readmore_link = sprintf(
                '<a href="%s" class="readmore_link">%s</a>', 
                $readmore_url, t('(show all text)'));

        // {{{ #1 (list mode)(a1)
        $src = <<<EOS
foo <YB_DUMMY p1/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p1,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(a2)
        $src = <<<EOS
foo <YB_DUMMY p1/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p1,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(b)
        $src = <<<EOS
foo
<YB_DUMMY p1/>
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo
!dummy,p1,,100,test!
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(c)
        $src = <<<EOS
foo <YB_DUMMY p1>p2</YB_DUMMY> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p1,p2,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(d)
        $src = <<<EOS
foo 
<YB_DUMMY p1>p2</yb_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2,100,test! 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(e)
        $src = <<<EOS
foo 
<YB_DUMMY p1>
p2
</YB_DUMMY> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,
p2
,100,test! 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(f)(duplicated closing tag)
        $src = <<<EOS
foo 
<YB_DUMMY p1>p2 </YB_DUmmy> </yb_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2 ,100,test! </yb_dummy> 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(g)(lack of closing tag)
        $src = <<<EOS
foo 
<YB_DUMMY p1>p2 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
p2 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(h)(nested tag)
        $src = <<<EOS
foo 
<yB_Dummy p1>p2 <yB_DUMMY p3>p4</yb_DUMMY> </YB_dummy> 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2 <yB_DUMMY p3>p4,100,test! </YB_dummy> 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(i)(nested tag, duplicated closing tag)
        $src = <<<EOS
foo 
<YB_DUMMY p1>p2 <yB_dummy p3>p4</yB_dummy> </YB_DUMMY> 
</yB_dummY>
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p1,p2 <yB_dummy p3>p4,100,test! </YB_DUMMY> 
</yB_dummY>
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #1 (list mode)(j)(nested tag, lack of closing tag)
        $src = <<<EOS
foo 
<YB_DUMMY p1>p2 <yb_DUMMY p3>p4 
bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
p2 p4 
bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(a)
        $src = <<<EOS
foo <YB_DUMMY p2a/> bar
foo <yB_Dummy p2b/> bar
foo <Yb_duMMY p2c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,,100,test! bar
foo !dummy,p2b,,100,test! bar
foo !dummy,p2c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #2 (list mode)(b)(pair-tag pattern)
        $src = <<<EOS
foo <YB_DUMMY p2a>pp2a</YB_DUMMY> bar
foo 
<Yb_dummY p2b>pp2b</yB_duMMY>
 bar
foo 
<YB_DUMMY p2c>
pp2c
pp2c_2
</YB_DUMMY>
 bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo !dummy,p2a,pp2a,100,test! bar
foo 
!dummy,p2b,pp2b,100,test!
 bar
foo 
!dummy,p2c,
pp2c
pp2c_2
,100,test!
 bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(b)
        $src = <<<EOS
<YB_MORE />
foo <YB_DUMMY p3a/> bar
foo <yb_duMMY p3b/> bar
foo <Yb_DUMMY p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
{$readmore_link}
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(c)
        $src = <<<EOS
foo 
<YB_DUMMY p3a>
hoge
</YB_DUMMY>
 bar
<YB_More />
foo <yb_DUMMY p3b/> bar
foo <YB_dummy p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p3a,
hoge
,100,test!
 bar
{$readmore_link}
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #3 (list mode with <yb_more>)(d) (<yb_more> internal block)
        $src = <<<EOS
foo 
<YB_Dummy p3a>
<YB_more />
hoge
</yb_DUMMY>
 bar
foo <yb_dummy p3b/> bar
foo <yb_dummy p3c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::LIST_MODE());
        $expect = <<<EOS
foo 
!dummy,p3a,
<YB_more />
hoge
,100,test!
 bar
foo !dummy,p3b,,100,test! bar
foo !dummy,p3c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #4 (detail mode with <yb_more>)(a)
        $src = <<<EOS
foo <YB_Dummy p4a/> bar
<yb_MORE />
foo <yB_dUmmy p4b/> bar
foo <yb_dummy p4c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p4a,,100,test! bar

foo !dummy,p4b,,100,test! bar
foo !dummy,p4c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #4 (detail mode with <yb_more>)(b)
        $src = <<<EOS
foo <YB_DUMMY p4a>p4a2</YB_DUMMY> bar
<yb_MORE />
foo <YB_DUMMY p4b>p4b2</YB_DUMMY> bar
foo <YB_dummy p4c>p4c2</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p4a,p4a2,100,test! bar

foo !dummy,p4b,p4b2,100,test! bar
foo !dummy,p4c,p4c2,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #4 (detail mode with <yb_more>)(c)
        $src = <<<EOS
foo <YB_DUMMY p4a>p4a2
<YB_MORE />
</YB_DUMMY> bar
foo <YB_DUMMY p4b>p4b2</yB_dummy> bar
foo <yb_dummy p4c>p4c2</yb_dummy> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p4a,p4a2
<YB_MORE />
,100,test! bar
foo !dummy,p4b,p4b2,100,test! bar
foo !dummy,p4c,p4c2,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}
        // {{{ #5 (detail mode)
        $src = <<<EOS
foo <YB_DUMMY p5a/> bar
foo <YB_DUMMY  p5b/> bar
foo <YB_DUMMY  p5c/> bar
buz
EOS;
        $src = yb_Html::convert($src, $ctx, yb_Html::DETAIL_MODE());
        $expect = <<<EOS
foo !dummy,p5a,,100,test! bar
foo !dummy,p5b,,100,test! bar
foo !dummy,p5c,,100,test! bar
buz
EOS;
        $this->assertEqual(trim($src), trim($expect));
        // }}}

        _YB('dir.plugin.html', $old);
    }

    // }}}
    // {{{ test_parse()

    function test_parse()
    {
        $old = _YB('dir.plugin.html', 
            dirname(__FILE__) . '/test_Html_Plugins');
        $ctx =& new yb_DataContext(array('id' => 100, 'name' => 'test'));
        // {{{ #1
        $src = "<yb_dummy p1/> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,,100,test!");
        // }}}
        // {{{ #2(a)
        $src = "<yb_dummy p1> p2 </yb_dummy> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1, p2 ,100,test!");
        // }}}
        // {{{ #2(b)
        $src = "<yb_dummy p1>\n p2 </yb_dummy> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,\n p2 ,100,test!");
        // }}}
        // {{{ #2(c)
        $src = "<yb_dummy p1>\n p2 \n</yb_dummy> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,\n p2 \n,100,test!");
        // }}}
        // {{{ #2(d)
        $src = "<yb_dummy p1>\n p2 \n</yb_dummy>\nbar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "\nbar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,\n p2 \n,100,test!");
        // }}}
        // {{{ #3(a)
        $src = "<yb_dummy p1></yb_dummy> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,,100,test!");
        // }}}
        // {{{ #3(b)
        $src = "<yb_dummy p1>\n</yb_dummy> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,\n,100,test!");
        // }}}
        // {{{ #3(c)
        $src = "<yb_dummy p1>\n</yb_dummy>\n bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "\n bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,\n,100,test!");
        // }}}
        // {{{ #3(d)
        $src = "<yb_dummy p1/>\n</yb_dummy>\n bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "\n</yb_dummy>\n bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1,,100,test!");
        // }}}
        // {{{ #3(e)
        $src = "<yb_dummy p1/ >\n</yb_dummy>\n bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "\n bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1/,\n,100,test!");
        // }}}
        // {{{ #4
        $src = "<h2>foobar</h2> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "<h2>foobar</h2> bar\nbuz\n");
        $this->assertEqual($result, '');
        // }}}
        // {{{ #5 (error case)
        $src = "<yb_dummy p1><h2>hoge</h2> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "<h2>hoge</h2> bar\nbuz\n");
        $this->assertEqual($result, '');
        $this->assertFalse($ctx->get('show_readmore'));
        // }}}
        // {{{ #6 (list mode with <yb_more/>)
        $src = <<<EOS
<yb_more />
foo
buz
EOS;
        $result = '';
        $this->assertFalse(yb_Html::parse(
            $src, $result, $ctx, yb_Html::LIST_MODE()));
        $this->assertTrue($ctx->get('show_readmore'));
        // }}}
        // {{{ #6 (detail mode with <yb_more/>)
        $src = "<yb_more />\nfoo\nbuz\n";
        $result = '';
        $this->assertTrue(
            yb_Html::parse($src, $result, $ctx, yb_Html::DETAIL_MODE()));
        $this->assertEqual($src, "\nfoo\nbuz\n");
        $this->assertEqual($result, "");
        $this->assertFalse($ctx->get('show_readmore'));
        // }}}
        // {{{ #7
        $src = "<yb_dummy p1/>";
        $result = '';
        $this->assertTrue(
            yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE()));
        $this->assertEqual($src, "");
        $this->assertEqual($result, "!dummy,p1,,100,test!");
        // }}}

        _YB('dir.plugin.html', $old);
    }

    // }}}
    // {{{ test_parse_large_case()

    function test_parse_large_case()
    {
        $old = _YB('dir.plugin.html', 
            dirname(__FILE__) . '/test_Html_Plugins');
        $ctx =& new yb_DataContext(array('id' => 100, 'name' => 'test'));
        // {{{ #1
        $src = "<YB_DUMMY p1/> bar\n<p>buz</p>\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\n<p>buz</p>\n");
        $this->assertEqual($result, "!dummy,p1,,100,test!");
        // }}}
        // {{{ #2(a)
        $src = "<YB_DUMMY p1> p2 </YB_DUMMY> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1, p2 ,100,test!");
        // }}}
        // {{{ #2(b)
        $src = "<YB_DUMMY p1> p2 </yb_dummy> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, " bar\nbuz\n");
        $this->assertEqual($result, "!dummy,p1, p2 ,100,test!");
        // }}}
        // {{{ #3
        $src = "<H2>foobar</H2> bar\nbuz\n";
        $result = '';
        yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE());
        $this->assertEqual($src, "<H2>foobar</H2> bar\nbuz\n");
        $this->assertEqual($result, '');
        $this->assertFalse($ctx->get('show_readmore'));
        // }}}
        // {{{ #6 (list mode with <yb_more/>)
        $src = <<<EOS
<YB_MORE />
foo
buz
EOS;
        $result = '';
        $this->assertFalse(yb_Html::parse(
            $src, $result, $ctx, yb_Html::LIST_MODE()));
        $this->assertTrue($ctx->get('show_readmore'));
        // }}}
        // {{{ #6 (detail mode with <yb_more/>)
        $src = "<YB_MORE />\nfoo\nbuz\n";
        $result = '';
        $this->assertTrue(
            yb_Html::parse($src, $result, $ctx, yb_Html::DETAIL_MODE()));
        $this->assertEqual($src, "\nfoo\nbuz\n");
        $this->assertEqual($result, "");
        $this->assertFalse($ctx->get('show_readmore'));
        // }}}
        // {{{ #7
        $src = "<YB_DUMMY p1/>";
        $result = '';
        $this->assertTrue(
            yb_Html::parse($src, $result, $ctx, yb_Html::LIST_MODE()));
        $this->assertEqual($src, "");
        $this->assertEqual($result, "!dummy,p1,,100,test!");
        // }}}

        _YB('dir.plugin.html', $old);
    }

    // }}}
    // {{{ test_invoke_html_plugin()

    function test_invoke_html_plugin()
    {
        $old = _YB('dir.plugin.html', 
            dirname(__FILE__) . '/test_Html_Plugins');
        $ctx =& new yb_DataContext(array('id' => 100, 'name' => 'test'));

        $ret = yb_Html::invoke_html_plugin('yb_dummy', 'p1', 'p2', $ctx);
        $this->assertEqual($ret, "!dummy,p1,p2,100,test!");

        $ret = yb_Html::invoke_html_plugin('yb_dummy2', 'p3', 'p4', $ctx);
        $this->assertEqual($ret, "!dummy2,p3,p4,100,test!");

        // error : plugin function is not defined.
        $ret = yb_Html::invoke_html_plugin('yb_dummy4', 'p3', 'p4', $ctx);
        $this->assertEqual($ret, '');

        _YB('dir.plugin.html', $old);
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
