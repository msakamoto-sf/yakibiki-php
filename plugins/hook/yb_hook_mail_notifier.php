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

require_once('yb/Mailer.php');

/**
 * YakiBiki Default Mail Notifier Core Hook
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: yb_hook_mail_notifier.php 540 2009-06-21 07:21:48Z msakamoto-sf $
 *
 * @param string mailto address 
 *    (if empty, send only to _YB('mail_notify.mail_to_all').
 * @param string subject
 * @param string mail body
 * @param array attachement files (array of array(rawpath, mime_filename))
 * @return array array(boolean:success or failure, 
 *                     string: errorinfo when failure)
 */
function yb_hook_mail_notifier($mailto, $subject, $body, $attaches)
{
    if (!_YB('mail_notify.enable')) {
        return array(true, '');
    }

    $mail =& new yb_Mailer();

    $mail->CharSet = _YB('mail_notify.charset');
    $mail->Encoding = '7bit';
    $_mb_func_charset = _YB('mail_notify.mb_func_charset');

    // 'smtp', 'mail', 'sendmail'
    $mail->Mailer = _YB('mail_notify.mailer');
    $mail->IsHTML(false);

    switch ($mail->Mailer) {
    case 'sendmail':
        $mail->Sendmail = _YB('mail_notify.sendmail_binpath');
        break;
    case 'smtp':
        $mail->Host = _YB('mail_notify.smtp_host');
        $mail->Port = (integer)_YB('mail_notify.smtp_port');
        $mail->SMTPDebug = _YB('mail_notify.use_smtp_debug');
    default:
    }

    $mail->From = _YB('mail_notify.mail_from');
    $mail->FromName = _YB('mail_notify.mail_from_name');
    $mail->AddReplyTo(_YB('mail_notify.mail_reply_to'));
    $mail->AddAddress(_YB('mail_notify.mail_to_all'));
    if (!empty($mailto)) {
        $mail->AddAddress($mailto);
    }

    if (function_exists('mb_convert_encoding')) {
        $subject = mb_convert_encoding(
            $subject, $_mb_func_charset, mb_internal_encoding());
        $body = mb_convert_encoding(
            $body, $_mb_func_charset, mb_internal_encoding());
    }
    $mail->Subject = $subject;
    $mail->Body = $body;

    foreach ($attaches as $attach) {
        list($raw_file, $filename) = $attach;
        $mail->AddAttach($raw_file, $filename, 'base64', 'application/octet-stream');
    }

    $ret = $mail->Send();
    $err = '';
    if (!$ret) {
        $err = $mail->ErrorInfo;
    }
    return array($ret, $err);
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
