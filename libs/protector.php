<?php
// NOTICE: characterset of this file is UTF-8, Japanese.
//
// Thank you for http://www.peak.ne.jp/support/phpcyber/
// (GIJOE : Mineaki Gotou)
// 「PHPサイバーテロの技法 攻撃と防御の実際」(GIJOE著 ソシム)
//
// $Id: protector.php 426 2008-11-08 06:47:46Z msakamoto-sf $
//

function protector_sanitize($arr)
{
    if (is_array($arr)) {
        // 変数汚染攻撃対策
        if (!empty($arr['_SESSION']) || 
            !empty($arr['_COOKIE']) || 
            !empty($arr['_SERVER']) || 
            !empty($arr['_ENV']) || 
            !empty($arr['_FILES']) || 
            !empty($arr['GLOBALS'])
            ) {
            exit;
        }
        return array_map('protector_sanitize', $arr);
    }

    // ヌルバイト攻撃対策
    return str_replace("\0", "", $arr);
}

$_GET    = protector_sanitize($_GET);
$_POST   = protector_sanitize($_POST);
$_COOKIE = protector_sanitize($_COOKIE);

// セッション鍵を利用したXSS, HTTPレスポンス分割対策
// POST や URIに埋め込まれたセッション鍵はあえて無視する
ini_set('session.use_only_cookies' , 1);
// セッション固定攻撃を避けるためには、ここより下の３行をコメントアウトする。
//if (!empty($_GET[session_name()]) && 
//    empty($_COOKIE[ session_name()]) && 
//    !preg_match('/[^0-9A-Za-z,-]/', $_GET[session_name()])
//    ) {
//    $_COOKIE[session_name()] = $_GET[session_name()];
//}

// PHP_SELFを利用したXSSおよびHTTPレスポンス分割攻撃への対策
$_SERVER['PHP_SELF'] = strtr(
    @$_SERVER['PHP_SELF'], 
    array(
        '<'=>'%3C',
        '>'=>'%3E',
        "'"=>'%27',
        '"'=>'%22',
        "\r" => '',
        "\n" => ''
    )
);
$_SERVER['PATH_INFO'] = strtr(
    @$_SERVER['PATH_INFO'], 
    array(
        '<'=>'%3C',
        '>'=>'%3E',
        "'"=>'%27',
        '"'=>'%22',
        "\r" => '',
        "\n" => ''
    )
);

// see: (japanese only)
// http://blog.ohgaki.net/a_a_a_a_a_ra_ca_a_oa_pa_fa_ma_sa_sa_raf
//
// define call back function for array_walk()
if (function_exists('mb_check_encoding')) {
    // mb_check_encoding exists. just use it
    function encoding_check($val, $key, $encoding) {
        if (is_array($val)) {
            array_walk($val, 'encoding_check', $encoding);
        } else {
            if (!mb_check_encoding($val, $encoding)) {
                dlog($key, $val);
                trigger_error('Encoding attack. ', E_USER_ERROR);
                exit;
            }
        }
        if (!mb_check_encoding($key, $encoding)) {
            dlog($key, $val);
            trigger_error('Encoding attack. ', E_USER_ERROR);
            exit;
        }
        return true;
    }
} else if (function_exists('mb_convert_encoding')) {
    // mb_check_encoding does not exist. use mb_convert_encoding()
    function encoding_check($val, $key, $encoding) {
        if (is_array($val)) {
            array_walk($val, 'encoding_check', $encoding);
        } else {
            $val2 = mb_convert_encoding($val, $encoding, $encoding);
            if (!($val2 === $val)) {
                dlog($key, $val);
                trigger_error('Encoding attack. ', E_USER_ERROR);
                exit;
            }
        }
        $key2 = mb_convert_encoding($key, $encoding, $encoding);
        if (!($key2 === $key)) {
            dlog($key, $val);
            trigger_error('Encoding attack. ', E_USER_ERROR);
            exit;
        }
        return true;
    }
}

// http input encoding check
if (function_exists('mb_internal_encoding')) {
    $inputs = array($_GET, $_POST, $_COOKIE, $_SERVER);
    foreach($inputs as $input) {
        array_walk($input, 'encoding_check', _YB('internal.encoding'));
    }
}

// $_REQUEST変数の強制初期化 : GET, POST値のみをまとめる。
// GETとPOSTで同じ変数があった場合は、POSTの方が優先する。
// (デフォルトのvariables_orderのGET, POSTの順番と同じ)
$_REQUEST = array();
foreach ($_GET as $k => $v) {
    $_REQUEST[$k] = $v;
}
foreach ($_POST as $k => $v) {
    $_REQUEST[$k] = $v;
}

/**
 * Local Variables:
 * mode: php
 * coding: utf8
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 * vim: set expandtab tabstop=4 shiftwidth=4:
 */
