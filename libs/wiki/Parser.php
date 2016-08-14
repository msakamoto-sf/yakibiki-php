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
 */

/**
 * Wiki Parser
 *
 * Special Thanks to KinoWiki.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Parser.php 466 2008-11-22 02:15:37Z msakamoto-sf $
 */


/**
 * Regular expression for URL
 * (we don't include "()" on purpose.)
 */
define('WIKI_EXP_URL', 
    '(?:s?https?|ftp|file):\/\/[-a-zA-Z0-9_:@&?=+,.!\/~*%$\';#]+');

/**
 * Regular expression for e-mail
 */
define('WIKI_EXP_MAIL', 
    '[a-zA-Z0-9_][-.a-zA-Z0-9_]*\@[-a-zA-Z0-9]+(?:\.[-a-zA-Z0-9_]+)+');

require_once('wiki/CharEntityRef.php');
require_once('wiki/HtmlConverter.php');

// {{{ wiki_Context

/**
 * Context container (page name)
 */
class wiki_Context
{
    /**
     * Page name as top level page name.
     *
     * @access public
     * @type string
     */
    var $pagename;

    /**
     * Data ID.
     *
     * @access public
     * @type integer
     */
    var $did;

    function wiki_Context($_did, $_pagename)
    {
        $this->did = $_did;
        $this->pagename = yb_Util::resolvepath($_pagename);
    }
}

// }}}
// {{{ wiki_Parser

/**
 * Parser main class
 */
class wiki_Parser
{
    // {{{ convert_block()

    /**
     * Convert Wiki source to html as Block Element.
     * 
     * @static
     * @access public
     * @param string source
     * @param integer data id
     * @param string page name
     * @return string html text data
     */
    function convert_block($source, $did, $pagename)
    {
        $el =& wiki_Parser::parse_block($source, $did, $pagename);
        return wiki_HtmlConverter::visit($el);
    }

    // }}}
    // {{{ convert_inline()

    /**
     * Convert Wiki source to html as Inline Element.
     *
     * @static
     * @access public
     * @param string source
     * @param integer data id
     * @param string page name
     * @return string html text data
     */
    function convert_inline($source, $did, $pagename)
    {
        $el =& wiki_Parser::parse_inline($source, $did, $pagename);
        return wiki_HtmlConverter::visit($el);
    }

    // }}}
    // {{{ parse_block()

    /**
     * Convert Wiki source to internal expression as Block Element.
     *
     * @static
     * @access public
     * @param string source
     * @param integer data id
     * @param string page name
     * @return wiki_T_Element's DOM tree
     */
    function &parse_block($source, $did, $pagename)
    {
        $_source = preg_replace('/\r?\n/', "\n", $source);
        $_ctx =& new wiki_Context($did, $pagename);
        $ret =& wiki_T_Body::parse($_source, $_ctx);
        return $ret;
    }

    // }}}
    // {{{ parse_inline()

    /**
     * Convert Wiki source to internal expression as Inline Element.
     *
     * @static
     * @access public
     * @param string source
     * @param integer data id
     * @param string page name
     * @return wiki_T_Element's DOM tree
     */
    function &parse_inline($source, $did, $pagename)
    {
        $_source = preg_replace('/\r?\n/', "\n", $source);
        $_ctx =& new wiki_Context($did, $pagename);
        $ret =& wiki_T_Line::parse($_source, $_ctx);
        return $ret;
    }

    // }}}
}

// }}}
// {{{ wiki_T_Element

/**
 * Internal expression element class
 *
 * @abstract
 */
class wiki_T_Element
{
    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**
     * Original source
     *
     * @type string
     */
    var $_source;

    /**
     * Context object
     *
     * @type wiki_Context
     */
    var $_context;

    /**
     * Including elements
     *
     * @type wiki_T_Element
     */
    var $_elements = array();

    /**
     * Reference to parent element
     *
     * @type wiki_T_Element
     */
    var $_parent = null;

    /**
     * Reference to previous element
     *
     * @type wiki_T_Element
     */
    var $_prev = null;

    /**
     * Reference to next element
     *
     * @type wiki_T_Element
     */
    var $_next = null;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    function getsource(){ return $this->_source; }
    function &getcontext(){ return $this->_context; }
    function &getelements(){ return $this->_elements; }
    function &getelem(){ return $this->_elements[0]; }
    function &getparent(){ return $this->_parent; }
    function &getprev(){ return $this->_prev; }
    function &getnext(){ return $this->_next; }
    // {{{ accept()

    /**
     * Invoke converter
     *
     * @param Converter converter
     */
    function accept(&$v)
    {
        $call = 'visit' . str_replace('wiki_', '', get_class($this));
        return $v->$call($this);
    }

    // }}}
    // {{{ undo()

    /**
     * Reverse from parsed string data to original string.
     *
     * @param string parsed string data reference.
     */
    function undo(&$source)
    {
        $source = $this->_source . $source;
    }

