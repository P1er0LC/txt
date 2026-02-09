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

class Sms extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['sms_id', 'user_id', 'device_id', 'contact_id', 'sim_subscription_id', 'campaign_id', 'flow_id', 'rss_automation_id', 'recurring_campaign_id', 'type', 'status',], ['phone_number', 'content'], ['sms_id', 'scheduled_datetime', 'datetime']));
        $filters->set_default_order_by($this->user->preferences->sms_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `sms` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('sms?' . $filters->get_get() . '&page=%d')));

        /* Generate stats */
        $sms_stats = [
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
            'total_received_sms' => 0,
        ];

        /* Get the sms list for the user */
        $sms = [];
        $sms_result = database()->query("
            SELECT `sms`.*, `contacts`.`phone_number`, `contacts`.`has_opted_out`, `contacts`.`country_code`, `contacts`.`name`
            FROM `sms` 
            LEFT JOIN `contacts` ON `contacts`.`contact_id` = `sms`.`contact_id`
            WHERE 
                `sms`.`user_id` = {$this->user->user_id} 
                {$filters->get_sql_where('sms')} 
                {$filters->get_sql_order_by('sms')} 
                {$paginator->get_sql_limit()}
        ");

        while($row = $sms_result->fetch_object()) {
            $sms_stats['total_sent_sms'] += $row->type == 'sent' && $row->status == 'sent' ? 1 : 0;
            $sms_stats['total_pending_sms'] += $row->status == 'pending' ? 1 : 0;
            $sms_stats['total_failed_sms'] += $row->status == 'failed' ? 1 : 0;
            $sms_stats['total_received_sms'] += $row->type == 'received' ? 1 : 0;

            $sms[] = $row;
        }

        /* Export handler */
        process_export_json($sms, ['sms_id', 'user_id', 'device_id', 'sim_subscription_id', 'contact_id', 'campaign_id', 'flow_id', 'rss_automation_id', 'recurring_campaign_id', 'type', 'status', 'content', 'error', 'notifications', 'scheduled_datetime', 'datetime']);
        process_export_csv($sms, ['sms_id', 'user_id', 'device_id', 'sim_subscription_id', 'contact_id', 'campaign_id', 'flow_id', 'rss_automation_id', 'recurring_campaign_id', 'type', 'status', 'content', 'error', 'notifications', 'scheduled_datetime', 'datetime']);

        /* Get statistics */
        if(count($sms) && !$filters->has_applied_filters) {
            $start_date_query = (new \DateTime())->modify('-' . (settings()->main->chart_days ?? 30) . ' day')->format('Y-m-d');
            $end_date_query = (new \DateTime())->modify('+1 day')->format('Y-m-d');

            $convert_tz_sql = get_convert_tz_sql('`datetime`', $this->user->timezone);

             $sms_result_query = "
                SELECT
                    `status`,
                    COUNT(*) AS `total`,
                    DATE_FORMAT({$convert_tz_sql}, '%Y-%m-%d') AS `formatted_date`
                FROM
                    `sms`
                WHERE   
                    `user_id` = {$this->user->user_id} 
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`,
                    `status`
                ORDER BY
                    `formatted_date`
            ";

            $sms_chart = \Altum\Cache::cache_function_result('sms_chart?user_id=' . $this->user->user_id, null, function() use ($sms_result_query) {
                $sms_chart= [];

                $sms_result = database()->query($sms_result_query);

                /* Generate the raw chart data and save logs for later usage */
                while($row = $sms_result->fetch_object()) {
                    $label = \Altum\Date::get($row->formatted_date, 5, \Altum\Date::$default_timezone);

                    $sms_chart[$label] = isset($sms_chart[$label]) ?
                        array_merge($sms_chart[$label], [
                            $row->status => $row->total,
                        ]) :
                        array_merge([
                            'sent' => 0,
                            'pending' => 0,
                            'failed' => 0,
                            'received' => 0,
                        ], [
                            $row->status => $row->total,
                        ]);
                }

                return $sms_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $sms_chart = get_chart_data($sms_chart);
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Available */
        $sent_sms_current_month = db()->where('user_id', $this->user->user_id)->getValue('users', '`text_sent_sms_current_month`');

        /* Prepare the view */
        $data = [
            'sms' => $sms,
            'sms_chart' => $sms_chart ?? null,
            'total_sms' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'sms_stats' => $sms_stats,
            'devices' => $devices,
            'sent_sms_current_month' => $sent_sms_current_month,
        ];

        $view = new \Altum\View('sms/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('sms');
        }

        if(empty($_POST['selected'])) {
            redirect('sms');
        }

        if(!isset($_POST['type'])) {
            redirect('sms');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.sms')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('sms');
                    }

                    foreach($_POST['selected'] as $sms_id) {
                        if($sms = db()->where('sms_id', $sms_id)->where('user_id', $this->user->user_id)->getOne('sms')) {

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
                        }

                        /* Clear the cache */
                        cache()->deleteItem('sms_total?user_id=' . $this->user->user_id);
                        cache()->deleteItem('sms_dashboard?user_id=' . $this->user->user_id);
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('sms');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.sms')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('sms');
        }

        if(empty($_POST)) {
            redirect('sms');
        }

        $sms_id = (int) query_clean($_POST['sms_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$sms = db()->where('sms_id', $sms_id)->where('user_id', $this->user->user_id)->getOne('sms')) {
            redirect('sms');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

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
            cache()->deleteItem('sms_total?user_id=' . $this->user->user_id);
            cache()->deleteItem('sms_dashboard?user_id=' . $this->user->user_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $sms->phone_number . '</strong>'));

            redirect('sms');
        }

        redirect('sms');
    }
}
