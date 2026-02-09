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

namespace Altum\Controllers;

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiContactsStatistics extends Controller {
    use Apiable;
    public $datetime;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                $this->get_all();

            break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* :) */
        $this->datetime = \Altum\Date::get_start_end_dates_new();

        $type = isset($_GET['type']) && in_array($_GET['type'], [
            'overview',
            'continent_code',
            'country_code',
            'has_opted_out',
        ]) ? query_clean($_GET['type']) : 'overview';

        /* :) */
        $data = [];

        switch($type) {
            case 'overview':

                $convert_tz_sql = get_convert_tz_sql('`datetime`', \Altum\Date::$default_timezone);

                $result = database()->query("
                    SELECT
                        COUNT(*) AS `contacts`,
                        DATE_FORMAT({$convert_tz_sql}, '{$this->datetime['query_date_format']}') AS `formatted_date`
                    FROM
                         `contacts`
                    WHERE
                        `user_id` = {$this->api_user->user_id}
                        AND ({$convert_tz_sql} BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `formatted_date`
                    ORDER BY
                        `formatted_date`
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        'contacts' => (int) $row->contacts,
                        'formatted_date' => $this->datetime['process']($row->formatted_date, true),
                    ];
                }

                break;

            case 'continent_code':
            case 'country_code':
            case 'has_opted_out':

                $result = database()->query("
                    SELECT
                        `{$type}`,
                        COUNT(*) AS `contacts`
                    FROM
                         `contacts`
                    WHERE
                        `user_id` = {$this->api_user->user_id}
                        AND (`datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}')
                    GROUP BY
                        `{$type}`
                    ORDER BY
                        `contacts` DESC
                    
                ");

                while($row = $result->fetch_object()) {
                    $data[] = [
                        $type => $row->{$type},
                        'contacts' => (int) $row->contacts
                    ];
                }

                break;

        }

        Response::jsonapi_success($data);

    }

}
