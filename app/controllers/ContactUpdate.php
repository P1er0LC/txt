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

class ContactUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.contacts')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('contacts');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `contacts` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->contacts_limit != -1 && $total_rows > $this->user->plan_settings->contacts_limit) {
            redirect('contacts');
        }

        $contact_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$contact = db()->where('contact_id', $contact_id)->where('user_id', $this->user->user_id)->getOne('contacts')) {
            redirect('contacts');
        }

        $contact->custom_parameters = json_decode($contact->custom_parameters ?? '');

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['phone_number'] = get_phone_number($_POST['phone_number']);

            /* Filter some of the variables */
            if(!isset($_POST['custom_parameter_key'])) {
                $_POST['custom_parameter_key'] = [];
                $_POST['custom_parameter_value'] = [];
            }

            $custom_parameters = [];
            foreach($_POST['custom_parameter_key'] as $key => $value) {
                if(empty(trim($value))) continue;

                $custom_parameter_key = input_clean($value, 64);
                $custom_parameter_value = input_clean($_POST['custom_parameter_value'][$key], 512);

                $custom_parameters[$custom_parameter_key] = $custom_parameter_value;
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['phone_number'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
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
                db()->where('contact_id', $contact->contact_id)->update('contacts', [
                    'phone_number' => $_POST['phone_number'],
                    'name' => $_POST['name'],
                    'continent_code' => $continent_code,
                    'country_code' => $country_code,
                    'custom_parameters' => json_encode($custom_parameters),
                    'last_datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $contact->phone_number . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('contact?contact_id=' . $contact->contact_id);
                cache()->deleteItem('contact?phone_number=' . $contact->phone_number);

                /* Refresh the page */
                redirect('contact-update/' . $contact_id);
            }
        }

        /* Prepare the view */
        $data = [
            'contact' => $contact,
        ];

        $view = new \Altum\View('contact-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
