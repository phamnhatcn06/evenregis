<?php

class EmailHelper
{
    public static function send($to, $subject, $view, $data = array(), $attachments = array())
    {
        $mail = Yii::app()->mail;
        $params = Yii::app()->params['mail'];

        $message = new YiiMailMessage();
        $message->setSubject($subject);
        $message->setFrom(array($params['from_email'] => $params['from_name']));
        $message->setTo($to);

        $message->view = $view;
        $message->setBody($data, 'text/html');

        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $message->attach(Swift_Attachment::fromPath($attachment));
            }
        }

        try {
            $result = $mail->send($message);
            Yii::log("Email sent to {$to}: {$subject}", CLogger::LEVEL_INFO, 'application.email');
            return $result > 0;
        } catch (Exception $e) {
            Yii::log("Email failed to {$to}: " . $e->getMessage(), CLogger::LEVEL_ERROR, 'application.email');
            return false;
        }
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
            '[Đại hội 2026] Mời gửi hồ sơ dự thi Miss',
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
