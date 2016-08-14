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
 * YakiBiki: rss module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 475 2008-11-30 07:54:19Z msakamoto-sf $
 */

yb_Session::start();
$uc = yb_Session::user_context();
$finder =& new yb_Finder();

// enforce javascript features OFF
_YB('js.enable', false);

// {{{ get and set search conditions

$v = array(
    'categories' => yb_Var::get('c'),
    'textmatch' => (string)yb_Var::get('s'),
    'is_fullmatch' => (boolean)yb_Var::get('ism'),
    'case_sensitive' => (boolean)yb_Var::get('cs'),
    'andor_c_t' => (integer)yb_Var::get('ao'),
    'sort_by' => yb_Var::get('sb'),
    'order_by' => (integer)yb_Var::get('ob'),
    'limit' => (integer)yb_Var::get('l'),
    'feedtype' => yb_Var::get('mode'),
);

if (is_array($v['categories']) && count($v['categories']) > 0) {
    $finder->categories = $v['categories'];
}

if ($v['textmatch'] != '') {
    $finder->textmatch = $v['textmatch'];
    $finder->is_fullmatch = $v['is_fullmatch'];
    $finder->case_sensitive = $v['case_sensitive'];
}

$finder->andor_c_t = $v['andor_c_t'];

// sort by
$sort_by = ($v['sort_by'] == yb_Finder::SORT_BY_CREATED_AT())
    ? yb_Finder::SORT_BY_CREATED_AT()
    : yb_Finder::SORT_BY_UPDATED_AT();
$finder->sort_by = $sort_by;

// order by
$order_by = ($v['order_by'] == ORDER_BY_ASC) ? ORDER_BY_ASC : ORDER_BY_DESC;
$finder->order_by = $order_by;

// limit default 10
if (empty($v['limit'])) {
    $v['limit'] = 10;
}

// }}}

// get id list.
$ids = $finder->search($uc);
$feed_ids = array_slice($ids, 0, $v['limit']);

switch ($v['feedtype']) {
case 'rdf':
    $feeder =& new yb_mdl_rss_RDF();
    break;
case 'rss2':
default:
    $feeder =& new yb_mdl_rss_RSS2();
}

// get data contents.
$err = array(); // dummy
foreach ($feed_ids as $did) {

    $_d = yb_Finder::find_by_id(
        $uc, $did, $err, YB_ACL_PERM_READ, true);
    $ctx =& new yb_DataContext($_d);
    $type = $_d['type'];
    $dtplugin =& yb_Util::factoryDataType($type);
    if (is_null($dtplugin)) {
        yb_Util::forward_error_die(
            t('Illegal data type : [%type]', 
            array('type' => $type)),
            null, 404);
    }
    $_d['contents'] = $dtplugin->view(
        $ctx, 
        $_d['_raw_filepath'], 
        yb_Html::LIST_MODE(), 
        $_d['display_id'], $_d['display_title']);

    $feeder->add_item($_d);
}

header('Content-Type: application/xml; charset=utf-8');
echo $feeder->gen();

function _yb_rss_substr($s, $len)
{
    if (function_exists('mb_substr')) {
        return mb_substr($s, 0, $len);
    } else {
        return substr($s, 0, $len);
    }
}

// {{{ yb_mdl_rss_Common

class yb_mdl_rss_Common
{
    var $xml_lang;
    var $title;
    var $link;
    var $description;
    var $items;
    var $feedurl;

    function yb_mdl_rss_Common()
    {
        $this->xml_lang = substr(_YB('resource.locale'), 0, 2);
        $this->title = _YB('title');
        $this->link = yb_Util::make_url(array());
        $this->description = _YB('title');
        $this->items = array();

        $_keys = array(
            'mdl', 'mode', 'c', 's', 'ism', 'cs', 'ao', 'sb', 'ob', 'l');
        $params = array();
        foreach ($_keys as $_k) {
            $params[$_k] = yb_Var::get($_k);
        }
        $this->feedurl = yb_Util::make_url($params);
    }

    function date_W3C($unixtime)
    {
        return gmstrftime('%Y-%m-%dT%H:%M:%S+00:00', $unixtime);
    }

    function date_RFC822($unixtime)
    {
        return gmstrftime('%a, %d %b %Y %H:%M:%S GMT', $unixtime);
    }

    function add_item($item)
    {
        $this->items[] = $item;
    }

    function gen()
    {
        // abstract
    }
}

// }}}
// {{{ yb_mdl_rss_RDF

class yb_mdl_rss_RDF extends yb_mdl_rss_Common
{
    function yb_mdl_rss_RDF()
    {
        parent::yb_mdl_rss_Common();
    }

    // {{{ gen()

    function gen()
    {
        $lang = h($this->xml_lang);
        $def =<<<DEF
<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF
	xmlns="http://purl.org/rss/1.0/"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xml:lang="{$lang}">
DEF;

        $head = $this->gen_channel();

        $_items = array();
        foreach ($this->items as $d) {
            $_items[] = $this->gen_item($d);
        }
        $item = implode("\n", $_items);

        return $def . "\n" . $head . "\n" . $item . "\n" . '</rdf:RDF>';
    }

    // }}}
    // {{{ gen_channel()

