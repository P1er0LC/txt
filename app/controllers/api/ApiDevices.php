<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode devices via https://altumcode.com/
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

class ApiDevices extends Controller {
    use Apiable;

    public function index() {

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {

                    if(isset($this->params[1]) && $this->params[1] == 'connect') {
                        $this->connect();
                    }

                    $this->patch();
                } else {
                    $this->post();
                }

                break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], []));
        $filters->set_default_order_by('device_id', $this->api_user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->api_user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `devices` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/devices?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `devices`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->device_id,
                'device_id' => (int) $row->device_id,
                'user_id' => (int) $row->user_id,
                'name' => $row->name,
                'notifications' => json_decode($row->notifications ?? ''),
                'settings' => json_decode($row->settings ?? ''),
                'sims' => json_decode($row->sims ?? ''),
                'device_fcm_token' => $row->device_fcm_token,
                'device_model' => $row->device_model,
                'device_brand' => $row->device_brand,
                'device_os' => $row->device_os,
                'device_battery' => (int) $row->device_battery,
                'device_is_charging' => (bool) $row->device_is_charging,
                'ip' => $row->ip,
                'total_sent_sms' => (int) $row->total_sent_sms,
                'total_pending_sms' => (int) $row->total_pending_sms,
                'total_received_sms' => (int) $row->total_received_sms,
                'total_failed_sms' => (int) $row->total_failed_sms,
                'last_sent_datetime' => $row->last_sent_datetime,
                'last_ping_datetime' => $row->last_ping_datetime,
                'last_received_datetime' => $row->last_received_datetime,
                'datetime' => $row->datetime,
                'last_datetime' => $row->last_datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $device_id = isset($this->params[0]) ? $this->params[0] : null;

        /* Try to get details about the resource id or code */
        if(is_numeric($device_id)) {
            $device = db()->where('device_id', (int) $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        } else {
            $device = db()->where('device_code', $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        }

        /* We haven't found the resource */
        if(!$device) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $device->device_id ,
            'user_id' => (int) $device->user_id,
            'name' => $device->name,
            'notifications' => json_decode($device->notifications ?? ''),
            'settings' => json_decode($device->settings ?? ''),
            'sims' => json_decode($device->sims ?? ''),
            'device_fcm_token' => $device->device_fcm_token,
            'device_model' => $device->device_model,
            'device_brand' => $device->device_brand,
            'device_os' => $device->device_os,
            'device_battery' => (int) $device->device_battery,
            'device_is_charging' => (bool) $device->device_is_charging,
            'ip' => $device->ip,
            'total_sent_sms' => (int) $device->total_sent_sms,
            'total_pending_sms' => (int) $device->total_pending_sms,
            'total_received_sms' => (int) $device->total_received_sms,
            'total_failed_sms' => (int) $device->total_failed_sms,
            'last_sent_datetime' => $device->last_sent_datetime,
            'last_ping_datetime' => $device->last_ping_datetime,
            'last_received_datetime' => $device->last_received_datetime,
            'datetime' => $device->datetime,
            'last_datetime' => $device->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('devices', 'count(`device_id`)');

        if($this->api_user->plan_settings->devices_limit != -1 && $total_rows >= $this->api_user->plan_settings->devices_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Check for any errors */
        $required_fields = ['name'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['sms_in_between_delay'] = max(1, min(300, isset($_POST['sms_in_between_delay']) ? (int) $_POST['sms_in_between_delay'] : 1));

        /* Notification handlers */
        $_POST['notifications'] = array_map(
            function($notification_handler_id) {
                return (int) $notification_handler_id;
            },
            array_filter($_POST['notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['notifications'] = array_slice($_POST['notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        $settings = [
            'sms_in_between_delay' => $_POST['sms_in_between_delay'],
        ];

        /* Generate unique device code */
        do {
            $device_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
            $code_exists = db()->where('device_code', $device_code)->getValue('devices', 'device_id');
        } while($code_exists);

        /* Database query */
        $device_id = db()->insert('devices', [
            'user_id' => $this->api_user->user_id,
            'device_code' => $device_code,
            'name' => $_POST['name'],
            'notifications' => json_encode($_POST['notifications']),
            'settings' => json_encode($settings),
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('devices?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('devices_total?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $device_id,
            'user_id' => (int) $this->api_user->user_id,
            'name' => $_POST['name'],
            'notifications' => $_POST['notifications'],
            'settings' => $settings,
            'sims' => [],
            'device_fcm_token' => null,
            'device_model' => null,
            'device_brand' => null,
            'device_os' => null,
            'device_battery' => (int) 0,
            'device_is_charging' => (bool) false,
            'ip' => null,
            'total_sent_sms' => (int) 0,
            'total_pending_sms' => (int) 0,
            'total_received_sms' => (int) 0,
            'total_failed_sms' => (int) 0,
            'last_sent_datetime' => null,
            'last_ping_datetime' => null,
            'last_received_datetime' => null,
            'datetime' => get_date(),
            'last_datetime' => null,
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $device_id = isset($this->params[0]) ? $this->params[0] : null;

        /* Try to get details about the resource id or code */
        if(is_numeric($device_id)) {
            $device = db()->where('device_id', (int) $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        } else {
            $device = db()->where('device_code', $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        }

        /* We haven't found the resource */
        if(!$device) {
            $this->return_404();
        }

        $device->notifications = json_decode($device->notifications ?? '');

        $_POST['name'] = trim($_POST['name'] ?? $device->name);
        $_POST['sms_in_between_delay'] = max(1, min(300, isset($_POST['sms_in_between_delay']) ? (int) $_POST['sms_in_between_delay'] : 1));

        /* Notification handlers */
        $_POST['notifications'] = array_map(
            function($notification_handler_id) {
                return (int) $notification_handler_id;
            },
            array_filter($_POST['notifications'] ?? $device->notifications, function($notification_handler_id) use($notification_handlers) {
                return array_key_exists($notification_handler_id, $notification_handlers);
            })
        );
        if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
            $_POST['notifications'] = array_slice($_POST['notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
        }

        $settings = [
            'sms_in_between_delay' => $_POST['sms_in_between_delay'],
        ];

        /* Database query */
        db()->where('device_id', $device->device_id)->update('devices', [
            'name' => $_POST['name'],
            'notifications' => json_encode($_POST['notifications']),
            'settings' => json_encode($settings),
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('devices?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('devices_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $device->device_id,
            'user_id' => $this->api_user->user_id,
            'name' => $_POST['name'],
            'sims' => json_decode($device->sims ?? ''),
            'device_fcm_token' => $device->device_fcm_token,
            'device_model' => $device->device_model,
            'device_brand' => $device->device_brand,
            'device_os' => $device->device_os,
            'device_battery' => (int) $device->device_battery,
            'device_is_charging' => (bool) $device->device_is_charging,
            'ip' => $device->ip,
            'total_sent_sms' => (int) $device->total_sent_sms,
            'total_pending_sms' => (int) $device->total_pending_sms,
            'total_received_sms' => (int) $device->total_received_sms,
            'total_failed_sms' => (int) $device->total_failed_sms,
            'last_sent_datetime' => $device->last_sent_datetime,
            'last_ping_datetime' => $device->last_ping_datetime,
            'last_received_datetime' => $device->last_received_datetime,
            'last_datetime' => get_date(),
            'datetime' => $device->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function connect() {

        $device_id = isset($this->params[0]) ? $this->params[0] : null;

        /* Try to get details about the resource id or code */
        if(is_numeric($device_id)) {
            $device = db()->where('device_id', (int) $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        } else {
            $device = db()->where('device_code', $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        }

        /* We haven't found the resource */
        if(!$device) {
            $this->return_404();
        }

        $_POST['device_model'] = isset($_POST['device_model']) ? input_clean($_POST['device_model'], 128) : null;
        $_POST['device_brand'] = isset($_POST['device_brand']) ? input_clean($_POST['device_brand'], 128) : null;
        $_POST['device_os'] = isset($_POST['device_os']) ? input_clean($_POST['device_os'], 128) : null;
        $_POST['device_battery'] = isset($_POST['device_battery']) && is_numeric($_POST['device_battery']) && $_POST['device_battery'] >= 0 && $_POST['device_battery'] <= 100 ? (int) $_POST['device_battery'] : 100;
        $_POST['device_is_charging'] = isset($_POST['device_is_charging']) && $_POST['device_is_charging'] ? 1 : 0;
        $_POST['ip'] = isset($_POST['ip']) ? input_clean($_POST['ip']) : null;
        $_POST['device_fcm_token'] = isset($_POST['device_fcm_token']) ? input_clean($_POST['device_fcm_token']) : null;

        $sims = [];
        if(isset($_POST['sims']) && is_array($_POST['sims'])) {
            foreach ($_POST['sims'] as $sim) {
                if (!$sim['subscription_id']) continue;

                $sims[] = [
                    'subscription_id' => (int) input_clean($sim['subscription_id'], 20),
                    'phone_number' => get_phone_number($sim['phone_number']),
                    'carrier_name' => input_clean($sim['carrier_name'], 32),
                    'display_name' => 'SIM ' . $sim['slot_index'] + 1,
                    'slot_index' => (int) $sim['slot_index'],
                ];
            }
        }

        /* Database query */
        db()->where('device_id', $device->device_id)->update('devices', [
            'device_fcm_token' => $_POST['device_fcm_token'],
            'sims' => json_encode($sims),
            'device_model' => $_POST['device_model'],
            'device_brand' => $_POST['device_brand'],
            'device_os' => $_POST['device_os'],
            'device_battery' => $_POST['device_battery'],
            'device_is_charging' => $_POST['device_is_charging'],
            'ip' => $_POST['ip'],
            'last_ping_datetime' => get_date(),
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('devices?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('devices_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $device->device_id,
            'name' =>  $device->name,
            'settings' => json_decode($device->settings ?? ''),
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $device_id = isset($this->params[0]) ? $this->params[0] : null;

        /* Try to get details about the resource id or code */
        if(is_numeric($device_id)) {
            $device = db()->where('device_id', (int) $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        } else {
            $device = db()->where('device_code', $device_id)->where('user_id', $this->api_user->user_id)->getOne('devices');
        }

        /* We haven't found the resource */
        if(!$device) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('device_id', $device_id)->delete('devices');

        /* Clear the cache */
        cache()->deleteItem('devices?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('devices_total?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('devices_dashboard?user_id=' . $this->api_user->user_id);

        http_response_code(200);
        die();

    }

}
