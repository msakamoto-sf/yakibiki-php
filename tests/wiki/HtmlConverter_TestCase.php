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

class wiki_HtmlConverter_TestCase extends UnitTestCase
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
    // {{{ test_t_string()

    function test_t_string()
    {
        $did = 100;
        $pagename = "Test Page";

        // #0
        $src = "source text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source text");

        // #1
        $src = "source & text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source &amp; text");

        // #2
        $src = "source &amp; text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source &amp; text");

        // #3
        $src = "<source> &#x0A; &#123; <text/>";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "&lt;source&gt; &#x0A; &#123; &lt;text/&gt;");

    }

    // }}}
    // {{{ test_t_url()

    function test_t_url()
    {
        $did = 100;
        $pagename = "Test Page";

        $src = "see & next goto : http://example.com/ examples.";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 'see &amp; next goto : <a class="externallink" href="http://example.com/" target="_blank">http://example.com/</a> examples.');

    }

    // }}}
    // {{{ test_t_mail()

    function test_t_mail()
    {
        $did = 100;
        $pagename = "Test Page";

        $src = "mailto : user01@example.com (don't send spam!).";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 'mailto : <a class="maillink" href="mailto:%75%73%65%72%30%31%40%65%78%61%6d%70%6c%65%2e%63%6f%6d">&#x75;&#x73;&#x65;&#x72;&#x30;&#x31;&#x40;&#x65;&#x78;&#x61;&#x6d;&#x70;&#x6c;&#x65;&#x2e;&#x63;&#x6f;&#x6d;</a> (don&#039;t send spam!).');

    }

    // }}}
    // {{{ test_t_strong()

    function test_t_strong()
    {
        $did = 100;
        $pagename = "Test Page";

        // #0
        $src = "source * strong * text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<em> strong </em>text");

        // #1
        $src = "source ** strong ** text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<strong> strong </strong>text");

        // #2
        $src = "source *** strong *** text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<strong>* strong *</strong>text");

        // #3
        $src = "source *** strong text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<em>*</em>strong text");
    }

    // }}}
    // {{{ test_t_italic()

    function test_t_italic()
    {
        $did = 100;
        $pagename = "Test Page";

        // #0
        $src = "source ''strong'' text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<strong>strong</strong>text");

        // #1
        $src = "source '''strong''' text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<em>strong</em>text");

        // #2
        $src = "source ''''strong'''' text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<em>&#039;strong&#039;</em>text");

        // #3
        $src = "source '''''' strong text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<strong>&#039;&#039;</strong>strong text");
    }

    // }}}
    // {{{ test_t_del()

    function test_t_del()
    {
        $did = 100;
        $pagename = "Test Page";

        // #0
        $src = "source %%deleted text%% text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<del>deleted text</del>text");

        // #1
        $src = "source %%%deleted text%%% text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<del>%deleted text%</del>text");

        // #3
        $src = "source %%%% deleted text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source %%%% deleted text");

        // #4
        $src = "source %%%%% deleted text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<del>%</del>deleted text");

        // #5
        $src = "source %%%%%% deleted text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, "source<del>%%</del>deleted text");
    }

    // }}}
    // {{{ test_t_bracketname()

    function test_t_bracketname()
    {
        $did = 100;
        $pagename = "Test Page";

        // #1-a PageName without Alias
        $src = "[[BracketName]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link BracketName ></yb_link> text");

        // #1-b PageName with Alias
        $src = "[[Alias \"<&'Name > BracketName]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link BracketName >Alias \"<&'Name</yb_link> text");

        // #2-a External URL without Alias
        $src = "[[http://example.com/?%4A%4B]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, '<a class="externallink" href="http://example.com/?%4A%4B" target="_blank">http://example.com/?%4A%4B</a> text');

        // #2-b External URL with Alias
        $src = "[[Alias \"<&'Name>http://example.com/?%4A%4B]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, '<a class="externallink" href="http://example.com/?%4A%4B" target="_blank">Alias &quot;&lt;&amp;&#039;Name</a> text');

        // #3-a Mail Address without Alias
        $src = "[[user01@example.com]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, '<a class="maillink" href="mailto:%75%73%65%72%30%31%40%65%78%61%6d%70%6c%65%2e%63%6f%6d">&#x75;&#x73;&#x65;&#x72;&#x30;&#x31;&#x40;&#x65;&#x78;&#x61;&#x6d;&#x70;&#x6c;&#x65;&#x2e;&#x63;&#x6f;&#x6d;</a> text');

        // #3-b Mail Address with Alias
        $src = "[[Alias \"<&'Name>user01@example.com]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, '<a class="maillink" href="mailto:%75%73%65%72%30%31%40%65%78%61%6d%70%6c%65%2e%63%6f%6d">Alias &quot;&lt;&amp;&#039;Name</a> text');

        // #4-a YakiBiki data ID without Alias
        $src = "[[30]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link 30 ></yb_link> text");

        // #4-b YakiBiki data ID with Alias
        $src = "[[Alias \"<&'Name > 30]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link 30 >Alias \"<&'Name</yb_link> text");

        // #5-a YakiBiki internal relative path without Alias
        $src = "[[yb://dir1/dir2/path]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link yb://dir1/dir2/path ></yb_link> text");

        // #5-b YakiBiki internal relative path with Alias
        $src = "[[Alias \"<&'Name > yb://dir1/dir2/path]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link yb://dir1/dir2/path >Alias \"<&'Name</yb_link> text");

        // #5-c YakiBiki internal relative path end with "/"
        $src = "[[Alias \"<&'Name > yb://dir1/dir2/]] text";
        $r = wiki_Parser::convert_inline($src, $did, $pagename);
        $this->assertEqual($r, 
            "<yb_link yb://dir1/dir2/ >Alias \"<&'Name</yb_link> text");

    }

    // }}}
    // {{{ test_simple_heading()

    function test_simple_heading()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
