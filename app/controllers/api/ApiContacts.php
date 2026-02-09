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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiContacts extends Controller {
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
        $filters->set_default_order_by($this->api_user->preferences->contacts_default_order_by, $this->api_user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->api_user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `contacts` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/contacts?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `contacts`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->contact_id,
                'user_id' => (int) $row->user_id,
                'name' => $row->name,
                'phone_number' => $row->phone_number,
                'custom_parameters' => json_decode($row->custom_parameters ?? ''),
                'continent_code' => $row->continent_code,
                'country_code' => $row->country_code,
                'has_opted_out' => (bool) $row->has_opted_out,
                'total_sent_sms' => (int) $row->total_sent_sms,
                'total_pending_sms' => (int) $row->total_pending_sms,
                'total_failed_sms' => (int) $row->total_failed_sms,
                'total_received_sms' => (int) $row->total_received_sms,
                'last_sent_datetime' => $row->last_sent_datetime,
                'last_received_datetime' => $row->last_received_datetime,
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

        $contact_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $contact = db()->where('contact_id', $contact_id)->where('user_id', $this->api_user->user_id)->getOne('contacts');

        /* We haven't found the resource */
        if(!$contact) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $contact->contact_id,
            'user_id' => (int) $contact->user_id,
            'name' => $contact->name,
            'phone_number' => $contact->phone_number,
            'custom_parameters' => json_decode($contact->custom_parameters ?? ''),
            'continent_code' => $contact->continent_code,
            'country_code' => $contact->country_code,
            'has_opted_out' => (bool) $contact->has_opted_out,
            'total_sent_sms' => (int) $contact->total_sent_sms,
            'total_pending_sms' => (int) $contact->total_pending_sms,
            'total_failed_sms' => (int) $contact->total_failed_sms,
            'total_received_sms' => (int) $contact->total_received_sms,
            'last_sent_datetime' => $contact->last_sent_datetime,
            'last_received_datetime' => $contact->last_received_datetime,
            'last_datetime' => $contact->last_datetime,
            'datetime' => $contact->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['phone_number'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('contacts', 'count(*)');
        if($this->api_user->plan_settings->contacts_limit != -1 && $total_rows >= $this->api_user->plan_settings->contacts_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Filter some of the variables */
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

        /* Get contact data */
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
            'name' => $_POST['name'],
            'continent_code' => $continent_code,
            'country_code' => $country_code,
            'custom_parameters' => json_encode($custom_parameters),
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('contacts?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('contacts_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $contact_id,
            'user_id' => (int) $this->api_user->user_id,
            'name' => $_POST['name'],
            'phone_number' => $_POST['phone_number'],
            'custom_parameters' => json_decode($contact->custom_parameters ?? ''),
            'continent_code' => $contact->continent_code,
            'country_code' => $contact->country_code,
            'has_opted_out' => (bool) 0,
            'total_sent_sms' => (int) 0,
            'total_pending_sms' => (int) 0,
            'total_failed_sms' => (int) 0,
            'total_received_sms' => (int) 0,
            'last_sent_datetime' => null,
            'last_received_datetime' => null,
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('contacts', 'count(`contact_id`)');

        if($this->api_user->plan_settings->contacts_limit != -1 && $total_rows > $this->api_user->plan_settings->contacts_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->contacts_limit, mb_strtolower(l('contacts.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $contact_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $contact = db()->where('contact_id', $contact_id)->where('user_id', $this->api_user->user_id)->getOne('contacts');

        /* We haven't found the resource */
        if(!$contact) {
            $this->return_404();
        }
        $contact->custom_parameters = json_decode($contact->custom_parameters ?? '');

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'] ?? $contact->name, 256);
        $_POST['phone_number'] = get_phone_number($_POST['phone_number'] ?? $contact->phone_number);

        $custom_parameters = [];

        /* Filter some of the variables */
        if(!isset($_POST['custom_parameter_key'])) {
            $_POST['custom_parameter_key'] = [];
            $_POST['custom_parameter_value'] = [];
            $custom_parameters = $contact->custom_parameters;
        }

        foreach($_POST['custom_parameter_key'] as $key => $value) {
            if(empty(trim($value))) continue;

            $custom_parameter_key = input_clean($value, 64);
            $custom_parameter_value = input_clean($_POST['custom_parameter_value'][$key], 512);

            $custom_parameters[$custom_parameter_key] = $custom_parameter_value;
        }

        /* Get contact data */
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
            'name' => $_POST['name'],
            'phone_number' => $_POST['phone_number'],
            'continent_code' => $continent_code,
            'country_code' => $country_code,
            'custom_parameters' => json_encode($custom_parameters),
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('contact?contact_id=' . $contact->contact_id);
        cache()->deleteItem('contact?phone_number=' . $contact->phone_number);
        cache()->deleteItem('contacts_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => (int) $contact->contact_id,
            'user_id' => (int) $contact->user_id,
            'name' => $_POST['name'],
            'phone_number' => $_POST['phone_number'],
            'custom_parameters' => json_decode($custom_parameters ?? ''),
            'continent_code' => $continent_code,
            'country_code' => $country_code,
            'has_opted_out' => (bool) $contact->has_opted_out,
            'total_sent_sms' => (int) $contact->total_sent_sms,
            'total_pending_sms' => (int) $contact->total_pending_sms,
            'total_failed_sms' => (int) $contact->total_failed_sms,
            'total_received_sms' => (int) $contact->total_received_sms,
            'last_sent_datetime' => $contact->last_sent_datetime,
            'last_received_datetime' => $contact->last_received_datetime,
            'last_datetime' => get_date(),
            'datetime' => $contact->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $contact_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $contact = db()->where('contact_id', $contact_id)->where('user_id', $this->api_user->user_id)->getOne('contacts');

        /* We haven't found the resource */
        if(!$contact) {
            $this->return_404();
        }

        /* Database query */
        db()->where('contact_id', $contact_id)->delete('contacts');

        /* Clear the cache */
        cache()->deleteItem('contacts_total?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('contacts_dashboard?user_id=' . $this->api_user->user_id);

        http_response_code(200);
        die();

    }
}
