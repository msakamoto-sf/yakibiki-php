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
 * requires
 */
require_once('class.phpmailer.php');
require_once('class.pop3.php');

/**
 * YakiBiki PHPMailer wrapper
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Mailer.php 518 2009-01-12 05:23:59Z msakamoto-sf $
 */
class yb_Mailer extends PHPMailer
{
    // {{{ EncodeHeader()

    // overwrite
    function EncodeHeader ($str, $position = 'text')
    {
        $x = 0;

        switch (strtolower($position)) {
        case 'phrase':
            if (!preg_match('/[\200-\377]/', $str)) {
                /* Can't use addslashes as we don't know what value has 
                 * magic_quotes_sybase. */
                $encoded = addcslashes($str, "\0..\37\177\\\"");
                if (($str == $encoded) && 
                    !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str))
                {
                    return ($encoded);
                } else {
                    return ("\"$encoded\"");
                }
            }
            $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', 
                $str, $matches);
            break;
        case 'comment':
            $x = preg_match_all('/[()"]/', $str, $matches);
            /* Fall-through */
        case 'text':
        default:
            $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', 
                $str, $matches);
            break;
        }

        if ($x == 0) {
            return ($str);
        }

        $encoded = $this->encodeMimeHeader($str);

        return $encoded;
    }

    // }}}
    // {{{ encodeMimeHeader()

    function encodeMimeHeader($string)
    {
        if (!strlen($string)){
            return "";
        }

        $charset = $this->CharSet;

        $start = "=?$charset?B?";
        $end = "?=";
        $encoded = '';

        /* Each line must have length <= 75, including $start and $end */
        $length = 75 - strlen($start) - strlen($end);
        /* Average multi-byte ratio */
        $ratio = mb_strlen($string, $charset) / strlen($string);
        /* Base64 has a 4:3 ratio */
        $magic = $avglength = floor(3 * $length * $ratio / 4);

        for ($i=0; $i <= mb_strlen($string, $charset); $i+=$magic) {
            $magic = $avglength;
            $offset = 0;
            /* Recalculate magic for each line to be 100% sure */
            do {
                $magic -= $offset;
                $chunk = mb_substr($string, $i, $magic, $charset);
                $chunk = base64_encode($chunk);
                $offset++;
            } while (strlen($chunk) > $length);

            if ($chunk)
                $encoded .= ' '.$start.$chunk.$end.$this->LE;
        }
        /* Chomp the first space and the last linefeed */
        $encoded = substr($encoded, 1, -strlen($this->LE));

        return $encoded;
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
