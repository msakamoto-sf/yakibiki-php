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
 * &yaruo() wiki inline/block plugin
 *
 * "やる夫"シリーズのAA表示用インライン/ブロックプラグインです。
 * 単純に 'ＭＳ Ｐゴシック', IPAMonaPGothic を指定したpreタグで囲みます。
 *
 * usage:
 * <code>
 * &yaruo() { ... }
 *
 * or
 *
 * #yaruo||>
 * ...
 * ||<
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_wiki_yaruo.php 568 2010-02-26 04:08:06Z msakamoto-sf $
 * @param string plugin parameter
 * @param string inline text
 * @param wiki_Context reference
 * @return string
 */
function yb_plugin_wiki_yaruo_invoke_inline($param1, $param2, &$ctx)
{
    $str = str_replace(' ', '&nbsp;', h($param2));
    return '<span style="font-family:\'ＭＳ Ｐゴシック\',IPAMonaPGothic; font-size:12pt; background-color:#F9F9F9; color:black; line-height:1.1em;">' . $str . '</span>';
}

function yb_plugin_wiki_yaruo_invoke_block($param1, $param2, &$ctx)
{
    return '<pre style="font-family:\'ＭＳ Ｐゴシック\',IPAMonaPGothic; font-size:12pt; background-color:#F9F9F9; color:black; line-height:1.1em; border: none;">' . h($param2) . '</pre>';
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
