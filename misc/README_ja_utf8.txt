//
//   Copyright (c) 2007 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//

YakiBiki : Yet Another Blog Like Wiki

READ ME DOCUMENTS

$Id: README_ja_utf8.txt 557 2009-07-21 07:03:45Z msakamoto-sf $

* 1. LICENSE

YakiBikiはApache License, Version 2.0で配布されています。

* 2. REQUIREMENTS

PHP4.3.0以上(開発環境は4.4.X/5.2.X + Apache 2.0.X, Apache Module)

デフォルトで必要なPEARライブラリは同梱済です。
システムでセットアップ済のPEARライブラリを利用する場合は、_YB('dir.pear')設定
をコメントアウトするかシステムのPEARを指すようにして下さい。
setup.phpでYakiBikiが最終的に使用するinclude_path設定を確認できます。

* 3. INSTALL

** 3-1. ディレクトリと主要ファイルの説明

/index.php ... PHP実行ファイル
/ybprepare.php ... 共通requireファイル
/ybwebtools/
    check-logs.php ... ログ出力確認用PHP
    check-perms.php ... ディレクトリパーミッションチェック用PHP
    check-phpini.php ... YakiBikiが実行される最終的なphp.ini環境確認用PHP
    check-ybvars.php ... libs/config.phpで定義された_YB()変数確認用PHP
    clean-cache.php ... YakiBikiのキャッシュファイル削除用PHP
    clean-yb.php ... YakiBikiのデータファイル削除用PHP
    fix-perms.php ... YakiBikiのログファイル・データファイル等のパーミッション
                      変更用PHP
    sample_yb_append.php ... ybwebtoolsを本番環境で一時的に使う為の
                             スイッチファイル
    setup.php ... インストール用PHP
    yb_local.php ... ybwebtools用共通requireファイル

(*) datas/ ... YakiBikiのデータディレクトリ
        grain/ ... データディレクトリ
        idx/ ... インデックスデータディレクトリ
        raw/ ... 物理データファイルディレクトリ
        seq/ ... 番号採番データディレクトリ
    libs/ ... YakiBikiのライブラリディレクトリ
        init.php ... 共通前処理プログラム
        config.php ... 設定ファイル
        funcs.php ... 共通関数
        timezones.php ... タイムゾーン定義ファイル
        mime_types.php ... ダウンロード用MIME TYPE定義ファイル
    locales/ ... 国際化メッセージカタログディレクトリ
(*) logs/ ... YakiBikiログディレクトリ
    misc/ ... ヘルプやsetup時初期化ファイルなど
    plugins/ ... YakiBikiプラグインディレクトリ
(*) temp/
        bookmark/ ... Xhwlayブックマーク保存ディレクトリ
        caches/ ... PEARのCache_Liteキャッシュディレクトリ
        session/ ... PHPセッション保存ディレクトリ
        templates_c/ ... Smartyテンプレートコンパイルディレクトリ
    themes/ ... テーマディレクトリ


上に挙げたディレクトリで、"(*)"印の付いたディレクトリ以下はWebサーバーの実行
ユーザーで読み書きできるようにしておいて下さい。
(check-perms.phpでチェックできます)

** 3-2. ディレクトリ位置の調整

各ディレクトリの位置は、殆ど libs/config.php で変更可能です。
一応 .htaccess で主要ディレクトリは見えないようにしてありますが、出来れば
index.php, ybwebtools/, themes/ 以外はWeb公開ディレクトリの外部に移動する
ことをお奨めします。

libs/config.phpと併せてybprepare.php中のinit.phpのパスも修正して下さい。

** 3-3. config.phpの調整

*** [セッション系] ... php.iniのエントリ名そのままです。

_YB('session.save.path', $__base_dir . '/temp/session');

↓この状態ですと2週間、ブラウザ側でセッションを保持します。
_YB('session.lifetime',  86400 * 14); // 2 weeks by seconds.
↑セキュリティ条件に合わせて適宜調整して下さい。

_YB('session.path',      '/');
_YB('session.domain',    '');
_YB('session.secure',    false); ←SSLを使う時は忘れずにtrueに！
_YB('session.name',      'ybs');
_YB('session.cache.limiter',    'none');

*** [Smarty関連] ... デフォルトのconfig.phpは開発用です。

