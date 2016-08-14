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

/*
 * #cat||>...||< wiki block plugin
 *
 * usage:
 * <code>
 * #cat||>
 * aaaaaaa
 * ...
 * ddddddd
 * ||<
 *
 * #cat|10|> # 10 length column, right align, space fill.
 * aaaaaaa
 * ...
 * ddddddd
 * ||<
 * 
 * #cat|05|> # 0 fill 5 length column.
 * aaaaaaa
 * ...
 * ddddddd
 * ||<
 * 
 * #cat|05,10|> # initial line number: :10
 * aaaaaaa
 * ...
 * ||<
 * 
 * #cat|05,10,3|> # initial line number : 10, increment 3
 * aaaaaaa
 * bbbbbbbbb
 * ccccccc
 * ...
 * ||<
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_cat.php 550 2009-07-15 05:35:53Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_cat_invoke_block($param1, $param2, &$ctx)
{
    $text = explode("\n", trim($param2));
    $p = explode(',', $param1);
    $p = array_map('trim', $p);

    // auto adjustment when column length not given.
    $num = (isset($p[0]) && trim($p[0]) != '') 
        ? $p[0] : strval(strlen(count($text)));

    // increment value.
    $add = isset($p[2]) ? intval($p[2]) : 1;

    // If not given initial value, then, same to increment value.
    $start = isset($p[1]) ? intval($p[1]) : $add;

    // if '0' is on head...
    $zero = $num{0} == 0 ? '0' : '';
    $_num = intval($num);
    $format = "%${zero}${_num}d";

    for($i = 0; $i < count($text); $i++){
        $text[$i] = sprintf($format, $start + $add*$i) . ': ' . $text[$i];
    }
    return '<pre class="plugin_cat">' . h(join("\n", $text)) . "</pre>\n";
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
