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
use Altum\Date;

defined('ALTUMCODE') || die();

class SmsCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.sms')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('sms');
        }

        /* Check for the plan limit */
        $sent_sms_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`text_sent_sms_current_month`');
        if($this->user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month >= $this->user->plan_settings->sent_sms_per_month_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('sms');
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
            $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

            if($_POST['device_id']) {
                /* Get all sim_subscription_id values */
                $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

                /* Check if the provided subscription exists */
                $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
            }

            /* Scheduling */
            $_POST['is_scheduled'] = (int) isset($_POST['is_scheduled']);
            $_POST['scheduled_datetime'] = $_POST['is_scheduled'] && !empty($_POST['scheduled_datetime']) && Date::validate($_POST['scheduled_datetime'], 'Y-m-d H:i:s') ?
                (new \DateTime($_POST['scheduled_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s')
                : get_date();

            /* Phone numbers */
            $_POST['phone_numbers'] = trim($_POST['phone_numbers'] ?? '');
            $_POST['phone_numbers'] = preg_split('/[\r\n,]+/', $_POST['phone_numbers']);
            $_POST['phone_numbers'] = array_filter(array_unique($_POST['phone_numbers']));
            $_POST['phone_numbers'] = array_map('get_phone_number', $_POST['phone_numbers']);

            if(empty($_POST['phone_numbers'])) {
                Alerts::add_field_error('phone_numbers', l('global.error_message.empty_field'));
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['content', 'device_id', 'sim_subscription_id',];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Make sure to insert all the phone numbers if needed */
            $contacts_ids = (new \Altum\Models\Contacts())->simple_bulk_insert($_POST['phone_numbers']);

            /* Sms messages to be sent */
            $sms_count = count($contacts_ids);

            /* Check for the plan limit */
            $sent_sms_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`text_sent_sms_current_month`');
            if($this->user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month + $sms_count > $this->user->plan_settings->sent_sms_per_month_limit) {
                Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $insert_counter = 0;
                $notifications = json_encode($devices[$_POST['device_id']]->sms_status_notifications);
                $sms = [];

                foreach($contacts_ids as $contact_id) {
                    $sms[] = [
                        'contact_id' => $contact_id,
                        'device_id' => $_POST['device_id'],
                        'sim_subscription_id' => $_POST['sim_subscription_id'],
                        'user_id' => $this->user->user_id,
                        'type' => 'sent',
                        'content' => $_POST['content'],
                        'status' => 'pending',
                        'notifications' => $notifications,
                        'scheduled_datetime' => $_POST['scheduled_datetime'],
                        'datetime' => get_date(),
                    ];

                    $insert_counter++;
            if ($insert_counter >= 5000) {
                        db()->insertMulti('sms', $sms);

                        /* Reset */
                        $sms = [];
                        $insert_counter = 0;
                    }
                }

                /* Insert the rest of the SMS if any */
                if(!empty($sms)) {
                    db()->insertInChunks('sms', $sms);
                }

                /* Updates all required contacts stats */
                db()->where('contact_id', $contacts_ids, 'IN')->update('contacts', [
                    'total_pending_sms' => db()->inc()
                ]);

                /* update device */
                db()->where('device_id', $_POST['device_id'])->update('devices', [
                    'total_pending_sms' => db()->inc($sms_count),
                ]);

                /* Wake device to start sending SMS */
                wake_device_to_send_sms($devices[$_POST['device_id']]->device_fcm_token);

                /* Set a nice success message */
                if($_POST['is_scheduled']) {
                    Alerts::add_success(sprintf(l('sms.success_message.scheduled'), '<strong>' . $sms_count . '</strong>', '<strong>' . \Altum\Date::get_time_until($_POST['scheduled_datetime']) . '</strong>'));
                } else {
                    Alerts::add_success(sprintf(l('sms.success_message.send'), '<strong>' . $sms_count . '</strong>'));
                }

                /* Clear the cache */
                cache()->deleteItem('sms?user_id=' . $this->user->user_id);
                cache()->deleteItem('sms_total?user_id=' . $this->user->user_id);
                cache()->deleteItem('sms_dashboard?user_id=' . $this->user->user_id);

                redirect('sms');
            }

        }

        if(isset($_GET['contact_id']) && empty($_POST)) {
            $_GET['contact_id'] = (int) $_GET['contact_id'];
            $contact = (new \Altum\Models\Contacts())->get_contact_by_contact_id($_GET['contact_id']);
            $_POST['phone_numbers'] = [$contact->phone_number];
        }

        $values = [
            'content' => $_POST['content'] ?? null,
            'device_id' => $_POST['device_id'] ?? $_GET['device_id'] ?? array_key_first($devices),
            'sim_subscription_id' => $_POST['sim_subscription_id'] ?? null,
            'is_scheduled' => $_POST['is_scheduled'] ?? null,
            'scheduled_datetime' => $_POST['scheduled_datetime'] ?? '',
            'phone_numbers' => implode("\r\n", $_POST['phone_numbers'] ?? []),
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'devices' => $devices,
        ];

        $view = new \Altum\View('sms-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
