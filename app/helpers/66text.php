<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

use Google\Auth\Credentials\ServiceAccountCredentials;

defined('ALTUMCODE') || die();

/* Normalize SMS text to GSM-7 safe characters */
/* Fully normalize SMS text: keep emojis + {} + newlines, remove only disallowed chars */
function normalize_sms_text($message) {
    /* ensure utf-8 */
    if (!mb_detect_encoding($message, 'UTF-8', true)) {
        $message = mb_convert_encoding($message, 'UTF-8');
    }

    /* normalize line endings to \n (LF) */
    $message = preg_replace('/\r\n?/', "\n", $message);

    /* replace problematic punctuation with safe equivalents */
    $replacement_map = [
        '‘' => "'", '’' => "'", '‚' => "'",
        '“' => '"', '”' => '"', '„' => '"',
        '–' => '-',  /* en dash */
        '—' => '-',  /* em dash */
        '…' => '...'
    ];
    $message = strtr($message, $replacement_map);

    /* remove zero-width and format chars (ZWSP, LRM/RLM, etc.) */
    $message = preg_replace('/\p{Cf}+/u', '', $message);

    /* remove control chars except newline (\n = \x0A) */
    $message = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/u', '', $message);

    /* collapse spaces but do not touch newlines */
    $message = preg_replace('/[ ]{2,}/', ' ', $message);
    $message = preg_replace('/[ \t]+\n/', "\n", $message); /* trim end-of-line spaces */
    $message = preg_replace('/\n[ \t]+/', "\n", $message); /* trim start-of-line spaces */

    /* trim overall (keeps internal newlines) */
    $message = trim($message);

    return $message;
}

function wake_device_to_send_sms($device_fcm_token) {
    if(empty(settings()->sms->firebase_project_id) || empty(settings()->sms->firebase_service_account_json)) return null;

    $scope = 'https://www.googleapis.com/auth/firebase.messaging';
    $credentials = new ServiceAccountCredentials($scope, \Altum\Uploads::get_full_path('firebase') . settings()->sms->firebase_service_account_json);
    $access_token_array = $credentials->fetchAuthToken();
    $access_token = $access_token_array['access_token'];

    /* Build request */
    $url = 'https://fcm.googleapis.com/v1/projects/' . settings()->sms->firebase_project_id . '/messages:send';
    $payload = [
        'message' => [
            'token' => $device_fcm_token,
            'android' => ['priority' => 'HIGH'],
            'data' => [
                'type' => 'sms'
            ]
        ]
    ];

    /* Send request */
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response_body = curl_exec($ch);
    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    /* Log FCM response to 66text log file */
    $log_message = '[FCM] token=' . substr($device_fcm_token, 0, 20) . '... code=' . $response_code . ' body=' . $response_body;
    $log_file = \Altum\Logger::$path ?? (defined('UPLOADS_PATH') ? UPLOADS_PATH . 'logs/' . date('d-M-Y') . '.log' : null);
    if($log_file) @file_put_contents($log_file, '[' . date('d-M-Y H:i:s') . ' UTC] ' . $log_message . PHP_EOL, FILE_APPEND);
    error_log($log_message);

    return [
        'response_code' => $response_code,
        'response_body' => $response_body
    ];
}

function get_next_run_datetime(
    $frequency,
    $time,
    $week_days = [],
    $month_days = [],
    $local_timezone = 'UTC',
    $datetime_modifier = null,
    $datetime_modifier_current = null,
) {
    $local_tz     = new DateTimeZone($local_timezone);
    $current_time = new DateTime('now', $local_tz);
    if($datetime_modifier_current) {
        $current_time = $current_time->modify($datetime_modifier_current);
    }

    [$hour, $minute] = explode(':', $time);
    $run_time = clone $current_time;
    $run_time->setTime((int) $hour, (int) $minute, 0);

    switch ($frequency) {
        case 'daily':
            if($run_time <= $current_time) {
                $run_time->modify('+1 day');
            }
            break;

        case 'weekly':
            while (!in_array((int) $run_time->format('N'), $week_days, true) || $run_time <= $current_time) {
                $run_time->modify('+1 day');
            }
            break;

        case 'monthly':
        default:
            while (!in_array((int) $run_time->format('j'), $month_days, true) || $run_time <= $current_time) {
                $run_time->modify('+1 day');
            }
            break;
    }

    $run_time->setTimezone(new DateTimeZone('UTC'));

    if($datetime_modifier) {
        $run_time->modify($datetime_modifier);
    }

    return $run_time->format('Y-m-d H:i:s');
}