    function gen_channel()
    {
        $about = $this->feedurl;
        $ch_title = h(_yb_rss_substr($this->title, 40));
        $ch_link = h($this->link);
        $ch_des = h(_yb_rss_substr($this->description, 500));

        $dao_user =& yb_dao_Factory::get('user');
        $r = $dao_user->find_by_id(1);
        if (1 == count($r)) {
            $dc_creator = h($r[0]['name']);
        } else {
            $dc_creator = '';
        }
        $dc_date = $this->date_W3C(time());

        $rdf_lis = array();
        foreach ($this->items as $d) {
            $rdf_lis[] = sprintf('<rdf:li rdf:resource="%s"/>', 
                yb_Util::make_url(array('mdl' => 'view', 'id' => $d['id'])));
        }
        $rdf_li = implode("\n", $rdf_lis);

        $head =<<<HEAD
<channel rdf:about="{$about}">
	<title>{$ch_title}</title>
	<link>{$ch_link}</link>
	<description>{$ch_des}</description>
	<dc:creator>{$dc_creator}</dc:creator>
	<dc:date>{$dc_date}</dc:date>
	<items>
	<rdf:Seq>
{$rdf_li}
	</rdf:Seq>
	</items>
</channel>
HEAD;

        return $head;
    }

    // }}}
    // {{{ gen_item()

    function gen_item($data)
    {
        $id = $data['id'];
        $about = yb_Util::make_url(array('mdl' => 'view', 'id' => $id));
        $title = h(_yb_rss_substr($data['title'], 100));
        $des = h(_yb_rss_substr(strip_tags($data['contents']), 500));
        $des = str_replace(
            array("\r\n", "\r", "\n"),
            array('', '', ''),
            $des);
        $dc_creator = h($data['updated_by']['name']);
        $content = $data['contents'];

        $t =& new yb_Time();
        $t->setInternalRaw($data['updated_at']);
        $dc_date = $this->date_W3C($t->unixtime());

        $dc_subjects = array();
        foreach ($data['categories'] as $c) {
            $dc_subjects[] = sprintf('<dc:subject>%s</dc:subject>', 
                h($c['name']));
        }
        $dc_subject = implode("\n", $dc_subjects);

        $ret =<<<ITEM
<item rdf:about="{$about}">
	<title>{$title}</title>
	<link>{$about}</link>
	<description>{$des}</description>
	<content:encoded><![CDATA[
{$content}
]]></content:encoded>
	<dc:creator>{$dc_creator}</dc:creator>
	<dc:date>{$dc_date}</dc:date>
{$dc_subject}
</item>
ITEM;

        return $ret;
    }

    // }}}

}

// }}}
// {{{ yb_mdl_rss_RSS2

class yb_mdl_rss_RSS2 extends yb_mdl_rss_Common
{
    function yb_mdl_rss_RSS2()
    {
        parent::yb_mdl_rss_Common();
    }

    // {{{ gen()

    function gen()
    {
        $lang = h($this->xml_lang);
        $def =<<<DEF
<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xml:lang="{$lang}">
DEF;

        $head = $this->gen_channel();

        $_items = array();
        foreach ($this->items as $d) {
            $_items[] = $this->gen_item($d);
        }
        $item = implode("\n", $_items);

        $footer =<<<FOOTER
	</channel>
</rss>
FOOTER;

        return $def . "\n" . $head . "\n" . $item . "\n" . $footer;
    }

    // }}}
    // {{{ gen_channel()

    function gen_channel()
    {
        $ch_title = h(_yb_rss_substr($this->title, 40));
        $ch_link = h($this->link);
        $ch_des = h(_yb_rss_substr($this->description, 500));
        $feed_url = $this->feedurl;

        $dao_user =& yb_dao_Factory::get('user');
        $r = $dao_user->find_by_id(1);
        if (1 == count($r)) {
            $dc_creator = h($r[0]['name']);
        } else {
            $dc_creator = '';
        }

        $head =<<<HEAD
	<channel>
		<title>{$ch_title}</title>
		<link>{$ch_link}</link>
		<description>{$ch_des}</description>
		<dc:creator>{$dc_creator}</dc:creator>
		<atom:link href="{$feed_url}" rel="self" type="application/rss+xml" />
HEAD;

        return $head;
    }

    // }}}
    // {{{ gen_item()

    function gen_item($data)
    {
        $id = $data['id'];
        $title = h(_yb_rss_substr($data['title'], 100));
        $link = yb_Util::make_url(array('mdl' => 'view', 'id' => $id));
        $content = $data['contents'];
        $dc_creator = h($data['updated_by']['name']);

        $t =& new yb_Time();
        $t->setInternalRaw($data['updated_at']);
        $pubdate = $this->date_RFC822($t->unixtime());

        $categories = array();
        foreach ($data['categories'] as $c) {
            $categories[] = sprintf('<category>%s</category>', 
                h($c['name']));
        }
        $category = implode("\n", $categories);

        $ret =<<<ITEM
		<item>
			<title>{$title}</title>
			<link>{$link}</link>
			<description><![CDATA[
{$content}
]]></description>
			<dc:creator>{$dc_creator}</dc:creator>
			<pubDate>{$pubdate}</pubDate>
			<guid isPermaLink="true">{$link}</guid>
{$category}
		</item>
ITEM;

        return $ret;
    }

    // }}}

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
