<?php

class EmailHelper
{
    public static function send($to, $subject, $view, $data = array(), $attachments = array())
    {
        return MyHelper::sendMail($to, $subject, $view, $data, $attachments);
    }

    public static function sendMissInvitation($contestant)
    {
        $email = $contestant->personal_email;
        if (empty($email)) {
            return false;
        }

        $token = isset($contestant->submission_token) ? $contestant->submission_token : '';
        if (empty($token)) {
            return false;
        }

        $submissionUrl = Yii::app()->createAbsoluteUrl('/frontend/miss/submit', array('token' => $token));

        $expiresAt = isset($contestant->submission_token_expires_at)
            ? date('d/m/Y H:i', $contestant->submission_token_expires_at)
            : date('d/m/Y H:i', strtotime('+7 days'));

        $data = array(
            'contestant' => $contestant,
            'submissionUrl' => $submissionUrl,
            'expiresAt' => $expiresAt,
        );

        return self::send(
            $email,
            '[Đại hội Mường Thanh 2026] Mời gửi hồ sơ dự thi Miss',
            'miss_invitation',
            $data
        );
    }

    public static function sendMissConfirmation($contestant)
    {
        $email = $contestant->personal_email;
        if (empty($email)) {
            return false;
        }

        $data = array(
            'contestant' => $contestant,
        );

        return self::send(
            $email,
            '[Đại hội 2026] Xác nhận đã nhận hồ sơ dự thi Miss',
            'miss_confirmation',
            $data
        );
    }
}
