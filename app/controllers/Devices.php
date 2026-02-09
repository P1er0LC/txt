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

defined('ALTUMCODE') || die();

class Devices extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', ], ['name', 'ip'], ['device_id', 'name', 'last_ping_datetime', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms']));
        $filters->set_default_order_by($this->user->preferences->devices_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `devices` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('devices?' . $filters->get_get() . '&page=%d')));

        /* Generate stats */
        $devices_stats = [
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
            'total_received_sms' => 0,
        ];

        /* Get the devices list for the user */
        $devices = [];
        $devices_result = database()->query("SELECT * FROM `devices` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $devices_result->fetch_object()) {
            $devices_stats['total_sent_sms'] += $row->total_sent_sms;
            $devices_stats['total_pending_sms'] += $row->total_pending_sms;
            $devices_stats['total_failed_sms'] += $row->total_failed_sms;
            $devices_stats['total_received_sms'] += $row->total_received_sms;

            $row->settings = json_decode($row->settings ?? '');
            $row->sims = json_decode($row->sims ?? '');
            $devices[] = $row;
        }

        /* Export handler */
        process_export_json($devices, ['device_id', 'user_id', 'name', 'settings', 'sims', 'device_fcm_token', 'device_model', 'device_brand', 'device_os', 'device_battery', 'device_is_charging', 'ip', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'last_ping_datetime', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime']);
        process_export_csv_new($devices, ['device_id', 'user_id', 'name', 'settings', 'sims', 'device_fcm_token', 'device_model', 'device_brand', 'device_os', 'device_battery', 'device_is_charging', 'ip', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'last_ping_datetime', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime'], ['settings', 'sims']);

        /* Get statistics */
        if(count($devices) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

            $devices_result_query = "
                SELECT
                    COUNT(*) AS `total`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `devices`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $devices_chart = \Altum\Cache::cache_function_result('devices_chart?user_id=' . $this->user->user_id, null, function() use ($devices_result_query) {
                $devices_chart= [];

                $devices_result = database()->query($devices_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $devices_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);
                    $devices_chart[$label]['total'] = $row->total;
                }

                return $devices_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $devices_chart = get_chart_data($devices_chart);
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'devices' => $devices,
            'devices_chart' => $devices_chart ?? null,
            'total_devices' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'devices_stats' => $devices_stats,
        ];

        $view = new \Altum\View('devices/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('devices');
        }

        if(empty($_POST['selected'])) {
            redirect('devices');
        }

        if(!isset($_POST['type'])) {
            redirect('devices');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.devices')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('devices');
                    }

                    foreach($_POST['selected'] as $device_id) {
                        if($device = db()->where('device_id', $device_id)->where('user_id', $this->user->user_id)->getOne('devices', ['device_id'])) {

                            /* Database query */
                            db()->where('device_id', $device_id)->delete('devices');

                            /* Clear the cache */
                            cache()->deleteItem('devices_total?user_id=' . $this->user->user_id);
                            cache()->deleteItem('devices_dashboard?user_id=' . $this->user->user_id);
                            cache()->deleteItem('devices_dashboard?user_id=' . $this->user->user_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('devices');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.devices')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('devices');
        }

        if(empty($_POST)) {
            redirect('devices');
        }

        $device_id = (int) query_clean($_POST['device_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$device = db()->where('device_id', $device_id)->where('user_id', $this->user->user_id)->getOne('devices', ['device_id', 'name'])) {
            redirect('devices');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('device_id', $device_id)->delete('devices');

            /* Clear the cache */
            cache()->deleteItem('devices_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('devices_dashboard?user_id=' . $this->user->user_id);
            cache()->deleteItem('devices_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $device->name . '</strong>'));

            redirect('devices');
        }

        redirect('devices');
    }
}
