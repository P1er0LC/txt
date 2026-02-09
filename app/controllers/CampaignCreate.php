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

class CampaignCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.campaigns')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('campaigns');
        }

        /* Check for the plan limit */
        $campaigns_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`text_campaigns_current_month`');
        if($this->user->plan_settings->campaigns_per_month_limit != -1 && $campaigns_current_month >= $this->user->plan_settings->campaigns_per_month_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('campaigns');
        }

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->user->user_id);

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
            $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

            if($_POST['device_id']) {
                /* Get all sim_subscription_id values */
                $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

                /* Check if the provided subscription exists */
                $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
            }

            /* Segment */
            $segment_type = null;
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
                (new \DateTime($_POST['scheduled_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s')
                : get_date();

            /* Phone numbers */
            $_POST['phone_numbers'] = trim($_POST['phone_numbers'] ?? '');
            $_POST['phone_numbers'] = preg_split('/[\r\n,]+/', $_POST['phone_numbers']);
            $_POST['phone_numbers'] = array_filter(array_unique($_POST['phone_numbers']));
            $_POST['phone_numbers'] = array_map('get_phone_number', $_POST['phone_numbers']);

            if(empty($_POST['phone_numbers'])) {
                $_POST['phone_numbers'] = [];
            }

            /* Contacts ids */
            $_POST['contacts_ids'] = trim($_POST['contacts_ids'] ?? '');
            $_POST['contacts_ids'] = array_filter(array_map('intval', explode(',', $_POST['contacts_ids'])));
            $_POST['contacts_ids'] = array_values(array_unique($_POST['contacts_ids']));
            $_POST['contacts_ids'] = $_POST['contacts_ids'] ?: [0];

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'content', 'device_id', 'sim_subscription_id'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $settings = [
                /* Scheduling */
                'is_scheduled' => $_POST['is_scheduled'],
            ];

            /* Get all the users needed */
            switch($segment_type) {
                case 'all':
                    $contacts = db()->where('user_id', $this->user->user_id)->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
                    break;

                case 'bulk':

                    (new \Altum\Models\Contacts())->simple_bulk_insert($_POST['phone_numbers']);

                    $contacts = db()->where('user_id', $this->user->user_id)->where('phone_number', $_POST['phone_numbers'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);

                    $settings['phone_numbers'] = $_POST['phone_numbers'];

                    $_POST['segment'] = 'custom';

                    break;

                case 'custom':
                    $contacts = db()->where('user_id', $this->user->user_id)->where('contact_id', $_POST['contacts_ids'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
                    break;

                case 'filter':

                    $query = db()->where('user_id', $this->user->user_id);

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
                $sent_sms_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`text_sent_sms_current_month`');
                if($this->user->plan_settings->sent_sms_per_month_limit != -1 && $sent_sms_current_month + $contacts_count > $this->user->plan_settings->sent_sms_per_month_limit) {
                    Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                }

                if(!$contacts_count) {
                    Alerts::add_error(l('campaigns.error_message.contacts_ids_is_null'));
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                $campaign_id = db()->insert('campaigns', [
                    'device_id' => $_POST['device_id'],
                    'sim_subscription_id' => $_POST['sim_subscription_id'],
                    'user_id' => $this->user->user_id,
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

                /* Database query */
                db()->where('user_id', $this->user->user_id)->update('users', [
                    'text_campaigns_current_month' => db()->inc()
                ]);

                if(isset($_POST['save'])) {
                    /* Set a nice success message */
                    Alerts::add_success(sprintf(l('campaigns.success_message.save'), '<strong>' . $_POST['name'] . '</strong>'));
                } else {
                    /* Set a nice success message */
                    if($_POST['is_scheduled']) {
                        Alerts::add_success(sprintf(l('campaigns.success_message.scheduled'), '<strong>' . $_POST['name'] . '</strong>', '<strong>' . \Altum\Date::get_time_until($_POST['scheduled_datetime']) . '</strong>'));
                    } else {
                        Alerts::add_success(sprintf(l('campaigns.success_message.send'), '<strong>' . $_POST['name'] . '</strong>'));
                    }
                }

                /* Clear the cache */
                cache()->deleteItem('campaigns?user_id=' . $this->user->user_id);
                cache()->deleteItem('campaigns_total?user_id=' . $this->user->user_id);
                cache()->deleteItem('campaigns_dashboard?user_id=' . $this->user->user_id);

                redirect('campaigns');
            }

        }

        $values = [
            'name' => $_POST['name'] ?? generate_prefilled_dynamic_names(l('campaigns.campaign')),
            'content' => $_POST['content'] ?? null,
            'device_id' => $_POST['device_id'] ?? $_GET['device_id'] ?? array_key_first($devices),
            'sim_subscription_id' => $_POST['sim_subscription_id'] ?? null,
            'is_scheduled' => $_POST['is_scheduled'] ?? null,
            'scheduled_datetime' => $_POST['scheduled_datetime'] ?? '',
            'segment' => $_POST['segment'] ?? 'all',
            'phone_numbers' => implode("\r\n", $_POST['phone_numbers'] ?? []),
            'contacts_ids' => implode(',', $_POST['contacts_ids'] ?? []),
            'filters_continents' => $_POST['filters_continents'] ?? [],
            'filters_countries' => $_POST['filters_countries'] ?? [],
            'filters_custom_parameters' => $_POST['filters_custom_parameters'] ?? [],
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'segments' => $segments,
            'devices' => $devices,
        ];

        $view = new \Altum\View('campaign-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
