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

class AdminDevices extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', ], ['name', 'ip'], ['device_id', 'name', 'last_ping_datetime', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms']));
        $filters->set_default_order_by($this->user->preferences->devices_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `devices` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/devices?' . $filters->get_get() . '&page=%d')));

        /* Get the devices list for the user */
        $devices = [];
        $devices_result = database()->query("
            SELECT
                `devices`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `devices`
            LEFT JOIN
                `users` ON `devices`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('devices')}
                {$filters->get_sql_order_by('devices')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $devices_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $row->sims = json_decode($row->sims ?? '');
            $devices[] = $row;
        }

        /* Export handler */
        process_export_json($devices, ['device_id', 'user_id', 'name', 'settings', 'sims', 'device_fcm_token', 'device_model', 'device_brand', 'device_os', 'device_battery', 'device_is_charging', 'ip', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'last_ping_datetime', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime']);
        process_export_csv_new($devices, ['device_id', 'user_id', 'name', 'settings', 'sims', 'device_fcm_token', 'device_model', 'device_brand', 'device_os', 'device_battery', 'device_is_charging', 'ip', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'last_ping_datetime', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime'], ['settings', 'sims']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'devices' => $devices,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/devices/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/devices');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/devices');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/devices');
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

                    foreach($_POST['selected'] as $device_id) {
                        if($device = db()->where('device_id', $device_id)->getOne('devices', ['device_id', 'user_id'])) {

                            /* Database query */
                            db()->where('device_id', $device_id)->delete('devices');

                            /* Clear the cache */
                            cache()->deleteItem('devices_total?user_id=' . $device->user_id);
                            cache()->deleteItem('devices_dashboard?user_id=' . $device->user_id);
                            cache()->deleteItem('devices_dashboard?user_id=' . $device->user_id);

                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/devices');
    }

    public function delete() {

        $device_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$device = db()->where('device_id', $device_id)->getOne('devices', ['device_id', 'user_id'])) {
            redirect('admin/devices');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('device_id', $device_id)->delete('devices');

            /* Clear the cache */
            cache()->deleteItem('devices_total?user_id=' . $device->user_id);
            cache()->deleteItem('devices_dashboard?user_id=' . $device->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

        }

        redirect('admin/devices');
    }
}
