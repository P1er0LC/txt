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

use Altum\Alerts;
use Altum\Title;

defined('ALTUMCODE') || die();

class ContactsStatistics extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->user->plan_settings->analytics_is_enabled) {
            Alerts::add_error(l('global.info_message.plan_feature_no_access'));
            redirect('contacts');
        }

        /* Statistics related variables */
        $type = isset($_GET['type']) && in_array($_GET['type'], ['overview', 'continent_code', 'country', 'has_opted_out']) ? input_clean($_GET['type']) : 'overview';

        $datetime = \Altum\Date::get_start_end_dates_new();

        /* Get data based on what statistics are needed */
        switch($type) {
            case 'overview':

                /* Get the required statistics */
                $contacts_chart = [];

                $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

                $contacts_result = database()->query("
                    SELECT
                        COUNT(*) AS `total`,
                        DATE_FORMAT({$convert_tz_sql}, '{$datetime['query_date_format']}') AS `formatted_date`
                    FROM
                         `contacts`
                    WHERE
                        1 = 1
                        AND ({$convert_tz_sql} BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `formatted_date`
                    ORDER BY
                        `formatted_date`
                ");

                /* Generate the raw chart data and save contacts for later usage */
                while($row = $contacts_result->fetch_object()) {
                    $contacts[] = $row;

                    $row->formatted_date = $datetime['process']($row->formatted_date, true);

                    $contacts_chart[$row->formatted_date] = [
                        'total' => $row->total,
                    ];
                }

                $contacts_chart = get_chart_data($contacts_chart);

                $limit = $this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page;
                $result = database()->query("
                    SELECT
                        *
                    FROM
                        `contacts`
                    WHERE
                        1 = 1
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    ORDER BY
                        `datetime` DESC
                    LIMIT {$limit}
                ");

                break;

            case 'continent_code':
            case 'country':
            case 'has_opted_out':

                $columns = [
                    'continent_code' => 'continent_code',
                    'country' => 'country_code',
                    'has_opted_out' => 'has_opted_out',
                ];

                $result = database()->query("
                    SELECT
                        `{$columns[$type]}`,
                        COUNT(*) AS `total`
                    FROM
                         `contacts`
                    WHERE
                        1 = 1
                        AND (`datetime` BETWEEN '{$datetime['query_start_date']}' AND '{$datetime['query_end_date']}')
                    GROUP BY
                        `{$columns[$type]}`
                    ORDER BY
                        `total` DESC
                    
                ");

                break;

        }

        switch($type) {
            case 'overview':

                $statistics_keys = [
                    'continent_code',
                    'country_code',
                    'has_opted_out',
                ];

                $statistics = [];
                foreach($statistics_keys as $key) {
                    $statistics[$key] = [];
                    $statistics[$key . '_total_sum'] = 0;
                }

                $has_data = $result->num_rows;

                /* Start processing the rows from the database */
                while($row = $result->fetch_object()) {
                    foreach($statistics_keys as $key) {

                        $statistics[$key][$row->{$key}] = isset($statistics[$key][$row->{$key}]) ? $statistics[$key][$row->{$key}] + 1 : 1;

                        $statistics[$key . '_total_sum']++;

                    }
                }

                foreach($statistics_keys as $key) {
                    arsort($statistics[$key]);
                }

                /* Prepare the statistics method View */
                $data = [
                    'statistics' => $statistics,
                    'datetime' => $datetime,
                    'contacts_chart' => $contacts_chart ?? null,
                    'has_data' => $has_data,
                ];

                break;

            case 'continent_code':
            case 'country':
            case 'has_opted_out':

                /* Store all the results from the database */
                $statistics = [];
                $statistics_total_sum = 0;

                while($row = $result->fetch_object()) {
                    $statistics[] = $row;

                    $statistics_total_sum += $row->total;
                }

                $has_data = count($statistics);

                /* Prepare the statistics method View */
                $data = [
                    'rows' => $statistics,
                    'total_sum' => $statistics_total_sum,
                    'datetime' => $datetime,
                    'has_data' => $has_data,
                    'country_code' => $country_code ?? null,
                ];


                break;
        }

        /* Set a custom title */
        if(isset($website)) {
            Title::set(sprintf(l('contacts_statistics.title_dynamic'), $website->name));
        } else {
            Title::set(l('contacts_statistics.title'));
        }

        /* Export handler */
        process_export_csv($statistics);
        process_export_json($statistics);

        $data['type'] = $type;
        $view = new \Altum\View('contacts-statistics/statistics_' . $type, (array) $this);
        $this->add_view_content('statistics', $view->run($data));

        /* Prepare the view */
        $data = [
            'type' => $type,
            'datetime' => $datetime,
            'has_data' => $has_data,
        ];

        $view = new \Altum\View('contacts-statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
