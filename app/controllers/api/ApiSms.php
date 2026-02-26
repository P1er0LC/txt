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

use Altum\Alerts;
use Altum\Date;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiSms extends Controller {
    use Apiable;

    public function index() {

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {

                    if($this->params[0] == 'get_pending') {

                        $this->verify_request(false, false, false);
                        $this->get_pending();

                    } else {

                        $this->verify_request();
                        $this->get();

                    }

                } else {

                    $this->verify_request();
                    $this->get_all();

                }

                break;

            case 'POST':

                /* Detect what method to use */
                if(isset($this->params[0])) {

                    if($this->params[0] == 'receive') {

                        $this->verify_request(false, false, false);
                        $this->receive();

                    } else if($this->params[0] == 'update_status') {

                        $this->verify_request(false, false, false);
                        $this->update_status();

                    }

                } else {

                    $this->verify_request();
                    $this->post();

                }

                break;

            case 'DELETE':

                $this->verify_request();
                $this->delete();

                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], [], []));
        $filters->set_default_order_by('sms_id', $this->api_user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->api_user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `sms` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/sms?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `sms`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'sms_id' => (int) $row->sms_id,
                'device_id' => (int) $row->device_id,
                'sim_subscription_id' => (int) $row->sim_subscription_id,
                'contact_id' => (int) $row->contact_id,
                'campaign_id' => (int) $row->campaign_id,
                'flow_id' => (int) $row->flow_id,
                'rss_automation_id' => (int) $row->rss_automation_id,
                'recurring_campaign_id' => (int) $row->recurring_campaign_id,
                'user_id' => (int) $row->user_id,
                'type' => $row->type,
                'content' => $row->content,
                'status' => $row->status,
                'error' => $row->error,
                'scheduled_datetime' => $row->scheduled_datetime,
                'datetime' => $row->datetime,
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

        $sms_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $sms = db()->where('sms_id', $sms_id)->where('user_id', $this->api_user->user_id)->getOne('sms');

        /* We haven't found the resource */
        if(!$sms) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'sms_id' => (int) $sms->sms_id,
            'device_id' => (int) $sms->device_id,
            'sim_subscription_id' => (int) $sms->sim_subscription_id,
            'contact_id' => (int) $sms->contact_id,
            'campaign_id' => (int) $sms->campaign_id,
            'flow_id' => (int) $sms->flow_id,
            'rss_automation_id' => (int) $sms->rss_automation_id,
            'recurring_campaign_id' => (int) $sms->recurring_campaign_id,
            'user_id' => (int) $sms->user_id,
            'type' => $sms->type,
            'content' => $sms->content,
            'status' => $sms->status,
            'error' => $sms->error,
            'scheduled_datetime' => $sms->scheduled_datetime,
            'datetime' => $sms->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function get_pending() {

        $device_id = isset($this->params[1]) ? (int) $this->params[1] : null;

        /* Try to get details about the resource id */
        $device = (new \Altum\Models\Devices())->get_device_by_device_id($device_id);

        /* We haven't found the resource */
        if(!$device || $device->user_id != $this->api_user->user_id) {
            $this->return_404();
        }

        /* Prepare device update */
        $_GET['device_battery'] = isset($_GET['device_battery']) && is_numeric($_GET['device_battery']) && $_GET['device_battery'] >= 0 && $_GET['device_battery'] <= 100 ? (int) $_GET['device_battery'] : 100;
        $_GET['device_is_charging'] = isset($_GET['device_is_charging']) && $_GET['device_is_charging'] ? 1 : 0;

        /* Update device */
        db()->where('device_id', $device->device_id)->update('devices', [
            'device_battery' => $_GET['device_battery'],
            'device_is_charging' => $_GET['device_is_charging'],
            'last_ping_datetime' => get_date(),
        ]);

        /* Get the pending SMS items */
        $sms = db()
            ->where('user_id', $this->api_user->user_id)
            ->where('device_id', $device_id)
            ->where('status', 'pending')
            ->where('scheduled_datetime', get_date(), '<=')
            ->orderBy('scheduled_datetime', 'DESC')
            ->getOne('sms');

        /* Prepare the data */
        $data = [];

        if($sms) {
            /* Get the contact */
            $contact = (new \Altum\Models\Contacts())->get_contact_by_contact_id($sms->contact_id);

            if($contact) {
                $contact->custom_parameters = json_decode($contact->custom_parameters ?? '');

                $replacers = [
                    '{{NAME}}'              => $contact->name,
                    '{{PHONE_NUMBER}}'      => $contact->phone_number,
                    '{{CONTINENT_NAME}}'    => get_continent_from_continent_code($contact->continent_code),
                    '{{COUNTRY_NAME}}'      => get_country_from_country_code($contact->country_code),
                ];

                /* Custom parameters */
                foreach($contact->custom_parameters as $key => $value) {
                    $replacers['{{CUSTOM_PARAMETERS:' . $key . '}}'] = $value;
                }

                /* Process spintax and replacers */
                $content = process_spintax(str_replace(
                    array_keys($replacers),
                    array_values($replacers),
                    $sms->content
                ));

                /* Branding */
                if (!$this->api_user->plan_settings->removable_branding_is_enabled && settings()->sms->branding) $content .= "\r\n" . settings()->sms->branding;

                $data = [
                    'id' => (int) $sms->sms_id,
                    'sim_subscription_id' => (int) $sms->sim_subscription_id,
                    'content' => $content,
                    'phone_number' => $contact->phone_number,
                ];
            }
        }

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for the plan limit */
        $sent_sms_current_month = db()->where('user_id', $this->api_user->user_id)->getValue('users', '`text_sent_sms_current_month`');
        if($this->api_user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month >= $this->api_user->plan_settings->sent_sms_per_month_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->api_user->user_id);

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
            (new \DateTime($_POST['scheduled_datetime'], new \DateTimeZone($this->api_user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s')
            : get_date();

        /* Phone numbers */
        $_POST['phone_numbers'] = trim($_POST['phone_numbers'] ?? '');
        $_POST['phone_numbers'] = preg_split('/[\r\n,]+/', $_POST['phone_numbers']);
        $_POST['phone_numbers'] = array_filter(array_unique($_POST['phone_numbers']));
        $_POST['phone_numbers'] = array_map('get_phone_number', $_POST['phone_numbers']);

        if(empty($_POST['phone_numbers'])) {
            $this->response_error(l('global.error_message.empty_fields'), 401);
        }

        /* Check for any errors */
        $required_fields = ['content', 'device_id', 'sim_subscription_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Make sure to insert all the phone numbers if needed */
        $contacts_ids = (new \Altum\Models\Contacts())->simple_bulk_insert($_POST['phone_numbers']);

        /* Sms messages to be sent */
        $sms_count = count($contacts_ids);

        /* Check for the plan limit */
        if($this->api_user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month + $sms_count > $this->api_user->plan_settings->sent_sms_per_month_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        $insert_counter = 0;
        $notifications = json_encode($devices[$_POST['device_id']]->sms_status_notifications);
        $sms = [];

        foreach($contacts_ids as $contact_id) {
            $sms[] = [
                'contact_id' => $contact_id,
                'device_id' => $_POST['device_id'],
                'sim_subscription_id' => $_POST['sim_subscription_id'],
                'user_id' => $this->api_user->user_id,
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
        $sms_ids = [];
        if(!empty($sms)) {
            $sms_ids = db()->insertInChunks('sms', $sms);
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
        cache()->deleteItem('sms?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('sms_total?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('sms_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'sms_ids' => $sms_ids,
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function receive() {

        /* Check for any errors */
        $required_fields = ['phone_number', 'device_id', 'content', 'sim_subscription_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['phone_number'] = get_phone_number($_POST['phone_number']);
        $_POST['content'] = input_clean($_POST['content'], 10000);
        $_POST['device_id'] = (int) $_POST['device_id'];
        $_POST['sim_subscription_id'] = (int) input_clean($_POST['sim_subscription_id'], 20);

        /* Try to get details about the resource id */
        $device = (new \Altum\Models\Devices())->get_device_by_device_id($_POST['device_id']);

        /* We haven't found the resource */
        if(!$device || $device->user_id != $this->api_user->user_id) {
            $this->return_404();
        }

        /* Check for start / stop words */
        $contact_has_opted_out = false;
        if(in_array($_POST['content'], $this->api_user->preferences->start_words)) {
            $contact_has_opted_out = false;
        }

        if(in_array($_POST['content'], $this->api_user->preferences->stop_words)) {
            $contact_has_opted_out = true;
        }

        /* Get data about the contact or create a new one */
        $contact = (new \Altum\Models\Contacts())->get_contact_by_phone_number($_POST['phone_number']);

        /* Create contact */
        if(!$contact || $contact->user_id != $this->api_user->user_id) {

            $country_code = null;
            try {
                $phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
                $phone_number_object = $phone_number_util->parse($_POST['phone_number'], null);
                $country_code = $phone_number_util->getRegionCodeForNumber($phone_number_object);
            } catch (\Exception $exception) {
                /* :) */
            }

            $continent_code = get_continent_code_from_country_code($country_code);

            /* Database query */
            $contact_id = db()->insert('contacts', [
                'user_id' => $this->api_user->user_id,
                'phone_number' => $_POST['phone_number'],
                'continent_code' => $continent_code,
                'country_code' => $country_code,
                'custom_parameters' => json_encode([]),
                'has_opted_out' => (int) $contact_has_opted_out,
                'last_received_datetime' => get_date(),
                'datetime' => get_date(),
            ]);

        } else {
            $contact_id = $contact->contact_id;

            /* Database query */
            db()->where('contact_id', $contact_id)->update('contacts', [
                'has_opted_out' => (int) $contact_has_opted_out,
                'total_received_sms' => db()->inc(),
                'last_received_datetime' => get_date(),
            ]);
        }

        /* Database query */
        $sms_id = db()->insert('sms', [
            'user_id' => $this->api_user->user_id,
            'device_id' => $device->device_id,
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'contact_id' => $contact_id,
            'type' => 'received',
            'status' => 'received',
            'content' => $_POST['content'],
            'datetime' => get_date(),
        ]);

        /* Prepare device update */
        $_POST['device_battery'] = isset($_POST['device_battery']) && is_numeric($_POST['device_battery']) && $_POST['device_battery'] >= 0 && $_POST['device_battery'] <= 100 ? (int) $_POST['device_battery'] : 100;
        $_POST['device_is_charging'] = isset($_POST['device_is_charging']) && $_POST['device_is_charging'] ? 1 : 0;

        /* Update device */
        db()->where('device_id', $device->device_id)->update('devices', [
            'device_battery' => $_POST['device_battery'],
            'device_is_charging' => $_POST['device_is_charging'],
            'total_received_sms' => db()->inc(),
            'last_received_datetime' => get_date(),
        ]);

        /* 🚀 WEBHOOK GLOBAL A CHATWOOT - Siempre se dispara para TODOS los SMS recibidos */
        $chatwoot_webhook_url = 'https://chat.buho.la/webhooks/buhotext';
        
        $notification_data = [
            'device_id'            => $device->device_id,
            'contact_id'           => $contact_id,
            'phone_number'         => $_POST['phone_number'],
            'message'              => $_POST['content'],
            'content'              => $_POST['content'],
            'sim_subscription_id'  => $_POST['sim_subscription_id'],
            'sms_id'               => $sms_id,
            'device_name'          => $device->name,
            'datetime'             => get_date(),
            'url'                  => url('contact-view/' . $contact_id),
        ];
        
        /* Enviar webhook a Chatwoot (sin esperar respuesta) */
        fire_and_forget('post', $chatwoot_webhook_url, $notification_data);

        /* Processing the notification handlers (opcional - para otros tipos de notificaciones) */
        if(count($device->notifications ?? [])) {
            $user = (new \Altum\Models\User())->get_user_by_user_id($device->user_id);

            /* Fetch user-level notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($device->user_id);

            /* Core data to be sent to the new processor (reutilizamos $notification_data definida arriba) */

            /* Build a plain caught-data string for the generic message */
            $dynamic_message_data = \Altum\NotificationHandlers::build_dynamic_message_data($notification_data);

            /* Compose the generic notification text */
            $notification_message = sprintf(
                l('sms.simple_notification', $user->language),
                $_POST['phone_number'],
                $device->name,
                $dynamic_message_data,
                $notification_data['url']
            );

            /* Prepare the email template used by the email handler */
            $email_template = get_email_template(
                [
                    '{{PHONE_NUMBER}}' => $_POST['phone_number'],
                ],
                l('global.emails.user_new_sms.subject', $user->language),
                [
                    '{{PHONE_NUMBER}}'  => $_POST['phone_number'],
                    '{{DEVICE_NAME}}'   => $device->name,
                    '{{MESSAGE}}'       => $_POST['content'],
                    '{{CONTACT_LINK}}'  => $notification_data['url'],
                ],
                l('global.emails.user_new_sms.body', $user->language)
            );

            /* Build the context passed to the new NotificationHandlers class */
            $context = [
                /* User details */
                'user'                 => $user,

                /* Email */
                'email_template'       => $email_template,

                /* Basic message for most integrations */
                'message'              => $notification_message,

                /* Push notifications */
                'push_title'           => l('sms.push_notification.title', $user->language),
                'push_description'     => sprintf(
                    l('sms.push_notification.description', $user->language),
                    $_POST['phone_number'],
                    $device->name,
                ),

                /* Whatsapp */
                'whatsapp_template'    => 'new_sms',
                'whatsapp_parameters'  => [
                    $_POST['phone_number'],
                    $device->name,
                    $notification_data['url'],
                ],

                /* Twilio call */
                'twilio_call_url'      => SITE_URL .
                    'twiml/sms.simple_notification?param1=' .
                    urlencode($_POST['phone_number']) .
                    '&param2=' . urlencode($device->name) .
                    '&param3=&param4=' . urlencode($notification_data['url']),

                /* Internal notification */
                'internal_icon'        => 'fas fa-comment',

                /* Discord */
                'discord_color'        => '2664261',

                /* Slack */
                'slack_emoji'          => ':large_green_circle:',
            ];

            /* Send notifications */
            \Altum\NotificationHandlers::process(
                $notification_handlers,
                $device->notifications,
                $notification_data,
                $context
            );
        }

        /* Clear the cache */
        cache()->deleteItem('sms_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $sms_id,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function update_status() {

        /* Check for any errors */
        $required_fields = ['sms_id', 'status'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['sms_id'] = (int) $_POST['sms_id'];
        $_POST['status'] = in_array($_POST['status'], ['sent', 'failed']) ? $_POST['status'] : 'sent';
        $_POST['error'] = isset($_POST['error']) ? input_clean($_POST['error'], 32) : null;

        /* Try to get details about the resource id */
        $sms = db()->where('sms_id', $_POST['sms_id'])->where('user_id', $this->api_user->user_id)->getOne('sms', ['sms_id', 'user_id', 'device_id', 'contact_id', 'rss_automation_id', 'recurring_campaign_id', 'campaign_id', 'flow_id', 'notifications']);

        /* We haven't found the resource */
        if(!$sms) {
            $this->return_404();
        }

        /* Parse notifications */
        $sms->notifications = json_decode($sms->notifications ?? '[]');

        /* Database query */
        db()->where('sms_id', $_POST['sms_id'])->update('sms', [
            'sms_id' => $_POST['sms_id'],
            'status' => $_POST['status'],
            'error' => $_POST['error'],
            'datetime' => get_date(),
        ]);

        /* Prepare update fields */
        $update_fields = [
            'total_pending_sms' => db()->dec(),
        ];
        if($_POST['status'] == 'sent') {
            $update_fields['total_sent_sms'] = db()->inc();
        } else {
            $update_fields['total_failed_sms'] = db()->inc();
        }

        /* Update rss automation if needed */
        if($sms->rss_automation_id) {
            db()->where('rss_automation_id', $sms->rss_automation_id)->update('rss_automations', $update_fields);
        }

        /* Update recurring campaign if needed */
        if($sms->recurring_campaign_id) {
            db()->where('recurring_campaign_id', $sms->recurring_campaign_id)->update('recurring_campaigns', $update_fields);
        }

        /* New update fields */
        if($_POST['status'] == 'sent') {
            $update_fields['last_sent_datetime'] = get_date();
        }

        /* Update campaign if needed */
        if($sms->campaign_id) {
            db()->where('campaign_id', $sms->campaign_id)->update('campaigns', $update_fields);
        }

        /* Update flow if needed */
        if($sms->flow_id) {
            db()->where('flow_id', $sms->flow_id)->update('flows', $update_fields);
        }

        /* Update device */
        db()->where('device_id', $sms->device_id)->update('devices', $update_fields);

        /* Update contact */
        db()->where('contact_id', $sms->contact_id)->update('contacts', $update_fields);

        /* Update user */
        if($_POST['status'] == 'sent') {
            db()->where('user_id', $sms->user_id)->update('users', [
                'text_sent_sms_current_month' => db()->inc(),
                'text_total_sent_sms' => db()->inc(),
            ]);
        }

        /* Processing the notification handlers */
        if(count($sms->notifications ?? [])) {
            /* Fetch user-level notification handlers */
            $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($sms->user_id);

            /* Core data to be sent to the new processor */
            $notification_data = [
                'sms_id'            => $sms->sms_id,
                'status'            => $_POST['status'],
                'error'             => $_POST['error'],
                'url'               => url('sms?sms_id=' . $sms->sms_id),
            ];

            /* Send notifications */
            foreach($notification_handlers as $notification_handler) {
                if(!$notification_handler->is_enabled) continue;
                if(!in_array($notification_handler->notification_handler_id, $sms->notifications)) continue;

                \Altum\NotificationHandlers::handle_webhook($notification_handler, $notification_data);
            }
        }

        /* Clear the cache */
        cache()->deleteItem('sms_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $sms->sms_id,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $sms_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $sms = db()->where('sms_id', $sms_id)->where('user_id', $this->api_user->user_id)->getOne('sms');

        /* We haven't found the resource */
        if(!$sms) {
            $this->return_404();
        }

        /* Update resources if needed */
        if($sms->status == 'pending') {
            db()->where('device_id', $sms->device_id)->update('devices', ['total_pending_sms' => db()->dec()]);

            db()->where('contact_id', $sms->contact_id)->update('contacts', ['total_pending_sms' => db()->dec()]);

            if($sms->campaign_id) {
                db()->where('campaign_id', $sms->campaign_id)->update('campaigns', ['total_pending_sms' => db()->dec()]);
            }

            if($sms->flow_id) {
                db()->where('flow_id', $sms->flow_id)->update('flows', ['total_pending_sms' => db()->dec()]);
            }

            if($sms->recurring_campaign_id) {
                db()->where('recurring_campaign_id', $sms->recurring_campaign_id)->update('recurring_campaigns', ['total_pending_sms' => db()->dec()]);
            }

            if($sms->rss_automation_id) {
                db()->where('rss_automation_id', $sms->rss_automation_id)->update('rss_automations', ['total_pending_sms' => db()->dec()]);
            }
        }

        /* Database query */
        db()->where('sms_id', $sms_id)->delete('sms');

        /* Clear the cache */
        cache()->deleteItem('sms_total?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('sms_dashboard?user_id=' . $this->api_user->user_id);

        http_response_code(200);
        die();

    }

}
