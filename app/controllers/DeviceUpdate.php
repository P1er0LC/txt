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

namespace Altum\controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class DeviceUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.devices')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('devices');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `devices` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->devices_limit != -1 && $total_rows > $this->user->plan_settings->devices_limit) {
            redirect('devices');
        }

        $device_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$device = db()->where('device_id', $device_id)->where('user_id', $this->user->user_id)->getOne('devices')) {
            redirect('devices');
        }

        $device->custom_parameters = json_decode($device->custom_parameters ?? '');
        $device->notifications = json_decode($device->notifications ?? '');
        $device->sms_status_notifications = json_decode($device->sms_status_notifications ?? '');
        $device->settings = json_decode($device->settings ?? '');

        /* Get available notification handlers */
        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['webhooks_on_sms_status_update'] = isset($_POST['webhooks_on_sms_status_update']) ? (int) (bool) $_POST['webhooks_on_sms_status_update'] : 1;
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

            $_POST['sms_status_notifications'] = array_map(
                function($notification_handler_id) {
                    return (int) $notification_handler_id;
                },
                array_filter($_POST['sms_status_notifications'] ?? [], function($notification_handler_id) use($notification_handlers) {
                    return array_key_exists($notification_handler_id, $notification_handlers);
                })
            );
            if($this->user->plan_settings->active_notification_handlers_per_resource_limit != -1) {
                $_POST['sms_status_notifications'] = array_slice($_POST['sms_status_notifications'], 0, $this->user->plan_settings->active_notification_handlers_per_resource_limit);
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $settings = [
                    'sms_in_between_delay' => $_POST['sms_in_between_delay'],
                ];

                /* Notification handlers */
                $notifications = json_encode($_POST['notifications']);
                $sms_status_notifications = json_encode($_POST['sms_status_notifications']);

                /* Database query */
                db()->where('device_id', $device->device_id)->update('devices', [
                    'name' => $_POST['name'],
                    'notifications' => $notifications,
                    'sms_status_notifications' => $sms_status_notifications,
                    'settings' => json_encode($settings),
                    'last_datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $device->name . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('device?device_id=' . $device->device_id);
                cache()->deleteItem('devices_dashboard?user_id=' . $this->user->user_id);

                /* Refresh the page */
                redirect('device-update/' . $device_id);
            }
        }

        /* Prepare the view */
        $data = [
            'device' => $device,
            'notification_handlers' => $notification_handlers,
        ];

        $view = new \Altum\View('device-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
