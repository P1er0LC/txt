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

class ContactsImport extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.contacts')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('contacts');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `contacts` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->contacts_limit != -1 && $total_rows >= $this->user->plan_settings->contacts_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('contacts');
        }

        if(!empty($_POST)) {
            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = [];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!isset($_FILES['file'])) {
                Alerts::add_error(l('global.error_message.empty_field'));
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Uploaded file */
            \Altum\Uploads::validate_upload('contacts_csv', 'file', get_max_upload());

            /* Parse csv */
            $csv_array = array_map(function($csv_line) {
                return str_getcsv($csv_line, ',', '"', '\\');
            }, file($_FILES['file']['tmp_name']));

            if(!$csv_array || !is_array($csv_array)) {
                Alerts::add_error(l('global.error_message.invalid_file_type'));
            }

            $headers_array = $csv_array[0];
            unset($csv_array[0]);
            reset($csv_array);

            /* Detect custom_parameters keys in the CSV headers */
            $custom_parameters_keys = [];
            foreach($headers_array as $header_index => $header_value) {
                if(preg_match('/^custom_parameters\[(.*?)\]$/', $header_value, $matches)) {
                    $custom_parameters_keys[$header_index] = input_clean($matches[1], 64);
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $imported_contacts = 0;

                /* Go over each row */
                foreach($csv_array as $key => $csv_row) {
                    if(count($headers_array) != count($csv_row)) {
                        continue;
                    }

                    /* Required fields */
                    $array_key = array_search('phone_number', $headers_array);
                    if($array_key === false) {
                        continue;
                    }
                    $phone_number = get_phone_number($csv_row[$array_key]);
                    if(!$phone_number) {
                        continue;
                    }

                    /* Name */
                    $array_key = array_search('name', $headers_array);
                    $name = input_clean($csv_row[$array_key] ?? l('global.unknown'), 64);

                    /* Date for insertion */
                    $datetime = get_date();
                    if($array_key = array_search('datetime', $headers_array)) {
                        try {
                            $datetime = (new \DateTime($csv_row[$array_key]))->format('Y-m-d H:i:s');
                        } catch (\Exception $exception) {
                            // :)
                        }
                    }

                    $country_code = null;
                    try {
                        $phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
                        $phone_number_object = $phone_number_util->parse($phone_number, null);
                        $country_code = $phone_number_util->getRegionCodeForNumber($phone_number_object);
                    } catch (\Exception $exception) {
                        /* :) */
                    }

                    $continent_code = get_continent_code_from_country_code($country_code);

                    $custom_parameters_array = [];
                    foreach($custom_parameters_keys as $header_index => $parameter_key) {
                        if(!empty($csv_row[$header_index])) {
                            $custom_parameters_array[$parameter_key] = input_clean($csv_row[$header_index] ?? '', 512);
                        }
                    }

                    /* Insert / update in the database */
                    $contact_id = db()->onDuplicate([
                        'phone_number'
                    ])->insert('contacts', [
                        'user_id' => $this->user->user_id,
                        'phone_number' => $phone_number,
                        'name' => $name,
                        'custom_parameters' => json_encode($custom_parameters_array),
                        'continent_code' => $continent_code,
                        'country_code' => $country_code,
                        'datetime' => $datetime,
                    ]);

                    $imported_contacts++;
                }

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('contacts_import.success_message'), '<strong>' . $imported_contacts . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('contacts_total?user_id=' . $this->user->user_id);
                cache()->deleteItem('contacts_dashboard?user_id=' . $this->user->user_id);

                redirect('contacts');
            }

        }

        $values = [];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('contacts-import/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