_YB('smarty.force_compile', true); ←通常利用時はfalseをお勧めします。
↑テーマファイルなどのカスタマイズを行う時は、trueにしておきます。

*** [システム関連]

--- ほぼ変更が必須 ---

_YB('setup.menu', 1); ←インストールが完了したら必ず0にして下さい。

_YB('check.support.scripts', 1);
↑check-*.php系の有効無効を切り替えます。障害発生時以外は0で良いでしょう。

↓この辺の調整も忘れずに。特に、'url'と'url.themes'はこれを元にURLを生成して
　ますので、間違えないようにして下さい。(setup.phpで確認できます)
_YB('title', 'YakiBiki');
_YB('theme', 'default');
_YB('url', 'http://yb-test/'); // MUST trailing '/'
_YB('url.themes', _YB('url') . 'themes/' . _YB('theme'));

↓通常利用時は PEAR_LOG_INFOに書き換えてください。
_YB('log.level', 'PEAR_LOG_DEBUG');

パスワードSALTは必ずデフォルト以外の適当な長さの文字列に変更して下さい。
_YB('password.salt', 'yakibiki');

--- 「こんなのもあるよ」程度 ---

_YB('default.timezone', 'Asia/Tokyo'); ←timezones.phpに載っている名前

↓デフォルト表示でのモジュールを指定します。
_YB('default.module', 'view');
↓デフォルト表示で表示するページ名を指定します。
_YB('default.pagename', 'FrontPage');
↓サイドバーのページ名を指定します。
_YB('default.sidebar', 'SideBar');

↓記事作成時のテキスト入力枠の大きさや改行の動きを変えたい時に・・・。
_YB('textarea.row', 20);
_YB('textarea.cols', 80);
_YB('textarea.wrap', 'soft');

↓画像を表示する時のデフォルトの幅。
_YB('yb.view.image.width.default', 600);

↓メールアドレスのチェックで、DNSのレコードまでチェックする時はtrueにします。
_YB('email.check.use_chkdnsrr', false);
(socketエクステンションが必要です)

↓GoogleMapsなどのJavaScriptを使う場合はtrueにします。
_YB('js.enable', false);
GoogleMapsを使う場合は、更にjqueryの調整やAPIキーの取得が必要です。
セットアップ後も変更可能ですので、セットアップ後にHELPの<yb_gmap>プラグインの
ヘルプを参照して調整して下さい。

** 3-4. 動作確認, セットアップ

調整が一通り終わったら、サポートスクリプトで動作確認をします。

1. ブラウザで ybwebtools/check-perms.php にアクセスし、ディレクトリの書き込み
権限をテストします。

2. ブラウザで ybwebtools/check-logs.php にアクセスし、ログ出力をテストします。
PHP独自のtrigger_error()のログと、YakiBikiの備えるPEAR_Logを用いたログの
二種類をテストできます。PEAR_Logはconfig.phpで、trigger_error()はphp.iniや
.htaccessで指定した設定通りに出力されているか確認します。

3. 最後にブラウザで setup.php にアクセスし、最初のユーザー(システム管理権限)を登録します。

そして、登録が終わったら必ずconfig.phpの次の2項目を0に設定して下さい！
_YB('setup.menu')
_YB('check.support.scripts')

登録と初期設定が成功すれば、setup.phpの画面上部に"login"リンクが表示されます。
リンクをクリックするか、index.phpにアクセスすればYakiBikiが始まります。

なお一時的に ybwebtools/ 下のツールを動かしたい時は、
ybwebtools/sample_yb_append.php
を
ybwebtools/yb_append.php
にリネームします。
clean-yb.phpを動かしたい場合は config.php の _YB('setup.menu') を1にします。

* SPECIAL THANKS *

PukiWiki : http://pukiwiki.sourceforge.jp/
KinoWiki : http://kinowiki.net/index.php

株式会社エヌ・エス・ディ : http://www.nsd-ltd.co.jp/
アシアル株式会社 : http://www.asial.co.jp/

↑この２つの会社には格別のThanksを。NSDの太っ腹？がなければYakiBikiが形に
なることは無かったです。また、アシアルの太っ腹？がなければ、YakiBikiがここに
到達する前に自分は飢え死にしていたと思います。