* Level1

** Level1_1

** Level1_2

*** Level1_2_1

*** Level1_2_2

** Level1_3

* Level2

* Level3

** Level3_1

* Level4

** Level4_1

** Level4_2

* Level5

***** Level5_x

TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<h3 id="id23f220">Level1</h3>

<h4 id="idb73a42">Level1_1</h4>

<h4 id="id5fe77a">Level1_2</h4>

<h5 id="id719f9c">Level1_2_1</h5>

<h5 id="ida66df1">Level1_2_2</h5>

<h4 id="id561d3f">Level1_3</h4>

<h3 id="idf75ea2">Level2</h3>

<h3 id="id9d8478">Level3</h3>

<h4 id="id7038dc">Level3_1</h4>

<h3 id="iddc596b">Level4</h3>

<h4 id="id56ca8f">Level4_1</h4>

<h4 id="idfa2f3f">Level4_2</h4>

<h3 id="id62d83a">Level5</h3>

<h6 id="ida81d79">* Level5_x</h6>
TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_comment()

    function test_simple_comment()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
paragraph1 line1.
paragraph1 line2.
paragraph1 line3.

// comment line1.
// comment line2.
// comment line3.

paragraph2 line1.
paragraph2 line2.
paragraph2 line3.

// comment line1.
// comment line2.
// comment line3.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<p class="paragraph">
paragraph1 line1.
<br />
paragraph1 line2.
<br />
paragraph1 line3.
<br />
</p>





<p class="paragraph">
paragraph2 line1.
<br />
paragraph2 line2.
<br />
paragraph2 line3.
<br />
</p>




TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_horizon_paragraph()

    function test_simple_horizon_paragraph()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
----
paragraph1 line1.
paragraph1 line2.
paragraph1 line3.

-----

paragraph2 line1.
paragraph2 line2.
paragraph2 line3.

paragraph3 line1.
paragraph3 line2.
paragraph3 line3.

----
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<hr />
<p class="paragraph">
paragraph1 line1.
<br />
paragraph1 line2.
<br />
paragraph1 line3.
<br />
</p>

<hr />

<p class="paragraph">
paragraph2 line1.
<br />
paragraph2 line2.
<br />
paragraph2 line3.
<br />
</p>

<p class="paragraph">
paragraph3 line1.
<br />
paragraph3 line2.
<br />
paragraph3 line3.
<br />
</p>

<hr />
TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_pre_blockquote()

    function test_simple_pre_blockquote()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
 <html>