function rss_feed_parse_url($rss_url) {
    $rss_xml = @simplexml_load_file($rss_url);
    if(!$rss_xml) return null;

    $rss_data = [];
    $namespaces = $rss_xml->getNamespaces(true);

    foreach ($rss_xml->channel->item ?? $rss_xml->entry ?? [] as $rss_item) {
        /* Default fields */
        $item_id = (string)($rss_item->guid ?? $rss_item->id ?? $rss_item->link);
        $item_title = (string)($rss_item->title ?? '');
        $item_url = (string)($rss_item->link['href'] ?? $rss_item->link ?? '');

        /* Description handling */
        $item_description = '';
        if(isset($rss_item->description)) {
            $item_description = (string)$rss_item->description;
        } elseif(isset($rss_item->summary)) {
            $item_description = (string)$rss_item->summary;
        } elseif(isset($rss_item->content)) {
            $item_description = (string)$rss_item->content;
        }

        $item_image = null;
        $item_publication_date = null;

        /* Publication date */
        if(isset($rss_item->pubDate)) {
            $item_publication_date = (string)$rss_item->pubDate;
        } elseif(isset($rss_item->published)) {
            $item_publication_date = (string)$rss_item->published;
        } elseif(isset($rss_item->updated)) {
            $item_publication_date = (string)$rss_item->updated;
        }

        /* Image extraction - media namespace */
        if(isset($namespaces['media'])) {
            $media_content = $rss_item->children($namespaces['media']);
            if(isset($media_content->content)) {
                foreach ($media_content->content as $media_item) {
                    if(!empty($media_item->attributes()->url)) {
                        $item_image = (string)$media_item->attributes()->url;
                        break;
                    }
                }
            }
            if(!$item_image && isset($media_content->thumbnail)) {
                foreach ($media_content->thumbnail as $media_thumbnail) {
                    if(!empty($media_thumbnail->attributes()->url)) {
                        $item_image = (string)$media_thumbnail->attributes()->url;
                        break;
                    }
                }
            }
        }

        /* Image extraction - enclosure */
        if(!$item_image && isset($rss_item->enclosure)) {
            $enclosure_attributes = $rss_item->enclosure->attributes();
            if(isset($enclosure_attributes['url'])) {
                $item_image = (string)$enclosure_attributes['url'];
            }
        }

        /* Image extraction - common field <image> */
        if(!$item_image && isset($rss_item->image)) {
            $item_image = (string)$rss_item->image;
        }

        /* Fallback: look for image in description */
        if(!$item_image && preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $item_description, $matches)) {
            $item_image = $matches[1];
        }

        $item_image = get_url($item_image);

        $rss_data[] = [
            'id' => input_clean(md5($item_id)),
            'title' => input_clean($item_title),
            'url' => input_clean($item_url),
            'description' => input_clean($item_description),
            'image' => input_clean($item_image),
            'publication_date' => input_clean($item_publication_date),
        ];
    }

    return $rss_data;
}


function get_phone_number($raw_phone_number) {
    /* trim whitespace */
    $raw_phone_number = trim($raw_phone_number);

    /* remove all characters except digits and plus */
    $raw_phone_number = preg_replace('/[^\d\+]/', '', $raw_phone_number);

    /* ensure only one leading plus and remove internal plus signs */
    if (substr($raw_phone_number, 0, 1) === '+') {
        $sanitized_phone_number = '+' . preg_replace('/[^\d]/', '', substr($raw_phone_number, 1));
    } else {
        $sanitized_phone_number = preg_replace('/[^\d]/', '', $raw_phone_number);
        $sanitized_phone_number = '+' . $sanitized_phone_number;
    }

    /* validate format and length (max 15 digits) */
    if (!preg_match('/^\+\d{1,15}$/', $sanitized_phone_number)) {
        return null;
    }

    return $sanitized_phone_number;
}

/* Get continent code by country code */
function get_continent_code_from_country_code($country_code) {
    $continent_codes = [
        'AF' => ['DZ', 'AO', 'BJ', 'BW', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD', 'KM', 'CG', 'CD', 'DJ', 'EG', 'GQ', 'ER', 'SZ', 'ET', 'GA', 'GM', 'GH', 'GN', 'GW', 'CI', 'KE', 'LS', 'LR', 'LY', 'MG', 'MW', 'ML', 'MR', 'MU', 'YT', 'MA', 'MZ', 'NA', 'NE', 'NG', 'RE', 'RW', 'ST', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SS', 'SD', 'TZ', 'TG', 'TN', 'UG', 'EH', 'ZM', 'ZW'],
        'AS' => ['AF', 'AM', 'AZ', 'BH', 'BD', 'BT', 'BN', 'KH', 'CN', 'CY', 'GE', 'HK', 'IN', 'ID', 'IR', 'IQ', 'IL', 'JP', 'JO', 'KZ', 'KW', 'KG', 'LA', 'LB', 'MO', 'MY', 'MV', 'MN', 'MM', 'NP', 'KP', 'OM', 'PK', 'PS', 'PH', 'QA', 'SA', 'SG', 'KR', 'LK', 'SY', 'TW', 'TJ', 'TH', 'TL', 'TR', 'TM', 'AE', 'UZ', 'VN', 'YE'],
        'EU' => ['AX', 'AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CZ', 'DK', 'EE', 'FO', 'FI', 'FR', 'DE', 'GI', 'GR', 'GG', 'VA', 'HU', 'IS', 'IE', 'IM', 'IT', 'JE', 'LV', 'LI', 'LT', 'LU', 'MT', 'MD', 'MC', 'ME', 'NL', 'MK', 'NO', 'PL', 'PT', 'RO', 'RU', 'SM', 'RS', 'SK', 'SI', 'ES', 'SJ', 'SE', 'CH', 'UA', 'GB'],
        'NA' => ['AI', 'AG', 'AW', 'BS', 'BB', 'BZ', 'BM', 'BQ', 'CA', 'KY', 'CR', 'CU', 'CW', 'DM', 'DO', 'SV', 'GL', 'GD', 'GP', 'GT', 'HT', 'HN', 'JM', 'MQ', 'MX', 'MS', 'NI', 'PA', 'PR', 'BL', 'KN', 'LC', 'MF', 'PM', 'VC', 'SX', 'TT', 'TC', 'VI', 'US'],
        'SA' => ['AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE'],
        'OC' => ['AS', 'AU', 'CK', 'FJ', 'PF', 'GU', 'KI', 'MH', 'FM', 'NR', 'NC', 'NZ', 'NU', 'NF', 'MP', 'PW', 'PG', 'PN', 'WS', 'SB', 'TK', 'TO', 'TV', 'UM', 'VU', 'WF'],
        'AN' => ['AQ', 'BV', 'HM', 'GS', 'TF']
    ];

    foreach ($continent_codes as $continent_code => $countries) {
        if (in_array($country_code, $countries)) {
            return $continent_code;
        }
    }

    return null;
}
