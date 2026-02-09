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

use Altum\Date;
use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiCampaigns extends Controller {
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
        $filters->set_default_order_by($this->api_user->preferences->campaigns_default_order_by, $this->api_user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->api_user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `campaigns` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/campaigns?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `campaigns`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->campaign_id,
                'device_id' => (int) $row->device_id,
                'sim_subscription_id' => (int) $row->sim_subscription_id,
                'user_id' => (int) $row->user_id,
                'rss_automation_id' => (int) $row->rss_automation_id,
                'recurring_campaign_id' => (int) $row->recurring_campaign_id,
                'name' => $row->name,
                'content' => $row->content,
                'segment' => $row->segment,
                'settings' => json_decode($row->settings),
                'contacts_ids' => json_decode($row->contacts_ids),
                'sent_contacts_ids' => json_decode($row->sent_contacts_ids),
                'total_sent_sms' => (int) $row->total_sent_sms,
                'total_pending_sms' => (int) $row->total_pending_sms,
                'total_failed_sms' => (int) $row->total_failed_sms,
                'status' => $row->status,
                'scheduled_datetime' => $row->scheduled_datetime,
                'last_sent_datetime' => $row->last_sent_datetime,
                'last_datetime' => $row->last_datetime,
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

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->api_user->user_id)->getOne('campaigns');

        /* We haven't found the resource */
        if(!$campaign) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $campaign->campaign_id,
            'device_id' => (int) $campaign->device_id,
            'sim_subscription_id' => (int) $campaign->sim_subscription_id,
            'user_id' => (int) $campaign->user_id,
            'rss_automation_id' => (int) $campaign->rss_automation_id,
            'recurring_campaign_id' => (int) $campaign->recurring_campaign_id,
            'name' => $campaign->name,
            'content' => $campaign->content,
            'segment' => $campaign->segment,
            'settings' => json_decode($campaign->settings),
            'contacts_ids' => json_decode($campaign->contacts_ids),
            'sent_contacts_ids' => json_decode($campaign->sent_contacts_ids),
            'total_sent_sms' => (int) $campaign->total_sent_sms,
            'total_pending_sms' => (int) $campaign->total_pending_sms,
            'total_failed_sms' => (int) $campaign->total_failed_sms,
            'status' => $campaign->status,
            'scheduled_datetime' => $campaign->scheduled_datetime,
            'last_sent_datetime' => $campaign->last_sent_datetime,
            'last_datetime' => $campaign->last_datetime,
            'datetime' => $campaign->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['name', 'content', 'device_id', 'sim_subscription_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for the plan limit */
        $campaigns_current_month = db()->where('user_id', $this->api_user->user_id)->getValue('users', '`text_campaigns_current_month`');
        if($this->api_user->plan_settings->campaigns_per_month_limit != -1 && $campaigns_current_month >= $this->api_user->plan_settings->campaigns_per_month_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->api_user->user_id);

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->api_user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
        $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

        /* Validate device_id exists */
        if(!$_POST['device_id']) {
            $this->response_error('Invalid device_id. Device not found or does not belong to user.', 400);
        }

        /* Get all sim_subscription_id values */
        $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

        /* Check if the provided subscription exists */
        $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
        
        /* Validate sim_subscription_id exists */
        if($_POST['sim_subscription_id'] === null) {
            $this->response_error('Invalid sim_subscription_id. SIM not found in device.', 400);
        }

        /* Phone numbers */
        $_POST['phone_numbers'] = trim($_POST['phone_numbers'] ?? '');
        $_POST['phone_numbers'] = preg_split('/[\r\n,]+/', $_POST['phone_numbers']);
        $_POST['phone_numbers'] = array_filter(array_unique($_POST['phone_numbers']));
        $_POST['phone_numbers'] = array_map('get_phone_number', $_POST['phone_numbers']);

        if(empty($_POST['phone_numbers'])) {
            $_POST['phone_numbers'] = [];
        }

        /* Contacts ids */
        $_POST['contacts_ids'] = $_POST['contacts_ids'] ?? '';
        // Handle both string and array (multipart/form-data can send arrays)
        if(is_array($_POST['contacts_ids'])) {
            $_POST['contacts_ids'] = array_filter(array_map('intval', $_POST['contacts_ids']));
        } else {
            $_POST['contacts_ids'] = trim($_POST['contacts_ids']);
            $_POST['contacts_ids'] = array_filter(array_map('intval', explode(',', $_POST['contacts_ids'])));
        }
        $_POST['contacts_ids'] = array_values(array_unique($_POST['contacts_ids']));
        $_POST['contacts_ids'] = $_POST['contacts_ids'] ?: [0];

        /* Segment */
        $segment_type = null;
        $_POST['segment'] = $_POST['segment'] ?? null;
        if(is_numeric($_POST['segment'])) {

            /* Get settings from custom segments */
            $segment = $segments[$_POST['segment']];

            if(!$segment) {
                $_POST['segment'] = 'all';
            }

            switch($segment->type) {
                case 'bulk':
                case 'custom':

                    $segment_type = 'custom';
                    $_POST['contacts_ids'] = implode(',', $segment->settings->contacts_ids);

                    break;

                case 'filter':

                    $segment_type = 'filter';

                    if(isset($segment->settings->filters_countries)) $_POST['filters_countries'] = $segment->settings->filters_countries ?? [];
                    if(isset($segment->settings->filters_continents)) $_POST['filters_continents'] = $segment->settings->filters_continents ?? [];
                    if(isset($segment->settings->filters_custom_parameters) && count($segment->settings->filters_custom_parameters)) {
                        foreach($segment->settings->filters_custom_parameters as $key => $custom_parameter) {
                            $_POST['filters_custom_parameter_key'][$key] = $custom_parameter->key;
                            $_POST['filters_custom_parameter_condition'][$key] = $custom_parameter->condition;
                            $_POST['filters_custom_parameter_value'][$key] = $custom_parameter->value;
                        }
                    }

                    break;
            }

        } else {
            $_POST['segment'] = in_array($_POST['segment'], ['all', 'bulk', 'custom', 'filter']) ? input_clean($_POST['segment']) : 'all';
            $segment_type = $_POST['segment'];
        }

        /* Scheduling */
        $_POST['is_scheduled'] = (int) isset($_POST['is_scheduled']);
        $_POST['scheduled_datetime'] = $_POST['is_scheduled'] && !empty($_POST['scheduled_datetime']) && Date::validate($_POST['scheduled_datetime'], 'Y-m-d H:i:s') ?
            (new \DateTime($_POST['scheduled_datetime'], new \DateTimeZone($this->api_user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s')
            : get_date();

        /* Contacts ids */
        $_POST['contacts_ids'] = $_POST['contacts_ids'] ?? '';
        // Handle both string and array (multipart/form-data can send arrays)
        if(is_array($_POST['contacts_ids'])) {
            $_POST['contacts_ids'] = array_filter(array_map('intval', $_POST['contacts_ids']));
        } else {
            $_POST['contacts_ids'] = trim($_POST['contacts_ids']);
            $_POST['contacts_ids'] = array_filter(array_map('intval', explode(',', $_POST['contacts_ids'])));
        }
        $_POST['contacts_ids'] = array_values(array_unique($_POST['contacts_ids']));
        $_POST['contacts_ids'] = $_POST['contacts_ids'] ?: [0];

        $settings = [
            /* Scheduling */
            'is_scheduled' => $_POST['is_scheduled'],
        ];

        /* Get all the users needed */
        switch($segment_type) {
            case 'all':
                $contacts = db()->where('user_id', $this->api_user->user_id)->where('has_opted_out', 0)->get('contacts', null, ['contact_id', 'user_id']);
                break;

            case 'bulk':

                (new \Altum\Models\Contacts())->simple_bulk_insert($_POST['phone_numbers']);

                $contacts = db()->where('user_id', $this->api_user->user_id)->where('phone_number', $_POST['phone_numbers'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);

                $settings['phone_numbers'] = $_POST['phone_numbers'];

                $_POST['segment'] = 'custom';

                break;

            case 'custom':
                $contacts = db()->where('user_id', $this->api_user->user_id)->where('contact_id', $_POST['contacts_ids'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
                break;

            case 'filter':

                $query = db()->where('user_id', $this->api_user->user_id);

                $has_filters = false;

                /* Custom parameters */
                if(!isset($_POST['filters_custom_parameter_key'])) {
                    $_POST['filters_custom_parameter_key'] = [];
                    $_POST['filters_custom_parameter_condition'] = [];
                    $_POST['filters_custom_parameter_value'] = [];
                }

                $custom_parameters = [];

                foreach($_POST['filters_custom_parameter_key'] as $key => $value) {
                    if(empty(trim($value))) continue;
                    if($key >= 50) continue;

                    $custom_parameters[] = [
                        'key' => input_clean($value, 64),
                        'condition' => isset($_POST['filters_custom_parameter_condition'][$key]) && in_array($_POST['filters_custom_parameter_condition'][$key], ['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than']) ? $_POST['filters_custom_parameter_condition'][$key] : 'exact',
                        'value' => input_clean($_POST['filters_custom_parameter_value'][$key], 512)
                    ];
                }

                if(count($custom_parameters)) {
                    $has_filters = true;
                    $settings['filters_custom_parameters'] = $custom_parameters;

                    foreach($custom_parameters as $custom_parameter) {
                        $key = $custom_parameter['key'];
                        $condition = $custom_parameter['condition'];
                        $value = $custom_parameter['value'];

                        /* reference JSON value once; unquote JSON for string ops, cast for numeric ops */
                        $json_value_expression = 'JSON_UNQUOTE(JSON_EXTRACT(`custom_parameters`, \'$."'.$key.'"\'))';
                        $numeric_expression = 'CAST('.$json_value_expression.' AS DECIMAL(65,10))';

                        switch($condition) {
                            case 'exact':
                                $query->where($json_value_expression.' = \''.$value.'\'');
                                break;

                            case 'not_exact':
                                $query->where($json_value_expression.' != \''.$value.'\'');
                                break;

                            case 'contains':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'%\'');
                                break;

                            case 'not_contains':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'%\'');
                                break;

                            case 'starts_with':
                                $query->where($json_value_expression.' LIKE \''.$value.'%\'');
                                break;

                            case 'not_starts_with':
                                $query->where($json_value_expression.' NOT LIKE \''.$value.'%\'');
                                break;

                            case 'ends_with':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'\'');
                                break;

                            case 'not_ends_with':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'\'');
                                break;

                            case 'bigger_than':
                                $query->where($numeric_expression.' > '.(is_numeric($value) ? $value : '0'));
                                break;

                            case 'lower_than':
                                $query->where($numeric_expression.' < '.(is_numeric($value) ? $value : '0'));
                                break;
                        }
                    }
                }

                /* Countries */
                if(isset($_POST['filters_countries'])) {
                    $_POST['filters_countries'] = array_filter($_POST['filters_countries'] ?? [], function($country) {
                        return array_key_exists($country, get_countries_array());
                    });

                    $has_filters = true;
                    $query->where('country_code', $_POST['filters_countries'], 'IN');
                    $settings['filters_countries'] = $_POST['filters_countries'];
                }

                /* Continents */
                if(isset($_POST['filters_continents'])) {
                    $_POST['filters_continents'] = array_filter($_POST['filters_continents'] ?? [], function($country) {
                        return array_key_exists($country, get_continents_array());
                    });

                    $has_filters = true;
                    $query->where('continent_code', $_POST['filters_continents'], 'IN');
                    $settings['filters_continents'] = $_POST['filters_continents'];
                }

                $contacts = $has_filters ? $query->where('has_opted_out', 0)->get('contacts', null, ['contact_id']) : [];

                db()->reset();

                break;
        }

        /* Get all the contacts ids */
        $contacts_ids = array_column($contacts, 'contact_id');
        $contacts_count = count($contacts_ids);

        /* Free memory */
        unset($contacts);

        $status = $_POST['is_scheduled'] && $_POST['scheduled_datetime'] ? 'scheduled' : 'processing';
        if(isset($_POST['save'])) {
            $status = 'draft';
        }

        if($status != 'draft') {
            /* Check for the plan limit */
            $sent_sms_current_month = db()->where('user_id', $this->api_user->user_id)->getValue('users', '`text_sent_sms_current_month`');
            if($this->api_user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month + count($contacts_ids) >= $this->api_user->plan_settings->sent_sms_per_month_limit) {
                $this->response_error(l('global.info_message.plan_feature_limit'), 401);
            }

            if(!$contacts_count) {
                $this->response_error(l('campaigns.error_message.contacts_ids_is_null'), 401);
            }
        }

        /* Database query */
        $campaign_id = db()->insert('campaigns', [
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => json_encode($settings),
            'contacts_ids' => json_encode($contacts_ids),
            'sent_contacts_ids' => $status == 'processing' ? json_encode($contacts_ids) : '[]',
            'status' => $status == 'processing' ? 'sent' : $status,
            'total_pending_sms' => $status == 'processing' ? $contacts_count : 0,
            'scheduled_datetime' => $_POST['scheduled_datetime'],
            'datetime' => get_date(),
        ]);

        /* Insert all planned SMS messages now, otherwise leave the cronjob to do its job */
        if($status == 'processing') {
            $insert_counter = 0;
            $notifications = json_encode($devices[$_POST['device_id']]->sms_status_notifications);
            $sms = [];

            foreach($contacts_ids as $contact_id) {
                $sms[] = [
                    'contact_id' => $contact_id,
                    'campaign_id' => $campaign_id,
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
            if(!empty($sms)) {
                db()->insertInChunks('sms', $sms);
            }

            /* Updates all required contacts stats */
            db()->where('contact_id', $contacts_ids, 'IN')->update('contacts', [
                'total_pending_sms' => db()->inc()
            ]);

            /* update device */
            db()->where('device_id', $_POST['device_id'])->update('devices', [
                'total_pending_sms' => db()->inc($contacts_count),
            ]);

            /* Wake device to start sending SMS */
            wake_device_to_send_sms($devices[$_POST['device_id']]->device_fcm_token);
        }

        /* Database query */
        db()->where('user_id', $this->api_user->user_id)->update('users', [
            'text_campaigns_current_month' => db()->inc()
        ]);

        /* Clear the cache */
        cache()->deleteItem('campaigns?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('campaigns_total?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('campaigns_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $campaign_id,
            'device_id' => (int) $_POST['device_id'],
            'sim_subscription_id' => (int) $_POST['sim_subscription_id'],
            'user_id' => (int) $this->api_user->user_id,
            'rss_automation_id' => null,
            'recurring_campaign_id' => null,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => $settings,
            'contacts_ids' => $contacts_ids,
            'sent_contacts_ids' => [],
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
            'status' => $status == 'processing' ? 'sent' : $status,
            'scheduled_datetime' => $_POST['scheduled_datetime'],
            'last_sent_datetime' => null,
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->api_user->user_id)->getOne('campaigns');

        /* We haven't found the resource */
        if(!$campaign) {
            $this->return_404();
        }

        $campaign->settings = json_decode($campaign->settings ?? '');
        $campaign->contacts_ids = implode(',', json_decode($campaign->contacts_ids));

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->api_user->user_id);

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->api_user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'] ?? $campaign->name, 256);
        $_POST['content'] = input_clean($_POST['content'] ?? $campaign->content, 1000);
        $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

        /* Validate device_id exists */
        if(!$_POST['device_id']) {
            $this->response_error('Invalid device_id. Device not found or does not belong to user.', 400);
        }

        /* Get all sim_subscription_id values */
        $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

        /* Check if the provided subscription exists */
        $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
        
        /* Validate sim_subscription_id exists */
        if($_POST['sim_subscription_id'] === null) {
            $this->response_error('Invalid sim_subscription_id. SIM not found in device.', 400);
        }

        /* Segment */
        $segment_type = $_POST['segment'] ?? $campaign->segment;
        if(is_numeric($_POST['segment'])) {

            /* Get settings from custom segments */
            $segment = $segments[$_POST['segment']];

            if(!$segment) {
                $_POST['segment'] = 'all';
            }

            switch($segment->type) {
                case 'bulk':
                case 'custom':

                    $segment_type = 'custom';
                    $_POST['contacts_ids'] = implode(',', $segment->settings->contacts_ids);

                    break;

                case 'filter':

                    $segment_type = 'filter';

                    if(isset($segment->settings->filters_countries)) $_POST['filters_countries'] = $segment->settings->filters_countries ?? [];
                    if(isset($segment->settings->filters_continents)) $_POST['filters_continents'] = $segment->settings->filters_continents ?? [];
                    if(isset($segment->settings->filters_custom_parameters) && count($segment->settings->filters_custom_parameters)) {
                        foreach($segment->settings->filters_custom_parameters as $key => $custom_parameter) {
                            $_POST['filters_custom_parameter_key'][$key] = $custom_parameter->key;
                            $_POST['filters_custom_parameter_condition'][$key] = $custom_parameter->condition;
                            $_POST['filters_custom_parameter_value'][$key] = $custom_parameter->value;
                        }
                    }

                    break;
            }

        } else {
            $_POST['segment'] = in_array($_POST['segment'], ['all', 'bulk', 'custom', 'filter']) ? input_clean($_POST['segment']) : 'all';
            $segment_type = $_POST['segment'];
        }

        /* Scheduling */
        $_POST['is_scheduled'] = (int) (bool) ($_POST['is_scheduled'] ?? $campaign->settings->is_scheduled);
        $_POST['scheduled_datetime'] = $_POST['is_scheduled'] && !empty($_POST['scheduled_datetime']) && Date::validate($_POST['scheduled_datetime'], 'Y-m-d H:i:s') ?
            (new \DateTime($_POST['scheduled_datetime'], new \DateTimeZone($this->api_user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s')
            : $campaign->scheduled_datetime;

        /* Phone numbers */
        $_POST['phone_numbers'] = trim($_POST['phone_numbers'] ?? $campaign->settings->phone_numbers);
        $_POST['phone_numbers'] = preg_split('/[\r\n,]+/', $_POST['phone_numbers']);
        $_POST['phone_numbers'] = array_filter(array_unique($_POST['phone_numbers']));
        $_POST['phone_numbers'] = array_map('get_phone_number', $_POST['phone_numbers']);

        if(empty($_POST['phone_numbers'])) {
            $_POST['phone_numbers'] = [];
        }

        /* Contacts ids */
        $_POST['contacts_ids'] = trim($_POST['contacts_ids'] ?? $campaign->contacts_ids);
        $_POST['contacts_ids'] = array_filter(array_map('intval', explode(',', $_POST['contacts_ids'])));
        $_POST['contacts_ids'] = array_values(array_unique($_POST['contacts_ids']));
        $_POST['contacts_ids'] = $_POST['contacts_ids'] ?: [0];

        $settings = [
            /* Scheduling */
            'is_scheduled' => $_POST['is_scheduled'],
        ];

        /* Get all the users needed */
        switch($segment_type) {
            case 'all':
                $contacts = db()->where('user_id', $this->api_user->user_id)->where('has_opted_out', 0)->get('contacts', null, ['contact_id', 'user_id']);
                break;

            case 'bulk':

                (new \Altum\Models\Contacts())->simple_bulk_insert($_POST['phone_numbers']);

                $contacts = db()->where('user_id', $this->api_user->user_id)->where('phone_number', $_POST['phone_numbers'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);

                $settings['phone_numbers'] = $_POST['phone_numbers'];

                $_POST['segment'] = 'custom';

                break;

            case 'custom':
                $contacts = db()->where('user_id', $this->api_user->user_id)->where('contact_id', $_POST['contacts_ids'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
                break;

            case 'filter':

                $query = db()->where('user_id', $this->api_user->user_id);

                $has_filters = false;

                /* Custom parameters */
                if(!isset($_POST['filters_custom_parameter_key'])) {
                    $_POST['filters_custom_parameter_key'] = [];
                    $_POST['filters_custom_parameter_condition'] = [];
                    $_POST['filters_custom_parameter_value'] = [];
                }

                $custom_parameters = [];

                foreach($_POST['filters_custom_parameter_key'] as $key => $value) {
                    if(empty(trim($value))) continue;
                    if($key >= 50) continue;

                    $custom_parameters[] = [
                        'key' => input_clean($value, 64),
                        'condition' => isset($_POST['filters_custom_parameter_condition'][$key]) && in_array($_POST['filters_custom_parameter_condition'][$key], ['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than']) ? $_POST['filters_custom_parameter_condition'][$key] : 'exact',
                        'value' => input_clean($_POST['filters_custom_parameter_value'][$key], 512)
                    ];
                }

                if(count($custom_parameters)) {
                    $has_filters = true;
                    $settings['filters_custom_parameters'] = $custom_parameters;

                    foreach($custom_parameters as $custom_parameter) {
                        $key = $custom_parameter['key'];
                        $condition = $custom_parameter['condition'];
                        $value = $custom_parameter['value'];

                        /* reference JSON value once; unquote JSON for string ops, cast for numeric ops */
                        $json_value_expression = 'JSON_UNQUOTE(JSON_EXTRACT(`custom_parameters`, \'$."'.$key.'"\'))';
                        $numeric_expression = 'CAST('.$json_value_expression.' AS DECIMAL(65,10))';

                        switch($condition) {
                            case 'exact':
                                $query->where($json_value_expression.' = \''.$value.'\'');
                                break;

                            case 'not_exact':
                                $query->where($json_value_expression.' != \''.$value.'\'');
                                break;

                            case 'contains':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'%\'');
                                break;

                            case 'not_contains':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'%\'');
                                break;

                            case 'starts_with':
                                $query->where($json_value_expression.' LIKE \''.$value.'%\'');
                                break;

                            case 'not_starts_with':
                                $query->where($json_value_expression.' NOT LIKE \''.$value.'%\'');
                                break;

                            case 'ends_with':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'\'');
                                break;

                            case 'not_ends_with':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'\'');
                                break;

                            case 'bigger_than':
                                $query->where($numeric_expression.' > '.(is_numeric($value) ? $value : '0'));
                                break;

                            case 'lower_than':
                                $query->where($numeric_expression.' < '.(is_numeric($value) ? $value : '0'));
                                break;
                        }
                    }
                }

                /* Countries */
                if(isset($_POST['filters_countries'])) {
                    $_POST['filters_countries'] = array_filter($_POST['filters_countries'] ?? [], function($country) {
                        return array_key_exists($country, get_countries_array());
                    });

                    $has_filters = true;
                    $query->where('country_code', $_POST['filters_countries'], 'IN');
                    $settings['filters_countries'] = $_POST['filters_countries'];
                }

                /* Continents */
                if(isset($_POST['filters_continents'])) {
                    $_POST['filters_continents'] = array_filter($_POST['filters_continents'] ?? [], function($country) {
                        return array_key_exists($country, get_continents_array());
                    });

                    $has_filters = true;
                    $query->where('continent_code', $_POST['filters_continents'], 'IN');
                    $settings['filters_continents'] = $_POST['filters_continents'];
                }

                $contacts = $has_filters ? $query->where('has_opted_out', 0)->get('contacts', null, ['contact_id']) : [];

                db()->reset();

                break;
        }

        if($campaign->status != 'sent') {
            /* Get all the contacts ids */
            $contacts_ids = array_column($contacts, 'contact_id');
            $contacts_count = count($contacts_ids);

            /* Free memory */
            unset($contacts);

            $status = $_POST['is_scheduled'] && $_POST['scheduled_datetime'] ? 'scheduled' : 'processing';
            if(isset($_POST['save'])) {
                $status = 'draft';
            }

            if($status != 'draft') {
                /* Check for the plan limit */
                $sent_sms_current_month = db()->where('user_id', $this->api_user->user_id)->getValue('users', '`text_sent_sms_current_month`');
                if($this->api_user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month + count($contacts_ids) >= $this->api_user->plan_settings->sent_sms_per_month_limit) {
                    $this->response_error(l('global.info_message.plan_feature_limit'), 401);
                }

                if(!$contacts_count) {
                    $this->response_error(l('campaigns.error_message.contacts_ids_is_null'), 401);
                }
            }
        }

        if($campaign->status == 'sent') {
            /* Database query */
            db()->where('campaign_id', $campaign->campaign_id)->update('campaigns', [
                'name' => $_POST['name'],
                'last_datetime' => get_date(),
            ]);
        }

        else {
            /* Database query */
            db()->where('campaign_id', $campaign->campaign_id)->update('campaigns', [
                'device_id' => $_POST['device_id'],
                'sim_subscription_id' => $_POST['sim_subscription_id'],
                'name' => $_POST['name'],
                'content' => $_POST['content'],
                'segment' => $_POST['segment'],
                'settings' => json_encode($settings),
                'contacts_ids' => json_encode($contacts_ids),
                'sent_contacts_ids' => $status == 'processing' ? json_encode($contacts_ids) : '[]',
                'status' => $status == 'processing' ? 'sent' : $status,
                'total_pending_sms' => $status == 'processing' ? $contacts_count : 0,
                'scheduled_datetime' => $_POST['scheduled_datetime'],
                'last_datetime' => get_date(),
            ]);

            /* Insert all planned SMS messages now, otherwise leave the cronjob to do its job */
            if($status == 'processing') {
                $insert_counter = 0;
                $notifications = json_encode($devices[$_POST['device_id']]->sms_status_notifications);
                $sms = [];

                foreach($contacts_ids as $contact_id) {
                    $sms[] = [
                        'contact_id' => $contact_id,
                        'campaign_id' => $campaign_id,
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
                    'total_pending_sms' => db()->inc($contacts_count),
                ]);

                /* Wake device to start sending SMS */
                wake_device_to_send_sms($devices[$_POST['device_id']]->device_fcm_token);
            }
        }

        if(!isset($_POST['save'])) {
            /* Update the total website sent campaigns */
            db()->update('websites', [
                'total_sent_campaigns' => db()->inc()
            ]);
        }

        /* Clear the cache */
        cache()->deleteItem('campaigns_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $campaign->campaign_id,
            'device_id' => (int) $_POST['device_id'],
            'sim_subscription_id' => (int) $_POST['sim_subscription_id'],
            'user_id' => (int) $campaign->user_id,
            'rss_automation_id' => null,
            'recurring_campaign_id' => null,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => $settings,
            'contacts_ids' => $contacts_ids,
            'sent_contacts_ids' => [],
            'total_sent_sms' => (int) $campaign->total_sent_sms,
            'total_pending_sms' => (int) $campaign->total_pending_sms,
            'total_failed_sms' => (int) $campaign->total_failed_sms,
            'status' => $status == 'processing' ? 'sent' : $status,
            'scheduled_datetime' => $_POST['scheduled_datetime'],
            'last_sent_datetime' => $campaign->last_sent_datetime,
            'last_datetime' => get_date(),
            'datetime' => $campaign->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->api_user->user_id)->getOne('campaigns');

        /* We haven't found the resource */
        if(!$campaign) {
            $this->return_404();
        }

        /* Delete the resource */
        (new \Altum\Models\Campaign())->delete($campaign_id);

        http_response_code(200);
        die();

    }
}