\t<head>
\t</head>
 * Strong Text *
 '' Strong Text ''
 %% Deleted Text %%
 -----
 </html>

>BlockQuote1.
>BlockQuote2. * Strong Text *
>BlockQuote3. '' Strong Text ''
>BlockQuote4. %% Deleted Text %%
> <html>
> </html>
>----
>http://example.com/
>>BlockQuote Level2.
>>BlockQuote Level2.
>
>Level1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<pre>&lt;html&gt;
&lt;head&gt;
&lt;/head&gt;
* Strong Text *
&#039;&#039; Strong Text &#039;&#039;
%% Deleted Text %%
-----
&lt;/html&gt;
</pre>

<blockquote><p class="paragraph">
BlockQuote1.
<br />
BlockQuote2.<em> Strong Text </em>
<br />
BlockQuote3.<strong> Strong Text </strong>
<br />
BlockQuote4.<del> Deleted Text </del>
<br />
</p>
<pre>&lt;html&gt;
&lt;/html&gt;
</pre>
<hr />
<p class="paragraph">
<a class="externallink" href="http://example.com/" target="_blank">http://example.com/</a>
<br />
</p>
<blockquote><p class="paragraph">
BlockQuote Level2.
<br />
BlockQuote Level2.
<br />
</p></blockquote>

<p class="paragraph">
Level1.<br />
</p></blockquote>
TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_ul_ol()

    function test_simple_ul_ol()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
- item1
- item2
-- item2_1
-- item2_2
--- item2_2_1
--- item2_2_2
---- item2_2_3
-- item2_3
- item3

+ item1
+ item2
++ item2_1
++ item2_2
+++ item2_2_1
+++ item2_2_2
++++ item2_2_3
++ item2_3
+ item3

- item1
- item2
-+ item2_1
-+ item2_2
-++ item2_2_1
-++ item2_2_2
-+ item2_3
- item3

TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<ul><li> item1</li>
<li> item2<ul><li> item2_1</li>
<li> item2_2<ul><li> item2_2_1</li>
<li> item2_2_2<ul><li> item2_2_3</li></ul></li></ul></li>
<li> item2_3</li></ul></li>
<li> item3</li></ul>

<ol><li> item1</li>
<li> item2<ol><li> item2_1</li>
<li> item2_2<ol><li> item2_2_1</li>
<li> item2_2_2<ol><li> item2_2_3</li></ol></li></ol></li>
<li> item2_3</li></ol></li>
<li> item3</li></ol>

<ul><li> item1</li>
<li> item2<ol><li> item2_1</li>
<li> item2_2<ol><li> item2_2_1</li>
<li> item2_2_2</li></ol></li>
<li> item2_3</li></ol></li>
<li> item3</li></ul>
TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_dl()

    function test_simple_dl()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
:dt1:
description1_1.
description1_2.
:dt2:description2.
:dt3:
description3_1.
description3_2.

----

:dt4: description4.
:dt5: description5.

----

:dt6: description6.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<dl>
<dt>dt1</dt>
<dd>description1_1.</dd>
<dd>description1_2.</dd>
<dt>dt2</dt>
<dd>description2.</dd>
<dt>dt3</dt>
<dd>description3_1.</dd>
<dd>description3_2.</dd>
</dl>

<hr />

<dl>
<dt>dt4</dt>
<dd>description4.</dd>
<dt>dt5</dt>
<dd>description5.</dd>
</dl>

<hr />