    // }}}

    /**#@-*/

    /**#@+
     * @access protected
     */

    // {{{ wiki_T_Element()

    function wiki_T_Element($source, &$context)
    {
        $this->_source = $source;
        $this->_context =& $context;
    }

    // }}}
    // {{{ addelement()

    /**
     * Store element to internal including elements collection.
     *
     * @param wiki_T_Element
     */
    function addelement(&$elem)
    {
        $elem->_parent =& $this;
        $this->_elements[] =& $elem;
        $last = count($this->_elements) - 1;
        if ($last != 0) {
            $_el1 =& $this->_elements[$last-1];
            $_el1->_next =& $this->_elements[$last];
            $_el2 =& $this->_elements[$last];
            $_el2->_prev =& $this->_elements[$last-1];
        }
    }

    // }}}

    /**#@-*/
}

// }}}
// {{{ wiki_T_BlockElement extends wiki_T_Element

/**
 * Represent Block type element
 *
 * @abstract
 */
class wiki_T_BlockElement extends wiki_T_Element
{
    /**
     * @static
     * @abstract
     * @access public
     * @param string parsed parts of text is removed.
     * @param wiki_Context
     * @return wiki_T_Element reference.
     *                           If parse error, then null.
     */
    //function &parse(&$source, &$context);
}

// }}}
// {{{ wiki_T_InlineElement extends wiki_T_Element

/**
 * Represent Inline type element
 *
 * @abstract
 */
class wiki_T_InlineElement extends wiki_T_Element
{
    /**
     * @static
     * @abstract
     * @access public
     * @param string parsed parts of text is removed.
     * @param wiki_Context
     * @return wiki_T_Element reference.
     *                           If parse error, then null.
     */
    //function &parse(&$source, &$context);
}

// }}}
// {{{ wiki_T_Body extends wiki_T_BlockElement

/**
 * Represent block elements collection.
 */
class wiki_T_Body extends wiki_T_BlockElement
{
    // {{{ wiki_T_Body()

    function wiki_T_Body($source, &$context, &$elements)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $sz = count($elements);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($elements[$i]);
        }
    }

    // }}}
    // {{{ &parse()

    function &parse(&$source, &$context)
    {
        $classlist = array(
            'wiki_T_Empty', 
            'wiki_T_Block', 
            'wiki_T_Paragraph'
        );

        $_source = $source;
        $elements = array();
        while ($source != '') {
            foreach ($classlist as $class) {
                // NOTE: call_user_func(_array) CAN'T return reference.
                // so, we can use only eval().
                eval("\$ret =& $class::parse(\$source, \$context);");
                if ($ret != null) {
                    $elements[] =& $ret;
                    break;
                }
            }
        }

        $result =& new wiki_T_Body($_source, $context, $elements);
        return $result;
    }

    // }}}
}

// }}}
// {{{  wiki_T_Empty extends wiki_T_BlockElement

/**
 * Represent block elements collection.
 */
