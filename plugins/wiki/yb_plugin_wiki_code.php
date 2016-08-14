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

require_once('Text/Highlighter.php');
require_once('Text/Highlighter/Renderer/Html.php');

/*
 * #code||>...||< wiki block plugin
 *
 * usage:
 * <code>
 * // (A) : simple usage (only lang specify)
 * #code|php|>
 * ...
 * ||<
 *
 * // (B) : lang is omitted, then <pre> tag.
 * #code||>
 * ...
 * ||<
 *
 * // (C) : unkown lang, then <pre> tag.
 * #code|foobar|>
 * ...
 * ||<
 *
 * // (D) : lang, line number. (defualt: no line number)
 * #code|php,numbers|>
 * ...
 * ||<
 *
 * // (E) : lang, tabsize=2.(default: 4)
 * #code|php,tabsize=2|>
 * ...
 * ||<
 *
 * // (F) : lang, line number, tabsize=8.
 * #code|php,numbers,tabsize=8|>
 * ...
 * ||<
 * or
 * #code|php,tabsize=8,numbers|> (1st arg is language)
 * ...
 * ||<
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_code.php 550 2009-07-15 05:35:53Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_code_invoke_block($param1, $param2, &$ctx)
{
    $valid_types = array(
        'ABAP' => 'ABAP',
        'C' => 'CPP',
        'CPP' => 'CPP',
        'CSS' => 'CSS',
        'DIFF' => 'DIFF',
        'DTD' => 'DTD',
        'HTML' => 'HTML',
        'JAVA' => 'JAVA',
        'JAVASCRIPT' => 'JAVASCRIPT',
        'MYSQL' => 'MYSQL',
        'PERL' => 'PERL',
        'PHP' => 'PHP',
        'PYTHON' => 'PYTHON',
        'RUBY' => 'RUBY',
        'SH' => 'SH',
        'SQL' => 'SQL',
        'VBSCRIPT' => 'VBSCRIPT',
        'XML' => 'XML',
        );

    $codeString = (string)trim($param2);
    if ($codeString == '') {
        return '';
    }
    $__p = explode(',', $param1);
    $_params = array_map('trim', $__p);

    $type = @array_shift($_params);
    $type = strtoupper($type);
    if (!isset($valid_types[$type])) {
        return '<pre class="plugin_pre">' . h($codeString) . '</pre>';
    } else {
        $type = $valid_types[$type];
    }

    $__numbers = false;
    $__tabsize = 4;
    while ($_p = @array_shift($_params)) {
        if ($_p == 'numbers') {
            $__numbers = HL_NUMBERS_LI;
            continue;
        }
        if (strpos($_p, 'tabsize') !== false) {
            $_els = explode('=', $_p);
            if (count($_els) == 2) {
                $__tabsize = (integer)$_els[1];
            }
            continue;
        }
    }

    $renderer =& new Text_Highlighter_Renderer_Html(
        array("numbers" => $__numbers, "tabsize" => $__tabsize));

    $hl =& Text_Highlighter::factory($type);
    if (PEAR::isError($hl)) {
        return '<pre class="plugin_pre">' . h($codeString) . '</pre>';
    } else {
        $hl->setRenderer($renderer);
        return $hl->highlight($codeString);
    }


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
