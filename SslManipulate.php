<?php

/**
 * SSL関連を操作するクラス
 */
class SslManipulate
{
    /**
     * SSH証明書期限を取得
     * @param string $domainname ドメイン名
     * @return DateTime $sslLimitDate SSL証明書期限
     */
    public static function fetchSslLimitDate($domainname)
    {
        // エラーをErrorExceptionに変換
        set_error_handler(
            function($severity, $message, $file, $line) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        );
        $resource = null;
        try {
            $stream_context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false]]);
            $resource = stream_socket_client(
                'ssl://' . $domainname . ':443',
                $errorNumber,
                $errorMessage,
                $timeOutSeconds = 30,
                STREAM_CLIENT_CONNECT,
                $stream_context
            );
        } catch (ErrorException $e) {
            throw new RuntimeException($e->getMessage());
        }
        $context = stream_context_get_params($resource);
        $opensslX509 = openssl_x509_parse($context['options']['ssl']['peer_certificate']);
        if (strpos($opensslX509['subject']['CN'], $domainname) === false) {
            // ワイルドカードSSL証明書かどうか
            if (strpos($domainname, str_replace('*.', '', $opensslX509['subject']['CN'])) === false) {
                throw new Exception("This domain is not contract ssl certificate.");
            }
        }
        $sslLimitDate = new DateTime(date('YmdHis', $opensslX509['validTo_time_t']));
        return $sslLimitDate;
    }

    /**
     * SSL証明書期限との差分(日付)を出す
     * @param DateTime $dayBased 基準となる日付
     * @param DateTime $sslLimitDate SSL証明書期限日
     * @return int $diffDays SSL証明書期限日との日付差異
     */
    public static function calculateDiffDaysToDeadline($dayBased, $sslLimitDate)
    {
        if (!$dayBased instanceof DateTime || !$sslLimitDate instanceof DateTime) {
            throw new InvalidArgumentException(
                'Unexpected type is passed Expected type is DateTime. '.
                '$dayBased - '.gettype($dayBased).'$sslLimitDate - '.gettype($sslLimitDate).' is passed.'
            );
        }
        $interval = $dayBased->diff($sslLimitDate);
        $diffDays = (int) $interval->format('%R%a');
        return $diffDays;
    }

    /**
     * SSL証明書が期限に近づいているかどうかを確認
     * @param int $diffDays SSL証明書期限日との日付差異
     * @param int $daysBased 基準日数
     * @return bool
     */
    public static function isCloseToDeadline($diffDays, $daysBased)
    {
        return ($diffDays <= $daysBased);
    }
}