class wiki_T_Empty extends wiki_T_BlockElement
{
    function &parse(&$source, &$context)
    {
        $result = null;
        if (preg_match('/^[\t ]*(?:\n|$)/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $result =& new wiki_T_Empty($m[0], $context);
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_Block extends wiki_T_BlockElement

/**
 * Represent One block element.
 *
 * @abstract
 */
class wiki_T_Block extends wiki_T_BlockElement
{
    function &parse(&$source, &$context)
    {
        $classlist = array(
            'wiki_T_Heading',
            'wiki_T_Horizon',
            'wiki_T_Pre',
            'wiki_T_BlockQuote',
            'wiki_T_UL',
            'wiki_T_OL',
            'wiki_T_DL',
            'wiki_T_Table',
            'wiki_T_BlockPlugin',
            'wiki_T_Comment',
        );

        $ret = null;
        foreach ($classlist as $class) {
            // NOTE: call_user_func(_array) CAN'T return reference.
            // so, we can use only eval().
            eval("\$ret =& $class::parse(\$source, \$context);");
            if ($ret != null) {
                return $ret;
            }
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_Heading extends wiki_T_Block

/**
 * Represent Heading element.
 */
class wiki_T_Heading extends wiki_T_Block
{
    /**
     * Heading Level
     *
     * @access protected
     * @type string
     */
    var $_level;

    function getlevel(){ return $this->_level; }
    function getstring()
    {
        $e =& $this->_elements[0];
        return $e->getsource();
    }

    /**
     * @access protected
     */
    function wiki_T_Heading($source, &$context, $level, &$subject)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->_level = $level;
        $this->addelement($subject);
    }

    function &parse(&$source, &$context)
    {
        $ret = null;
        $r = '/^([*]{1,4})[\t ]*(.+?)[\t ]*(?:\n|$)/';
        if (preg_match($r, $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $subj =& wiki_T_Line::parse($m[2], $context);
            $ret =& new wiki_T_Heading(
                $m[0], 
                $context, 
                strlen($m[1]), 
                $subj
            );
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_Horizon extends wiki_T_Block

/**
 * Represent horizontal bar.
 */
class wiki_T_Horizon extends wiki_T_Block
{
    function &parse(&$source, &$context)
    {
        $ret = null;
        $r = '/^[-]{4,}[\t ]*(?:\n|$)/';
        if (preg_match($r, $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $ret =& new wiki_T_Horizon($m[0], $context);
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_Pre extends wiki_T_Block

/**
 * Represent pre-formatted text element.
 */
class wiki_T_Pre extends wiki_T_Block
{
    /**
     * @access protected
     * @type string
     */
    var $_text;

    function gettext(){ return $this->_text; }

    /**
     * @access protected
     */
    function wiki_T_Pre($source, &$context, $text)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->_text = $text;
    }

    function &parse(&$source, &$context)
    {
        $ret = null;
        if (preg_match('/^(?:[ \t].*?(?:\n|$))+/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $text = preg_replace('/^[ \t](.*?(?:\n|$))/m', '\1', $m[0]);
            $ret =& new wiki_T_Pre($m[0], $context, $text);
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_BlockQuote extends wiki_T_Block

/**
 * Represent quoted block element.
 */
class wiki_T_BlockQuote extends wiki_T_Block
{
    /**
     * @access protected
     */
    function wiki_T_BlockQuote($source, &$context, &$body)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($body);
    }

    function &parse(&$source, &$context)
    {
        $ret = null;

        if (preg_match('/^(?:[>].*?(?:\n|$))+/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $text = preg_replace('/^[>](.*?(?:\n|$))/m', '\1', $m[0]);
            $ret =& new wiki_T_BlockQuote(
                $m[0], 
                $context, 
                wiki_T_Body::parse($text, $context)
            );
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_UL extends wiki_T_Block

/**
 * Represent "ul" tag's nested elements.
 */
class wiki_T_UL extends wiki_T_Block
{
    /**
     * @access protected
     */
    function wiki_T_UL($source, &$context, &$list)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($list);
    }

    function &parse(&$source, &$context)
    {
        $ret = null;
        if (preg_match('/^(?:[-].*?(?:\n|$))+/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $text = preg_replace('/^[-](.*?(?:\n|$))/m', '\1', $m[0]);
            $list =& wiki_T_List::parse($text, $context);
            $ret =& new wiki_T_UL($m[0], $context, $list);
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_OL extends wiki_T_Block

/**
 * Represent "ol" tag's nested elements.
 */
class wiki_T_OL extends wiki_T_Block
{
    /**
     * @access protected
     */
    function wiki_T_OL($source, &$context, &$list)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($list);
    }

    function &parse(&$source, &$context)
    {
        $ret = null;
        if (preg_match('/^(?:[+].*?(?:\n|$))+/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $text = preg_replace('/^[+](.*?(?:\n|$))/m', '\1', $m[0]);
            $list =& wiki_T_List::parse($text, $context);
            $ret =& new wiki_T_OL($m[0], $context, $list);
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_List extends wiki_T_Block

/**
 * Represent "ul", "ol", "li" tag's nested elements.
 */
class wiki_T_List extends wiki_T_Block
{
    /**
     * @access protected
     */
    function wiki_T_List($source, &$context, &$list)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $sz = count($list);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($list[$i]);
        }
    }

    function &parse(&$source, &$context)
    {
        $classlist = array(
            'wiki_T_UL', 
            'wiki_T_OL', 
            'wiki_T_LI', 
        );

        $_source = $source;
        $list = array();
        while ($source != '') {
            foreach ($classlist as $class) {
                // NOTE: call_user_func(_array) CAN'T return reference.
                // so, we can use only eval().
                eval("\$ret =& $class::parse(\$source, \$context);");
                if ($ret != null) {
                    $list[] =& $ret;
                    break;
                }
            }
            if($ret == null){
                // ERROR (for infinite loop bug)
                die('Infinite loop bug : wiki_T_List');
            }
        }
        $_lists =& new wiki_T_List($_source, $context, $list);
        return $_lists;
    }
}

// }}}
// {{{ wiki_T_LI extends wiki_T_Block

/**
 * Represent "li" tag's nested elements.
 */
class wiki_T_LI extends wiki_T_Block
{
    /**
     * @access protected
     */
    function wiki_T_LI($source, &$context, &$line)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($line);
    }

    function &parse(&$source, &$context)
    {
        $ret = null;
        if (preg_match('/^(.*?)(?:\n|$)/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $lines =& wiki_T_Line::parse($m[1], $context);
            $ret =& new wiki_T_LI($m[0], $context, $lines);
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_DL extends wiki_T_Block

/**
 * Represent "dl", "dt", "dd" tag's elements
 */
class wiki_T_DL extends wiki_T_Block
{
    /**
     * @access protected
     */
    function wiki_T_DL($source, &$context, &$elements)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $sz = count($elements);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($elements[$i]);
        }
    }

    function &parse(&$source, &$context)
    {
        $classlist = array('wiki_T_DT', 'wiki_T_DD');

        $_source = $source;
        $result = null;

        $elem[] =& wiki_T_DT::parse($source, $context);
        if ($elem[0] == null) {
            return $result;
        }
        while ($source != '') {
            foreach($classlist as $class){
                // NOTE: call_user_func(_array) CAN'T return reference.
                // so, we can use only eval().
                eval("\$ret =& $class::parse(\$source, \$context);");
                if($ret != null){
                    $elem[] =& $ret;
                    break;
                }
            }
            if ($ret == null) {
                break;
            }
        }
        $result =& new wiki_T_DL(
            _substr($_source, 0, strlen($_source) - strlen($source)), 
            $context, 
            $elem
        );
        return $result;
    }
}

// }}}
// {{{ wiki_T_DT extends wiki_T_DL

/**
 * Represent "dt" word's element.
 */
class wiki_T_DT extends wiki_T_DL
{
    function wiki_T_DT($source, &$context, &$term)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($term);
    }

    function &parse(&$source, &$context)
    {
        $ret = null;
        // ex) $source = ":definition label: \nabc"
        if (preg_match('/^([:])([^\n]+?\1.*?(?:\n|$))/', $source, $m)) {
            $mark = $m[1]; // should be ":"
            $_part = $m[2]; // should be "definition label: \n"
            $term =& wiki_T_Line::parse($_part, $context, $mark);
            // $_part should be ": \n"
            if (preg_match("/^{$mark}[\\t ]*\\n?/", $_part, $n)) {
                $src = $mark . $term->getsource() . $n[0];
                // should be ":definition label: \n"
                $source = _substr($source, strlen($src)); // should be "abc"
                $ret =& new wiki_T_DT($src, $context, $term);
            }
        }
        return $ret;
    }
}

// }}}
// {{{ wiki_T_DD extends wiki_T_DL

/**
 * Represent "dd" description's element.
 */
class wiki_T_DD extends wiki_T_DL
{
    function wiki_T_DD($source, &$context, &$elem)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($elem);
    }

    function &parse(&$source, &$context)
    {
        $_source = $source;

        $result = null;
        $ret =& wiki_T_DT::parse($source, $context);
        if ($ret != null) {
            $ret->undo($source);
            return $result;
        }
        $ret =& wiki_T_Empty::parse($source, $context);
        if ($ret != null) {
            $ret->undo($source);
            return $result;
        }

        $ret =& wiki_T_Block::parse($source, $context);
        if ($ret != null) {
            $result =& new wiki_T_DD(
                _substr($_source, 0, strlen($_source) - strlen($source)), 
                $context, 
                $ret
            );
            return $result;
        }
        if (preg_match('/^(.*?)(?:\n|$)/', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $ret =& wiki_T_Line::parse($m[1], $context);
            $result =& new wiki_T_DD(
                _substr($_source, 0, strlen($_source) - strlen($source)), 
                $context, 
                $ret
            );
            return $result;
        }
        // ERROR (for infinite loop bug)
        die('Infinite loop bug : wiki_T_DD');
    }
}

// }}}
// {{{ wiki_T_Table extends wiki_T_Block

/**
 * Represent table element.
 */
class wiki_T_Table extends wiki_T_Block
{
    function wiki_T_Table($source, &$context, &$elements)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $sz = count($elements);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($elements[$i]);
        }
    }

    function &parse(&$source, &$context)
    {
        $_source = $source;
        $elem[] =& wiki_T_TR::parse($source, $context);
        $result = null;
        if ($elem[0] == null) {
            return $result;
        }
        while ($source != '') {
            $e =& wiki_T_TR::parse($source, $context);
            if ($e == null) {
                break;
            }
            if ($e->getcols() != $elem[0]->getcols()) {
                $e->undo($source);
                break;
            }
            $elem[] =& $e;
        }
        $result =& new wiki_T_Table(
            _substr($_source, 0, strlen($_source) - strlen($source)), 
            $context, 
            $elem
        );
        return $result;
    }
}

// }}}
// {{{ wiki_T_TR extends wiki_T_Table

/**
 * Represent "tr" tag's elements.
 */
class wiki_T_TR extends wiki_T_Table
{
    /**
     * @access protected
     * @type integer
     */
    var $_cols;

    function getcols(){ return $this->_cols; }

    function wiki_T_TR($source, &$context, &$elements)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $sz = count($elements);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($elements[$i]);
        }
        $this->_cols = count($elements);
    }

    function &parse(&$source, &$context)
    {
        $result = null;
        $elem = array();
        $regexp = '/^\|((?:[^\n]*?\|)+)([HhLlCcRr]*)[\t ]*(?:\n|$)/';
        if (preg_match($regexp, $source, $m)) {
            $src = $m[1];
            $opts = preg_split('//', $m[2], -1, PREG_SPLIT_NO_EMPTY);
            while ($src != '') {
                $elem[] =& wiki_T_TD::parse($src, $context, $opts);
            }
            $source = _substr($source, strlen($m[0]));
            $result =& new wiki_T_TR($m[0], $context, $elem);

        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_TD extends wiki_T_TR

/**
 * Represent "td" tag's element.
 */
class wiki_T_TD extends wiki_T_TR
{
    /**#@+
     * @access protected
     */

    var $_align = null;
    var $_isheader = false;
    var $_bgcolor = null;

    /**#@-*/

    function getalign(){ return $this->_align; }
    function isheader(){ return $this->_isheader; }
    function getbgcolor(){ return $this->_bgcolor; }

    function wiki_T_TD($source, &$context, &$elem, $option)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($elem);
        $this->setoption($option);
    }

    function &parse(&$source, &$context, $option = array())
    {
        $_source = $source;
        $pattern = '(?:LEFT|CENTER|RIGHT|BGCOLOR\(.+?\))';
        if (preg_match("/^({$pattern}(?:,{$pattern})*):/i", $source, $m)){
            $option = array_merge($option, explode(',', $m[1]));
            $source = _substr($source, strlen($m[0]));
        }
        $result = null;
        $e =& wiki_T_Line::parse($source, $context, '\|');
        if ($e == null || !preg_match('/^\|/', $source)) {
            $source = $_source;
            return $result;
        }
        $source = _substr($source, 1);
        $result =& new wiki_T_TD(
            _substr($_source, 0, strlen($_source) - strlen($source) - 1), 
            $context, 
            $e, 
            $option
        );
        return $result;
    }

    // {{{ setoption()

    function setoption($option)
    {
        foreach ($option as $opt) {
            switch(strtoupper($opt)) {
            case 'H':
                $this->_isheader = true;
                break;
            case 'L':
            case 'LEFT':
                $this->_align = 'left';
                break;
            case 'C':
            case 'CENTER':
                $this->_align = 'center';
                break;
            case 'R':
            case 'RIGHT':
                $this->_align = 'right';
                break;
            default:
                if (preg_match('/^BGCOLOR\((.+?)\)$/i', $opt, $m)) {
                    $this->_bgcolor = $m[1];
                }
            }
        }
    }

    // }}}
}

// }}}
// {{{ wiki_T_BlockPlugin extends wiki_T_Block

/**
 * Represent Block-Type Plugin element.
 */
class wiki_T_BlockPlugin extends wiki_T_Block
{
    /**#@+
     * @access protected
     */
    var $_pluginname;
    var $_param1;
    var $_param2;
    /**#@-*/

    function getpluginname(){ return $this->_pluginname; }
    function getparam1(){ return $this->_param1; }
    function getparam2(){ return $this->_param2; }

    function wiki_T_BlockPlugin(
        $source, &$context, $pluginname, $param1, $param2)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->_pluginname = $pluginname;
        $this->_param1 = $param1;
        $this->_param2 = $param2;
    }

    function &parse(&$source, &$context)
    {
        $regexp = '/^#([a-zA-Z0-9_]+)(?:[\t ]*\|([^\n]*?)\|)?[\t ]*\n?/';
        $result = null;

        // ex) source = "#p01 |params|>\nline1\nline2\n||<\nabc\n"
        if (!preg_match($regexp, $source, $m)) {
            return $result;
        }

        $pluginname = $m[1]; // should be "p01"
        $param1 = @$m[2]; // should be "params"
        $src = $m[0]; // should be "#p01 |params|"
        $source = _substr($source, strlen($src));
        // should be ">\nline1\nline2\n||<\nabc\n"

        $param2 = '';
        if (!preg_match('/^>/', $source)) {
            $result =& new wiki_T_BlockPlugin($src, $context, 
                $pluginname, $param1, $param2);
            return $result;
        }

        $s = $_s = _substr($source, 1);
        // should be "\nline1\nline2\n||<\nabc\n"

        while ($s != '') {
            $classlist = array('wiki_T_Empty', 'wiki_T_Block');
            foreach ($classlist as $class) {
                // NOTE: call_user_func(_array) CAN'T return reference.
                // so, we can use only eval().
                eval("\$ret =& $class::parse(\$s, \$context);");
                if ($ret != null) {
                    break;
                }
            }

            if ($ret == null) {
                //wiki_T_Line::parse($s, $context, '[}\n]');
                wiki_T_Line::parse($s, $context, '((?:\|\|<)|\n)');
                if (preg_match('/^\|\|<[\t ]*\n?/', $s, $m)) {
                    // here, $s should be "}\nabc\n"
                    $len = strlen($source) // ">\nline1\nline2\n||<\nabc\n"
                        - strlen($s) // ">\nabc\n"
                        + strlen($m[0]); // "||<\n"
                    // should be strlen(">\nline1\nline2\n||<\n")

                    $src .= _substr($source, 0, $len);
                    // should be "#p01 (params)>\nline1\nline2\n||<\n"

                    $source = _substr($source, $len);
                    // should be "abc\n"

                    // $_s = "\nline1\nline2\n||<\nabc\n"
                    // now, $s = "||<\nabc\n"
                    $param2 = _substr($_s, 0, strlen($_s) - strlen($s));
                    // should be "\nline1\nline2\n"
                    // equals content text between ">" and "||<".

                    break;
                }
            }
        }

        $result =& new wiki_T_BlockPlugin($src, $context, 
            $pluginname, $param1, $param2);
        return $result;
    }
}

// }}}
// {{{ wiki_T_Comment extends wiki_T_Block

/**
 * Represent comment element.
 */
class wiki_T_Comment extends wiki_T_Block
{
    function &parse(&$source, &$context)
    {
        $result = null;

        if (preg_match('!^//.*?(?:\n|$)!', $source, $m)) {
            $source = _substr($source, strlen($m[0]));
            $result =& new wiki_T_Comment($m[0], $context);
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_Paragraph extends wiki_T_Block

/**
 * Represent paragraph elements.
 */
class wiki_T_Paragraph extends wiki_T_Block
{
    function wiki_T_Paragraph($source, &$context, &$lines)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $sz = count($lines);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($lines[$i]);
        }
    }

    function &parse(&$source, &$context)
    {
        $classlist = array('wiki_T_Empty', 'wiki_T_Block');
        $_source = $source;
        $line = array();
        while ($source != '') {
            foreach ($classlist as $class) {
                // NOTE: call_user_func(_array) CAN'T return reference.
                // so, we can use only eval().
                eval("\$ret =& $class::parse(\$source, \$context);");
                if($ret != null){
                    $ret->undo($source);
                    break 2;
                }
            }
            preg_match('/^.*?(?:\n|$)/', $source, $m);
            $source = _substr($source, strlen($m[0]));
            $line[] =& wiki_T_Line::parse($m[0], $context);
        }
        $result =& new wiki_T_Paragraph(
            _substr($_source, 0, strlen($_source) - strlen($source)), 
            $context, 
            $line
        );
        return $result;
    }
}

// }}}
// {{{ wiki_T_Line extends wiki_T_InlineElement

/**
 * Represent Inline element collections.
 */
class wiki_T_Line extends wiki_T_InlineElement
{
    function wiki_T_Line($source, &$context, &$elements)
    {
        $this->_source = $source;
        $this->_context =& $context;
        //NOTICE: we CAN'T USE foreach !!
        //foreach breaks zval's reference relations between addelement()!!
        $sz = count($elements);
        for ($i = 0; $i < $sz; $i++) {
            $this->addelement($elements[$i]);
        }
    }

    function &parse(&$str, &$context, $terminator = '')
    {
        $classlist = array(
            'wiki_T_URL', 
            'wiki_T_Mail', 
            'wiki_T_BracketName', 
            'wiki_T_InlinePlugin', 
            'wiki_T_Footnote', 
            'wiki_T_Strong', 
            'wiki_T_Italic', 
            'wiki_T_Del'
        );

        $backup = $str;
        $elements = array();
        while ($str != '') {
            $len = strlen($str);
            foreach ($classlist as $class) {
                $ret = eval(
                    "return $class::getdistance(\$str, \$context);");
                if ($ret == 0) {
                    // NOTE: call_user_func(_array) CAN'T return reference.
                    // so, we can use only eval().
                    eval("\$elements[] =& $class::parse(\$str, \$context);");
                    $len = 0;
                    break;
                }
                if ($ret < $len) {
                    $len = $ret;
                }
            }
            if ($len > 0) {
                $text = _substr($str, 0, $len);
                if ($terminator != '' && 
                    preg_match("/^(.*?)$terminator/mi", $text, $m)) {
                    $str = _substr($str, strlen($m[1]));
                    $elements[] =& wiki_T_String::parse($m[1], $context);
                    break;
                } else {
                    $str = _substr($str, $len);
                    $elements[] =& wiki_T_String::parse($text, $context);
                }
            }
        }
        $result =& new wiki_T_Line(
            _substr($backup, 0, strlen($backup) - strlen($str)), 
            $context, 
            $elements
        );
        return $result;
    }

}

// }}}
// {{{ wiki_T_URL extends wiki_T_InlineElement

/**
 * Represent URL.
 */
class wiki_T_URL extends wiki_T_InlineElement
{
    function geturl(){ return $this->_source; }

    function getdistance(&$str, &$context)
    {
        if (preg_match('/^(.*?)' . WIKI_EXP_URL . '/', $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        if (preg_match('/^' . WIKI_EXP_URL . '/', $str, $m)) {
            $str = _substr($str, strlen($m[0]));
            $result =& new wiki_T_URL($m[0], $context);
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_Mail extends wiki_T_InlineElement

/**
 * Represent E-Mail address.
 */
class wiki_T_Mail extends wiki_T_InlineElement
{
    function getaddress(){ return $this->_source; }

    function getdistance(&$str, &$context)
    {
        if (preg_match('/^(.*?)' . WIKI_EXP_MAIL . '/', $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        if (preg_match('/^' . WIKI_EXP_MAIL . '/', $str, $m)) {
            $str = _substr($str, strlen($m[0]));
            $result =& new wiki_T_Mail($m[0], $context);
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_BracketName extends wiki_T_InlineElement

/**
 * Represent BracketName element.
 */
class wiki_T_BracketName extends wiki_T_InlineElement
{
    var $_pagename;
    var $_alias;

    function getpagename(){ return $this->_pagename; }
    function getalias(){ return $this->_alias; }

    function wiki_T_BracketName($source, &$context, $pagename, $alias)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->_pagename = $pagename;
        $this->_alias = $alias;
    }

    function getdistance(&$str, &$context)
    {
        if (preg_match('/^(.*?)\[\[.+?\]\]/', $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        if (preg_match('/^\[\[(.+?)\]\]/', $str, $m)) {
            $str = _substr($str, strlen($m[0]));
            if (preg_match('/(.+?)>(.+)/', $m[1], $match)) {
                $pagename = trim($match[2]);
                $alias = trim($match[1]);
                $result =& new wiki_T_BracketName(
                    $m[0], 
                    $context, 
                    $pagename, 
                    $alias
                );
            } else {
                $pagename = trim($m[1]);
                $result =& new wiki_T_BracketName(
                    $m[0], 
                    $context, 
                    $pagename, 
                    ''
                );
            }
        }
        return $result;
    }

}

// }}}
// {{{ wiki_T_InlinePlugin extends wiki_T_InlineElement

/**
 * Represent Inline-Type Plugin element.
 */
class wiki_T_InlinePlugin extends wiki_T_InlineElement
{
    var $_pluginname;
    var $_param1;
    var $_param2;

    function getpluginname(){ return $this->_pluginname; }
    function getparam1(){ return $this->_param1; }
    function getparam2(){ return $this->_param2; }

    function wiki_T_InlinePlugin(
        $source, &$context, $pluginname, $param1, $param2)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->_pluginname = $pluginname;
        $this->_param1 = $param1;
        $this->_param2 = $param2;
    }

    function getdistance(&$str, &$context)
    {
        if (preg_match('/^(.*?)&[a-zA-Z0-9_]+[\t ]*\(.*?\)/', $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        // ex) $str = "&p01 (a, b,c) { text } xyz..."
        if (preg_match('/^&([a-zA-Z0-9_]+)[\t ]*\((.*?)\)/', $str, $m)) {
            $pluginname = $m[1]; // should be "p01"
            $param1 = $m[2]; // should be "a, b,c"
            $src = $m[0]; // should be "&p01 (a, b,c)"
            $str = _substr($str, strlen($src));
            // should be " { text } xyz..."

            $param2 = '';

            if (preg_match('/^[\t ]*\{/', $str, $m)) {
                $s = $_s = _substr($str, strlen($m[0]));
                // should be " text } xyz..."

                wiki_T_Line::parse($s, $context, '}');
                // $s should be "} xyz..."

                if (preg_match('/^}/', $s, $m)) {

                    $len = strlen($str) - strlen($s) + strlen($m[0]);
                    // should be strlen(" { text }")

                    $src .= _substr($str, 0, $len);
                    // should be "&p01 (a, b,c) { text }"

                    $str = _substr($str, $len);
                    // should be " xyz..."

                    $param2 = _substr($_s, 0, strlen($_s) - strlen($s));
                    // should be " text "
                }
            }

            $result =& new wiki_T_InlinePlugin(
                $src, 
                $context, 
                $pluginname, 
                $param1, 
                $param2
            );
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_Footnote extends wiki_T_InlineElement

/**
 * Represent Footnote element.
 */
class wiki_T_Footnote extends wiki_T_InlineElement
{
    /**
     * @access protected
     */
    function wiki_T_Footnote($source, &$context, &$line)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($line);
    }

    function getdistance(&$str, &$context)
    {
        $src = $str; // ex) "abc ((def.. )) ghi..."

        while (preg_match('/^(.*?)\(\(/', $src, $m)) {
            $src = _substr($src, strlen($m[1]));
            // should be "((def.. )) ghi..."

            $ret =& wiki_T_Footnote::parse($src, $context);
            if($ret != null){
                // $src should be " ghi..."

                $ret->undo($src); // should be "((def.. )) ghi..."

                return strlen($str) - strlen($src);
                // should be strlen("abc ")
            }
            $src = _substr($src, 2);
            // should be "def..*" if not found rdelim
        }
        return strlen($str);
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        if (!preg_match('/^(\(\()/', $str, $m)) {
            return $result;
        }
        // $m[0] = "(("

        $src = $str; // "(( abc... )) xyz..."

        $str = _substr($str, strlen($m[0]));
        // should be " abc... )) xyz..."

        // target should be " abc... "
        $rdelim = '\)\)';
        $ret =& wiki_T_Line::parse($str, $context, $rdelim);
        // $str should be ")) xyz..."

        if (preg_match("/^{$rdelim}/", $str, $m)) {
            $str = _substr($str, strlen($m[0])); // should be " xyz..."

            // $src = original text: "(( abc... )) xyz..."
            $src = _substr($src, 0, strlen($src) - strlen($str));
            // should be "(( abc...))" == wiki_T_Footnote's source text
            $result =& new wiki_T_Footnote($src, $context, $ret);

            // $str should be "xyz..."
            return $result;
        }

        // NOTE: reaching here means right delimiter was not found.
        // so, return original string.
        $str = $src;
        return $result;
    }
}

// }}}
// {{{ wiki_T_Strong extends wiki_T_InlineElement

/**
 * Represent Strong-emphatic word element.
 */
class wiki_T_Strong extends wiki_T_InlineElement
{
    /**#@+
     * @access protected
     */
    var $_str;
    var $_level;

    /**#@-*/

    function getlevel(){ return $this->_level; }

    function wiki_T_Strong($source, &$context, &$str, $level)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($str);
        $this->_level = $level;
    }

    function getdistance(&$str, &$context)
    {
        $regexp = '/^(.*?)[\t ]?(\*\*?).+?\2(?:[\t ]|$|(?=\n))/';
        if (preg_match($regexp, $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        $regexp = '/^[\t ]?(\*\*?)(.+?)\1(?:[\t ]|$|(?=\n))/';
        if (preg_match($regexp, $str, $m)) {
            $str = _substr($str, strlen($m[0]));
            $elem =& new wiki_T_String($m[2], $context);
            $result =& new wiki_T_Strong(
                $m[0], $context, $elem, strlen($m[1]));
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_Italic extends wiki_T_InlineElement

/**
 * Represent Strong-emphatic word element. (PukiWiki Style)
 */
class wiki_T_Italic extends wiki_T_InlineElement
{
    /**#@+
     * @access protected
     */
    var $_str;
    var $_level;

    /**#@-*/

    function getlevel(){ return $this->_level; }

    function wiki_T_Italic($source, &$context, &$str, $level)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($str);
        $this->_level = $level;
    }

    function getdistance(&$str, &$context)
    {
        $regexp = '/^(.*?)[\t ]?(\'\'\'?).+?\2(?:[\t ]|$|(?=\n))/';
        if (preg_match($regexp, $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        $regexp = '/^[\t ]?(\'\'\'?)(.+?)\1(?:[\t ]|$|(?=\n))/';
        if (preg_match($regexp, $str, $m)) {
            $str = _substr($str, strlen($m[0]));
            $elem =& new wiki_T_String($m[2], $context);
            $result =& new wiki_T_Italic(
                $m[0], $context, $elem, strlen($m[1]));
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_Del extends wiki_T_InlineElement

/**
 * Represent deleted-line word element. (PukiWiki Style)
 * (simple implement. inline only)
 */
class wiki_T_Del extends wiki_T_InlineElement
{
    /**#@+
     * @access protected
     */
    var $_str;

    /**#@-*/

    function wiki_T_Del($source, &$context, &$str)
    {
        $this->_source = $source;
        $this->_context =& $context;
        $this->addelement($str);
    }

    function getdistance(&$str, &$context)
    {
        $regexp = '/^(.*?)[\t ]?(%%).+?\2(?:[\t ]|$|(?=\n))/';
        if (preg_match($regexp, $str, $m)) {
            return strlen($m[1]);
        } else {
            return strlen($str);
        }
    }

    function &parse(&$str, &$context)
    {
        $result = null;
        $regexp = '/^[\t ]?(%%)(.+?)\1(?:[\t ]|$|(?=\n))/';
        if (preg_match($regexp, $str, $m)) {
            $str = _substr($str, strlen($m[0]));
            $elem =& new wiki_T_String($m[2], $context);
            $result =& new wiki_T_Del($m[0], $context, $elem);
        }
        return $result;
    }
}

// }}}
// {{{ wiki_T_String extends wiki_T_InlineElement

/**
 * Represent String element.
 */
class wiki_T_String extends wiki_T_InlineElement
{
    function getstring(){ return $this->_source; }

    function getdistance(&$str, &$context)
    {
        return 0;
    }

    function &parse(&$str, &$context)
    {
        $ins =& new wiki_T_String($str, $context);
        $str = '';
        return $ins;
    }
}

// }}}

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