<dl>
<dt>dt6</dt>
<dd>description6.</dd>
</dl>
TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_table()

    function test_simple_table()
    {
        $did = 100;
        $pagename = "Test Page";

        // {{{ source text
        $src = <<<TEXT
|LEFT: col1|right: col2|BGCOLOR(red): col3|CH
|col4|col5| * col6 * |
|CENTER,bgcolor(black): http://example.com/|col8|col9|

----

|L: col10|c: col11|r: col12|h
|col13|col14|col15|l
|col16|col17|col18|r
|col19|col20|col21|c

TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<table>
	<tr>
		<th style=" text-align: left;"> col1</th>
		<th style=" text-align: right;"> col2</th>
		<th style=" text-align: center; background-color: red;"> col3</th>
	</tr>
	<tr>
		<td>col4</td>
		<td>col5</td>
		<td><em> col6 </em></td>
	</tr>
	<tr>
		<td style=" text-align: center; background-color: black;"> <a class="externallink" href="http://example.com/" target="_blank">http://example.com/</a></td>
		<td>col8</td>
		<td>col9</td>
	</tr>
</table>

<hr />

<table>
	<tr>
		<th>L: col10</th>
		<th>c: col11</th>
		<th>r: col12</th>
	</tr>
	<tr>
		<td style=" text-align: left;">col13</td>
		<td style=" text-align: left;">col14</td>
		<td style=" text-align: left;">col15</td>
	</tr>
	<tr>
		<td style=" text-align: right;">col16</td>
		<td style=" text-align: right;">col17</td>
		<td style=" text-align: right;">col18</td>
	</tr>
	<tr>
		<td style=" text-align: center;">col19</td>
		<td style=" text-align: center;">col20</td>
		<td style=" text-align: center;">col21</td>
	</tr>
</table>
TEXT;
        // }}}

        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_wiki_footnote()

    function test_wiki_footnote()
    {
        // {{{ singleton() and reserve()
        $ft1 =& wiki_Footnote::singleton();
        $fts[1][] = $ft1->reserve(1);
        $fts[1][] = $ft1->reserve(1);
        $fts[2][] = $ft1->reserve(2);
        $fts[2][] = $ft1->reserve(2);

        $ft2 =& wiki_Footnote::singleton();
        $fts[1][] = $ft2->reserve(1);
        $fts[2][] = $ft2->reserve(2);

        $fts[1][] = $ft1->reserve(1);
        $fts[2][] = $ft1->reserve(2);

        $fts[1][] = $ft2->reserve(1);
        $fts[2][] = $ft2->reserve(2);

        foreach ($fts as $did => $_fts) {
            $this->assertEqual(count($_fts), 5);
            foreach ($_fts as $i => $n) {
                $this->assertEqual($n, $i + 1);
            }
        }
        // }}}
        // {{{ setnote()
        $this->assertEqual(
            $ft1->setnote(1, "<b>footnote1</b>", 1), 
            '<span class="hidden">(</span><a class="footnote" href="#footnote_1_1" id="footnote_1_1_r"  title="footnote1">*1</a><span class="hidden">)</span>'
        );
        $this->assertEqual(
            $ft2->setnote(1, "<b>footnote2</b>", 2), 
            '<span class="hidden">(</span><a class="footnote" href="#footnote_1_2" id="footnote_1_2_r"  title="footnote2">*2</a><span class="hidden">)</span>'
        );
        $ft1->setnote(1, "<b>footnote3</b>", 3);
        $ft1->setnote(1, "<b>footnote4</b>", 4);
        $ft1->setnote(1, "<b>footnote5</b>", 5);
        $this->assertEqual($ft1->setnote(1, "<b>footnote6</b>", 6), "");

        $this->assertEqual(
            $ft2->setnote(2, "<b>footnote21</b>", 1), 
            '<span class="hidden">(</span><a class="footnote" href="#footnote_2_1" id="footnote_2_1_r"  title="footnote21">*1</a><span class="hidden">)</span>'
        );
        $this->assertEqual(
            $ft1->setnote(2, "<b>footnote22</b>", 2), 
            '<span class="hidden">(</span><a class="footnote" href="#footnote_2_2" id="footnote_2_2_r"  title="footnote22">*2</a><span class="hidden">)</span>'
        );
        $ft2->setnote(2, "<b>footnote23</b>", 3);
        $ft2->setnote(2, "<b>footnote24</b>", 4);
        $ft2->setnote(2, "<b>footnote25</b>", 5);
        $this->assertEqual($ft2->setnote(2, "<b>footnote26</b>", 6), "");
        // }}}
        // {{{ getnote()
        $r1 = <<<TEXT
<div class="footnote">
<a id="footnote_1_1" href="#footnote_1_1_r">*1</a>: <b>footnote1</b><br />
<a id="footnote_1_2" href="#footnote_1_2_r">*2</a>: <b>footnote2</b><br />
<a id="footnote_1_3" href="#footnote_1_3_r">*3</a>: <b>footnote3</b><br />
<a id="footnote_1_4" href="#footnote_1_4_r">*4</a>: <b>footnote4</b><br />
<a id="footnote_1_5" href="#footnote_1_5_r">*5</a>: <b>footnote5</b>
</div>
TEXT;
        $this->assertEqual($ft1->getnote(1), $r1);

        $r2 = <<<TEXT
<div class="footnote">
<a id="footnote_2_1" href="#footnote_2_1_r">*1</a>: <b>footnote21</b><br />
<a id="footnote_2_2" href="#footnote_2_2_r">*2</a>: <b>footnote22</b><br />
<a id="footnote_2_3" href="#footnote_2_3_r">*3</a>: <b>footnote23</b><br />
<a id="footnote_2_4" href="#footnote_2_4_r">*4</a>: <b>footnote24</b><br />
<a id="footnote_2_5" href="#footnote_2_5_r">*5</a>: <b>footnote25</b>
</div>
TEXT;
        $this->assertEqual($ft2->getnote(2), $r2);
        // }}}
    }

    // }}}
    // {{{ test_simple_footnote()

    function test_simple_footnote()
    {
        //#1 ($did = 100)
        // {{{ source text
        $src = <<<TEXT
abc(( * footnote1_1 * ))def

ghi((footnote1_2: http://example.com/ refer.))jkl
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<p class="paragraph">
abc<span class="hidden">(</span><a class="footnote" href="#footnote_100_1" id="footnote_100_1_r"  title=" footnote1_1 ">*1</a><span class="hidden">)</span>def
<br />
</p>

<p class="paragraph">
ghi<span class="hidden">(</span><a class="footnote" href="#footnote_100_2" id="footnote_100_2_r"  title="footnote1_2: http://example.com/ refer.">*2</a><span class="hidden">)</span>jkl<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, 100, "Test Page1");
        $this->assertEqual($r, $result);

        //#2 ($did = 200)
        // {{{ source text
        $src = <<<TEXT
abc(( * footnote2_1 * ))def

ghi((footnote2_2: http://example.com/ refer.))jkl
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<p class="paragraph">
abc<span class="hidden">(</span><a class="footnote" href="#footnote_200_1" id="footnote_200_1_r"  title=" footnote2_1 ">*1</a><span class="hidden">)</span>def
<br />
</p>

<p class="paragraph">
ghi<span class="hidden">(</span><a class="footnote" href="#footnote_200_2" id="footnote_200_2_r"  title="footnote2_2: http://example.com/ refer.">*2</a><span class="hidden">)</span>jkl<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, 200, "Test Page2");
        $this->assertEqual($r, $result);

        $ft =& wiki_Footnote::singleton();

        // {{{ footnote html ($did = 100)
        $result = <<<TEXT
<div class="footnote">
<a id="footnote_100_1" href="#footnote_100_1_r">*1</a>: <em> footnote1_1 </em><br />
<a id="footnote_100_2" href="#footnote_100_2_r">*2</a>: footnote1_2: <a class="externallink" href="http://example.com/" target="_blank">http://example.com/</a> refer.
</div>
TEXT;
        // }}}
        $this->assertEqual($ft->getnote(100), $result);

        // {{{ footnote html ($did = 200)
        $result = <<<TEXT
<div class="footnote">
<a id="footnote_200_1" href="#footnote_200_1_r">*1</a>: <em> footnote2_1 </em><br />
<a id="footnote_200_2" href="#footnote_200_2_r">*2</a>: footnote2_2: <a class="externallink" href="http://example.com/" target="_blank">http://example.com/</a> refer.
</div>
TEXT;
        // }}}
        $this->assertEqual($ft->getnote(200), $result);
    }

    // }}}
    // {{{ test_simple_inline_plugin()

    function test_simple_inline_plugin()
    {
        $did = 100;
        $pagename = "Test Page";

        // #1 plugin is found, function is found, all correct.
        // {{{ source text
        $src = <<<TEXT
paragraph1 &dummy1(a,b, c) { test, * Strong Text * test. } line1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<p class="paragraph">
paragraph1 !dummy1_inline|a,b, c| test, * Strong Text * test. |100|Test Page! line1.<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);

        // #2 pattern2.
        // {{{ source text
        $src = <<<TEXT
paragraph1 &dummy1(a,b, c) line1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<p class="paragraph">
paragraph1 !dummy1_inline|a,b, c||100|Test Page! line1.<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);

        // #3 plugin is found, function is not found.
        // {{{ source text
        $src = <<<TEXT
paragraph1 &dummy2(a,b, c) { test, * Strong Text * test. } line1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<p class="paragraph">
paragraph1 <span class="warning">(plugin function: "yb_plugin_wiki_dummy2_invoke_inline" for plugin: "dummy2" was not found!!)</span>&amp;dummy2(a,b, c) { test, * Strong Text * test. } line1.<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);

        // #4 plugin is not found. (!!PHP warning occurr, so, comment out.!!)
//        // {{{ source text
//        $src = <<<TEXT
//paragraph1 &dummy4(a,b, c) { test, * Strong Text * test. } line1.
//TEXT;
//        // }}}
//        // {{{ result text
//        $result = <<<TEXT
//<p class="paragraph">
//paragraph1 <span class="warning">(plugin file: "yb_plugin_wiki_dummy4.php" for plugin: "dummy4" was not found!!)</span>&amp;dummy4(a,b, c) { test, * Strong Text * test. } line1.<br />
//</p>
//TEXT;
//        // }}}
//        $r = wiki_Parser::convert_block($src, $did, $pagename);
//        $this->assertEqual($r, $result);
    }

    // }}}
    // {{{ test_simple_block_plugin()

    function test_simple_block_plugin()
    {
        $did = 100;
        $pagename = "Test Page";

        // #1 plugin is found, function is found, all correct.
        // {{{ source text
        $src = <<<TEXT
#dummy1 |a, b,c|>
paragraph1 line1.
paragraph1 &dummy1(a,b, c) { test, * Strong Text * test. } line2.
paragraph1 line3.
||<

paragraph2 line1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
!dummy1_block|a, b,c|
paragraph1 line1.
paragraph1 &dummy1(a,b, c) { test, * Strong Text * test. } line2.
paragraph1 line3.
|100|Test Page!

<p class="paragraph">
paragraph2 line1.<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);

        // #2 pattern2.
        // {{{ source text
        $src = <<<TEXT
