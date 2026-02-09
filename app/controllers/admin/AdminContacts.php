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

class AdminContacts extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'has_opted_out', 'continent_code', 'country_code',], ['name', 'phone_number'], ['contact_id', 'name', 'phone_number', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms']));
        $filters->set_default_order_by($this->user->preferences->contacts_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `contacts` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/contacts?' . $filters->get_get() . '&page=%d')));

        /* Get the contacts list for the user */
        $contacts = [];
        $contacts_result = database()->query("
            SELECT
                `contacts`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `contacts`
            LEFT JOIN
                `users` ON `contacts`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('contacts')}
                {$filters->get_sql_order_by('contacts')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $contacts_result->fetch_object()) {
            $row->custom_parameters = json_decode($row->custom_parameters ?? '');
            $contacts[] = $row;
        }

        /* Export handler */
        process_export_json($contacts, ['contact_id', 'user_id', 'name', 'phone_number', 'continent_code', 'country_code', 'custom_parameters', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'has_opted_out', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime']);
        process_export_csv_new($contacts, ['contact_id', 'user_id', 'name', 'phone_number', 'continent_code', 'country_code', 'custom_parameters', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'has_opted_out', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime'], ['custom_parameters']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'contacts' => $contacts,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/contacts/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/contacts');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/contacts');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/contacts');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            session_write_close();

            switch($_POST['type']) {
                case 'delete':

                    foreach($_POST['selected'] as $contact_id) {
                        if($contact = db()->where('contact_id', $contact_id)->getOne('contacts', ['contact_id', 'user_id'])) {

                            /* Database query */
                            db()->where('contact_id', $contact_id)->delete('contacts');

                            /* Clear the cache */
                            cache()->deleteItem('contacts_total?user_id=' . $contact->user_id);
                            cache()->deleteItem('contacts_dashboard?user_id=' . $contact->user_id);

                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/contacts');
    }

    public function delete() {

        $contact_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$contact = db()->where('contact_id', $contact_id)->getOne('contacts', ['contact_id', 'user_id'])) {
            redirect('admin/contacts');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('contact_id', $contact_id)->delete('contacts');

            /* Clear the cache */
            cache()->deleteItem('contacts_total?user_id=' . $contact->user_id);
            cache()->deleteItem('contacts_dashboard?user_id=' . $contact->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

        }

        redirect('admin/contacts');
    }
}
