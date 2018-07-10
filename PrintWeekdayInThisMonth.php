<?php

// GoogleAPI Setting
const API_URL = 'https://www.googleapis.com/calendar/v3/calendars/';
const API_KEY = 'Your API Key';
const CALENDAR_ID = 'japanese__ja@holiday.calendar.google.com';

// 処理実行
main();

/**
 * GoogleAPIから祝日を取得して、今月の平日のみ出力してみるサンプル
 * Main
 */
function main()
{
    // GoogleAPIコールの為にtimezoneを合わせる
    date_default_timezone_set('UTC');
    $startDate = new DateTime('today first day of January this year');
    $endDate = new DateTime('today first day of January next year');
    $querys = [
        'key' => API_KEY,
        'timeMin' => $startDate->format(DateTime::ISO8601),
        'timeMax' => $endDate->format(DateTime::ISO8601),
        'orderBy' => 'startTime',
        'singleEvents' => 'true',
        'maxResults' => 30,
    ];
    // 祝日取得
    $holidays = getHolidaysInThisYear($querys);

    // タイムゾーンを戻して、今月の平日のみ出力
    date_default_timezone_set('Asia/Tokyo');
    $datetime = new DateTime('today first day of this month');
    $interval1Day = new DateInterval('P1D');
    while (isThisMonth($datetime)) {
        if (isWeekday($datetime, $holidays)) {
            $weekday = getWeekDayJp($datetime->format('w'));
            echo $datetime->format('n/j')."($weekday)<br>";
        }
        $datetime->add($interval1Day);
    }
}

/**
 * 今月の日付かどうか判定する
 * @param DateTime $datetime
 * @return bool
 */
function isThisMonth($datetime)
{
    // 今月の年/月を比較用に用意
    $now = new DateTime('now');
    $year = $now->format('Y');
    $month = $now->format('m');
    // 比較
    if ($datetime->format('Y') !== $year) {
        return false;
    }
    if ($datetime->format('m') !== $month) {
        return false;
    }
    return true;
}

/**
 * 日付が平日かどうか判定する
 * @param Datetime $datetime 判定する日付
 * @param array $holidays 祝日のリスト
 * @return bool
 */
function isWeekday($datetime, $holidays = [])
{
    $HOLIDAYS = ['SUN' => 0, 'SAT' => 6];
    $weekday = (int) $datetime->format('w');
    if (array_search($weekday, $HOLIDAYS, true) !== false) {
        return false;
    }
    $date = $datetime->format('Y-m-d');
    if (array_key_exists($date, $holidays)) {
        return false;
    }
    return true;
}

/**
 * 日本の短縮系の曜日を取得
 * @param int $weeknum 数字で表現された曜日
 * @return string $weekdays[$i] 日本語短縮系の曜日
 */
function getWeekDayJp($weeknum)
{
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    return $weekdays[$weeknum] ?? '';
}

/**
 * GoogleCalendarAPIから今年の祝日を取得
 * @param array $querys API呼び出しの為のクエリの配列
 * @return array $holidays | false
 */
function getHolidaysInThisYear($querys)
{
    // コールするURL
    $url = API_URL.CALENDAR_ID.'/events?'.http_build_query($querys);
    $response = getHttpResponse($url);
    // エラーハンドリング
    if (isset($response['error'])) {
        return false;
    }
    // データの整形
    $data = json_decode($response['body']);
    $holidays = [];
    foreach ($data->items as $item) {
        $datetime = new DateTime($item->start->date);
        $title = $item->summary;
        $holidays[$datetime->format('Y-m-d')] = $title;
    }
    ksort($holidays);
    return $holidays;
}

/**
 * GETでAPIを叩く
 * @param string $url
 * @return array $response
 */
function getHttpResponse($url)
{
    $response = [];
    try {
        $curlHandle = curl_init();
        $headers = [
            'HTTP/1.1'
        ];
        $options = [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_URL => $url,
            CURLOPT_FAILONERROR => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10
        ];

        // API Call
        curl_setopt_array($curlHandle, $options);
        $body = curl_exec($curlHandle);
        $info = curl_getinfo($curlHandle);

        // エラー取得(codeとinfoは今のところ使う予定ないが一応返しておく)
        $errorNumber = curl_errno($curlHandle);
        $errorInfo = curl_error($curlHandle);
        if (CURLE_OK !== $errorNumber) {
            $response = [
                'error' => [
                    'code' => $errorNumber,
                    'info' => $errorInfo
                ]
            ];
            throw new Exception('http response error');
        }

        // 正常系(infoは今のところ使う予定ないが一応返しておく)
        $response = ['info' => $info, 'body' => $body];
        return $response;
    } catch (Exception $e) {
        return $response;
    } finally {
        curl_close($curlHandle);
    }
}
