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

require_once('Xhwlay/Util.php');

/*
 * <yb_gmap> html plugin
 *
 * First, you require jquery. set _YB('js.enable') to true, 
 * _YB('js.jquery.path') to correct jquery.js path.
 * Second, you must get Google Maps API Key from google web site.
 * and set to _YB('js.google.map.key').
 *
 * usage:
 * [param1]('optional' means optional)
 * id : <div> block id name. (automatically data id is append)
 * w : <div> width by pixel unit. (optional)
 * h : <div> height by pixel unit. (optional)
 * lat : latitude by float value. (optional)
 * lng : longitude by float value. (optional)
 * zoom : zoom by integer value. (optional)
 * fixed : disable map dragging (no value, optional)
 * marker : enable/disable pin marker by boolean (optional, default enable)
 * control : combination of controls by '+' char.
 *           {'small' or 'large'}, 'maptype', 'where'
 *           ex : control=small+maptype+where
 *
 * [param2] : optional message for pin marker. 
 * (if 'marker' is false, param2 is ignored.)
 *
 * <code>
 * <yb_gmap id=map1,lat=34.67,lng=135.41>pin marker message</yb_gmap>
 * <yb_gmap id=map2,w=200,h=200,zoom=15,fized,marker=false />
 * <yb_gmap id=map3,control=small+maptype+where />
 * <yb_gmap id=map4,control=large+maptype+where />
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_plugin_html_gmap.php 552 2009-07-18 03:20:39Z msakamoto-sf $
 * @param mixed tag attribute
 * @param mixed internal element
 * @param yb_DataContext
 * @return string
 */
function yb_plugin_html_gmap_invoke($param1, $param2, &$ctx)
{
    $param1 = yb_hex2bin($param1);
    $param2 = yb_hex2bin($param2);

    $key = _YB('js.google.map.key');

    if (_YB('js.enable') && !empty($key)) {
        $init_js = _yb_plugin_html_gmap_init();
    }

    // setup default parameters
    $_div_id = sprintf('ybgmap_%d_%s', $ctx->get('id'), uniqid(mt_rand()));
    $yb_gmap_div = array('id' => $_div_id, 'w' => 500, 'h' => 300);
    $yb_gmap_params = array(
        'element_id' => $_div_id, 
        'latitude' => 0, 
        'longitude' => 0, 
        'zoom' => 1, 
        'use_small' => false, 
        'use_large' => false, 
        'use_maptype' => false, 
        'use_where' => false, 
        'draggable' => true, 
        'show_marker' => true, 
        'pinmarkmsg' => '', 
    );

    // parse param1
    $_els = explode(',', trim($param1));
    $__els = array_map('trim', $_els);
    $_params = array();
    foreach ($__els as $e) {
        $_p = explode('=', $e, 2);
        $__p = array_map('trim', $_p);
        if (count($__p) == 1) {
            $_params[$__p[0]] = true;
        } else if (count($__p) == 2) {
            $_params[$__p[0]] = $__p[1];
        }
    }

    // convert param1 to js arguments
    foreach ($_params as $k => $v) {
        switch ($k) {
        case 'id':
            if (preg_match('/^[0-9a-zA-Z]+$/mi', $v)) {
                $v = sprintf('ybgmap_%d_%s', $ctx->get('id'), $v);
                $yb_gmap_params['element_id'] = $v;
                $yb_gmap_div['id'] = $v;
            }
            break;
        case 'w':
            $yb_gmap_div['w'] = (integer)$v;
            break;
        case 'h':
            $yb_gmap_div['h'] = (integer)$v;
            break;
        case 'lat':
            $yb_gmap_params['latitude'] = (float)$v;
            break;
        case 'lng':
            $yb_gmap_params['longitude'] = (float)$v;
            break;
        case 'zoom':
            $yb_gmap_params['zoom'] = (float)$v;
            break;
        case 'fixed':
            $yb_gmap_params['draggable'] = false;
            break;
        case 'marker':
            $yb_gmap_params['show_marker'] = Xhwlay_Util::isTrue($v);
            break;
        case 'control':
            $_ctrls = explode('+', $v);
            foreach ($_ctrls as $_c) {
                $_use_c = 'use_' . $_c;
                $yb_gmap_params[$_use_c] = true;
            }
        }
    }

    $yb_gmap_params['pinmarkmsg'] = trim((string)$param2);

    $_base_url = _YB('jsoff.google.map.url');
    $_base_url .= '?z=' . h($yb_gmap_params['zoom']);
    $_base_url .= '&amp;sll=' . h($yb_gmap_params['latitude']);
    $_base_url .= ',' . h($yb_gmap_params['longitude']);
    $noscript_link = '<a href="' . $_base_url . '" target="_blank">Google Map';
    if (!empty($yb_gmap_params['pinmarkmsg'])) {
        $noscript_link .= '(' . h($yb_gmap_params['pinmarkmsg']) . ')';
    }
    $noscript_link .= '</a>';
    if (!_YB('js.enable') || empty($key)) {
        return $noscript_link;
    }

    $_js_args = array();
    $_js_args[] = '"' . h($yb_gmap_params['element_id']) . '"';
    $_js_args[] = h($yb_gmap_params['latitude']);
    $_js_args[] = h($yb_gmap_params['longitude']);
    $_js_args[] = h($yb_gmap_params['zoom']);
    $_js_args[] = $yb_gmap_params['use_small'] ? 'true' : 'false';
    $_js_args[] = $yb_gmap_params['use_large'] ? 'true' : 'false';
    $_js_args[] = $yb_gmap_params['use_maptype'] ? 'true' : 'false';
    $_js_args[] = $yb_gmap_params['use_where'] ? 'true' : 'false';
    $_js_args[] = $yb_gmap_params['draggable'] ? 'true' : 'false';
    $_js_args[] = $yb_gmap_params['show_marker'] ? 'true' : 'false';
    $_js_args[] = '"' . h($yb_gmap_params['pinmarkmsg']) . '"';

    $ybjs_make_gmap = 'ybjs_make_gmap(' . implode(',', $_js_args) . ');';
    yb_jQuery::add_onload_event(
        $yb_gmap_params['element_id'], $ybjs_make_gmap);

    $div = '<div id="' . h($yb_gmap_div['id']) . '" style="width: '
        . h($yb_gmap_div['w']) . 'px; height: ' . h($yb_gmap_div['h']) 
        . 'px;">' . $noscript_link . '</div>';

    return $init_js . $div;
}

function _yb_plugin_html_gmap_init()
{
    static $init = null;
    if (!is_null($init)) {
        return '';
    }
    $init = 'done';

    yb_jQuery::add_onload_event('gmap_unload', 
        'google.load("maps", "2", {"callback" : function() { $(window).unload = google.maps.Unload; }});'
    );
    yb_jQuery::add_onload_event('gmap_whereami', 
        'google.load("maps", "2", {"callback" : ybjs_build_whereami });'
    );

    $_base_dir = dirname(__FILE__);
    $_jsfile = $_base_dir . '/yb_plugin_html_gmap.js';
    return @file_get_contents($_jsfile);
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
