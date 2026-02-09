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

class Contacts extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'has_opted_out', 'continent_code', 'country_code',], ['name', 'phone_number'], ['contact_id', 'name', 'phone_number', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms']));
        $filters->set_default_order_by($this->user->preferences->contacts_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `contacts` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('contacts?' . $filters->get_get() . '&page=%d')));

        /* Generate stats */
        $contacts_stats = [
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
            'total_received_sms' => 0,
        ];

        /* Get the contacts list for the user */
        $contacts = [];
        $contacts_result = database()->query("SELECT * FROM `contacts` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $contacts_result->fetch_object()) {
            $contacts_stats['total_sent_sms'] += $row->total_sent_sms;
            $contacts_stats['total_pending_sms'] += $row->total_pending_sms;
            $contacts_stats['total_failed_sms'] += $row->total_failed_sms;
            $contacts_stats['total_received_sms'] += $row->total_received_sms;

            $row->custom_parameters = json_decode($row->custom_parameters ?? '');
            $contacts[] = $row;
        }

        /* Export handler */
        process_export_json($contacts, ['contact_id', 'user_id', 'name', 'phone_number', 'continent_code', 'country_code', 'custom_parameters', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'has_opted_out', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime']);
        process_export_csv_new($contacts, ['contact_id', 'user_id', 'name', 'phone_number', 'continent_code', 'country_code', 'custom_parameters', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_received_sms', 'has_opted_out', 'last_sent_datetime', 'last_received_datetime', 'datetime', 'last_datetime'], ['custom_parameters']);

        /* Get statistics */
        if(count($contacts) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

            $contacts_result_query = "
                SELECT
                    COUNT(*) AS `total`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `contacts`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`
                ORDER BY
                    `formatted_date`
            ";

            $contacts_chart = \Altum\Cache::cache_function_result('contacts_chart?user_id=' . $this->user->user_id, null, function() use ($contacts_result_query) {
                $contacts_chart= [];

                $contacts_result = database()->query($contacts_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $contacts_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);
                    $contacts_chart[$label]['total'] = $row->total;
                }

                return $contacts_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $contacts_chart = get_chart_data($contacts_chart);
        }

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'contacts' => $contacts,
            'contacts_chart' => $contacts_chart ?? null,
            'total_contacts' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'contacts_stats' => $contacts_stats,
        ];

        $view = new \Altum\View('contacts/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('contacts');
        }

        if(empty($_POST['selected'])) {
            redirect('contacts');
        }

        if(!isset($_POST['type'])) {
            redirect('contacts');
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

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.contacts')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('contacts');
                    }

                    foreach($_POST['selected'] as $contact_id) {
                        if($contact = db()->where('contact_id', $contact_id)->where('user_id', $this->user->user_id)->getOne('contacts', ['contact_id'])) {

                            /* Database query */
                            db()->where('contact_id', $contact_id)->delete('contacts');

                        }
                    }

                    /* Clear the cache */
                    cache()->deleteItem('contacts_total?user_id=' . $this->user->user_id);
                    cache()->deleteItem('contacts_dashboard?user_id=' . $this->user->user_id);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('contacts');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.contacts')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('contacts');
        }

        if(empty($_POST)) {
            redirect('contacts');
        }

        $contact_id = (int) query_clean($_POST['contact_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$contact = db()->where('contact_id', $contact_id)->where('user_id', $this->user->user_id)->getOne('contacts', ['contact_id', 'phone_number'])) {
            redirect('contacts');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('contact_id', $contact_id)->delete('contacts');

            /* Clear the cache */
            cache()->deleteItem('contacts_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('contacts_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $contact->phone_number . '</strong>'));

            redirect('contacts');
        }

        redirect('contacts');
    }
}
