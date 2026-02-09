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

class SegmentCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.segments')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('segments');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `segments` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->segments_limit != -1 && $total_rows >= $this->user->plan_settings->segments_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('segments');
        }

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['type'] = isset($_POST['type']) && in_array($_POST['type'], ['bulk', 'custom', 'filter']) ? $_POST['type'] : 'filter';

            /* Phone numbers */
            $_POST['phone_numbers'] = trim($_POST['phone_numbers'] ?? '');
            $_POST['phone_numbers'] = preg_split('/[\r\n,]+/', $_POST['phone_numbers']);
            $_POST['phone_numbers'] = array_filter(array_unique($_POST['phone_numbers']));
            $_POST['phone_numbers'] = array_map('get_phone_number', $_POST['phone_numbers']);

            if(empty($_POST['phone_numbers'])) {
                $_POST['phone_numbers'] = [];
            }

            /* Contacts IDs */
            $_POST['contacts_ids'] = trim($_POST['contacts_ids'] ?? '');
            $_POST['contacts_ids'] = array_filter(array_map('intval', explode(',', $_POST['contacts_ids'])));
            $_POST['contacts_ids'] = array_values(array_unique($_POST['contacts_ids']));
            $_POST['contacts_ids'] = $_POST['contacts_ids'] ?: [0];

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
                $settings = [];

                /* Get all the users needed */
                switch($_POST['type']) {

                    case 'bulk':

                        (new \Altum\Models\Contacts())->simple_bulk_insert($_POST['phone_numbers']);

                        $contacts = db()->where('user_id', $this->user->user_id)->where('phone_number', $_POST['phone_numbers'], 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);

                        $settings['phone_numbers'] = $_POST['phone_numbers'];

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

                $contacts_ids = array_column($contacts, 'contact_id');

                /* Free memory */
                unset($contacts);

                $settings['contacts_ids'] = in_array($_POST['type'], ['bulk', 'custom']) ? $contacts_ids : [];

                /* Database query */
                $segment_id = db()->insert('segments', [
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'settings' => json_encode($settings),
                    'total_contacts' => count($contacts_ids),
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('segments?user_id=' . $this->user->user_id);
                cache()->deleteItem('segments_dashboard?user_id=' . $this->user->user_id);

                redirect('segments');
            }

        }

        $values = [
            'name' => $_POST['name'] ?? null,
            'type' => $_POST['type'] ?? 'filter',
            'phone_numbers' => implode("\r\n", $_POST['phone_numbers'] ?? []),
            'contacts_ids' => implode(',', $_POST['contacts_ids'] ?? []),
            'filters_continents' => $_POST['filters_continents'] ?? [],
            'filters_countries' => $_POST['filters_countries'] ?? [],
            'filters_custom_parameters' => $_POST['filters_custom_parameters'] ?? [],
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
        ];

        $view = new \Altum\View('segment-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