#dummy1 |a, b,c| 

paragraph1 line1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
!dummy1_block|a, b,c||100|Test Page!

<p class="paragraph">
paragraph1 line1.<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);

        // #3 plugin is found, function is not found.
        // {{{ source text
        $src = <<<TEXT
#dummy3 |a, b,c|>
paragraph1 line1.
paragraph1 &dummy1(a,b, c) { test, * Strong Text * test. } line2.
paragraph1 line3.
||<

paragraph2 line1.
TEXT;
        // }}}
        // {{{ result text
        $result = <<<TEXT
<span class="warning">(plugin function: "yb_plugin_wiki_dummy3_invoke_block" for plugin: "dummy3" was not found!!)</span>#dummy3 |a, b,c|&gt;<br />
paragraph1 line1.<br />
paragraph1 &amp;dummy1(a,b, c) { test, * Strong Text * test. } line2.<br />
paragraph1 line3.<br />
||&lt;<br />


<p class="paragraph">
paragraph2 line1.<br />
</p>
TEXT;
        // }}}
        $r = wiki_Parser::convert_block($src, $did, $pagename);
        $this->assertEqual($r, $result);

        // #4 plugin is not found. (!!PHP warning occurr, so, comment out.!!)
//        // {{{ source text
//        $src = <<<TEXT
//#dummy4 (a, b,c)
//
//paragraph2 line1.
//TEXT;
//        // }}}
//        // {{{ result text
//        $result = <<<TEXT
//<span class="warning">(plugin file: "yb_plugin_wiki_dummy4.php" for plugin: "dummy4" was not found!!)</span>#dummy4 (a, b,c)<br />
//
//
//<p class="paragraph">
//paragraph2 line1.<br />
//</p>
//TEXT;
//        // }}}
//        $r = wiki_Parser::convert_block($src, $did, $pagename);
//        $this->assertEqual($r, $result);
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
