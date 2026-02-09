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
use Altum\Models\RssAutomation;

defined('ALTUMCODE') || die();

class RssAutomations extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['segment', 'user_id'], ['content', 'rss_url'], ['rss_automation_id', 'name', 'content', 'last_check_datetime', 'next_check_datetime', 'datetime', 'last_datetime', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_campaigns']));
        $filters->set_default_order_by($this->user->preferences->rss_automations_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `rss_automations` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('rss-automations?' . $filters->get_get() . '&page=%d')));

        /* Generate stats */
        $sms_stats = [
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
        ];

        /* Get the rss_automations list for the user */
        $rss_automations = [];
        $rss_automations_result = database()->query("SELECT * FROM `rss_automations` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $rss_automations_result->fetch_object()) {
            $sms_stats['total_sent_sms'] += $row->total_sent_sms;
            $sms_stats['total_pending_sms'] += $row->total_pending_sms;
            $sms_stats['total_failed_sms'] += $row->total_failed_sms;

            $row->settings = json_decode($row->settings ?? '');
            $rss_automations[] = $row;
        }

        /* Export handler */
        process_export_json($rss_automations, ['rss_automation_id', 'device_id', 'sim_subscription_id', 'user_id', 'name', 'rss_url', 'content', 'segment', 'settings', 'is_enabled', 'total_campaigns', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'last_check_datetime', 'next_check_datetime', 'datetime', 'last_datetime',]);
        process_export_csv_new($rss_automations, ['rss_automation_id', 'device_id', 'sim_subscription_id', 'user_id', 'name', 'rss_url', 'content', 'segment', 'settings', 'is_enabled', 'total_campaigns', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'last_check_datetime', 'next_check_datetime', 'datetime', 'last_datetime',], ['settings']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Get statistics */
        if(count($rss_automations) && !$filters->has_applied_filters) {
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
                    AND `rss_automation_id` IS NOT NULL
                    AND ({$convert_tz_sql} BETWEEN '{$start_date_query}' AND '{$end_date_query}')
                GROUP BY
                    `formatted_date`,
                    `status`
                ORDER BY
                    `formatted_date`
            ";

            $sms_chart = \Altum\Cache::cache_function_result('rss_automations_chart?user_id=' . $this->user->user_id, null, function() use ($sms_result_query) {
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
                        ], [
                            $row->status => $row->total,
                        ]);
                }

                return $sms_chart;
            }, 60 * 60 * settings()->main->chart_cache ?? 12);

            $sms_chart = get_chart_data($sms_chart);
        }

        /* Prepare the view */
        $data = [
            'sms_chart' => $sms_chart ?? null,
            'rss_automations' => $rss_automations,
            'total_rss_automations' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
            'sms_stats' => $sms_stats,
        ];

        $view = new \Altum\View('rss-automations/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function duplicate() {
        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.rss_automations')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('rss-automations');
        }

        if(empty($_POST)) {
            redirect('rss-automations');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `rss_automations` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->rss_automations_limit != -1 && $total_rows >= $this->user->plan_settings->rss_automations_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('rss-automations');
        }

        $rss_automation_id = (int) $_POST['rss_automation_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');
        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('rss-automations');
        }

        /* Verify the main resource */
        if(!$rss_automation = db()->where('rss_automation_id', $rss_automation_id)->where('user_id', $this->user->user_id)->getOne('rss_automations')) {
            redirect('rss-automations');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Insert to database */
            $rss_automation_id = db()->insert('rss_automations', [
                'user_id' => $this->user->user_id,
                'device_id' => $rss_automation->device_id,
                'sim_subscription_id' => $rss_automation->sim_subscription_id,
                'name' => string_truncate($rss_automation->name . ' - ' . l('global.duplicated'), 64, null),
                'rss_url' => $rss_automation->rss_url,
                'content' => $rss_automation->content,
                'segment' => $rss_automation->segment,
                'settings' => $rss_automation->settings,
                'rss_last_entries' => '[]',
                'is_enabled' => 0,
                'datetime' => get_date(),
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . input_clean($rss_automation->name) . '</strong>'));

            /* Redirect */
            redirect('rss_automation-update/' . $rss_automation_id);

        }

        redirect('rss-automations');
    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('rss-automations');
        }

        if(empty($_POST['selected'])) {
            redirect('rss-automations');
        }

        if(!isset($_POST['type'])) {
            redirect('rss-automations');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.rss_automations')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('rss-automations');
                    }

                    foreach($_POST['selected'] as $rss_automation_id) {
                        db()->where('rss_automation_id', $rss_automation_id)->where('user_id', $this->user->user_id)->delete('rss_automations');
                    }

                    /* Clear the cache */
                    cache()->deleteItem('rss_automations_dashboard?user_id=' . $this->user->user_id);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('rss-automations');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.rss_automations')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('rss-automations');
        }

        if(empty($_POST)) {
            redirect('rss-automations');
        }

        $rss_automation_id = (int) query_clean($_POST['rss_automation_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$rss_automation = db()->where('rss_automation_id', $rss_automation_id)->where('user_id', $this->user->user_id)->getOne('rss_automations', ['rss_automation_id', 'name'])) {
            redirect('rss-automations');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            db()->where('rss_automation_id', $rss_automation_id)->where('user_id', $this->user->user_id)->delete('rss_automations');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $rss_automation->name . '</strong>'));

            /* Clear the cache */
            cache()->deleteItem('rss_automations_dashboard?user_id=' . $this->user->user_id);

            redirect('rss-automations');
        }

        redirect('rss-automations');
    }
}
