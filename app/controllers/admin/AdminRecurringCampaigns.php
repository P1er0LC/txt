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

class AdminRecurringCampaigns extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['segment', 'user_id'], ['content'], ['recurring_campaign_id', 'name', 'content', 'last_run_datetime', 'next_run_datetime', 'datetime', 'last_datetime', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'total_campaigns']));
        $filters->set_default_order_by($this->user->preferences->recurring_campaigns_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `recurring_campaigns` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/recurring_campaigns?' . $filters->get_get() . '&page=%d')));

        /* Get the recurring_campaigns list for the user */
        $recurring_campaigns = [];
        $recurring_campaigns_result = database()->query("
            SELECT
                `recurring_campaigns`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`
            FROM
                `recurring_campaigns`
            LEFT JOIN
                `users` ON `recurring_campaigns`.`user_id` = `users`.`user_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('recurring_campaigns')}
                {$filters->get_sql_order_by('recurring_campaigns')}
            
            {$paginator->get_sql_limit()}
        ");
        while($row = $recurring_campaigns_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $recurring_campaigns[] = $row;
        }

        /* Export handler */
        process_export_json($recurring_campaigns, ['recurring_campaign_id', 'user_id', 'name', 'content', 'segment', 'settings', 'is_enabled', 'total_campaigns', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'last_run_datetime', 'next_run_datetime', 'datetime', 'last_datetime',]);
        process_export_csv_new($recurring_campaigns, ['recurring_campaign_id', 'user_id', 'name', 'content', 'segment', 'settings', 'is_enabled', 'total_campaigns', 'total_sent_sms', 'total_pending_sms', 'total_failed_sms', 'last_run_datetime', 'next_run_datetime', 'datetime', 'last_datetime',], ['settings']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'recurring_campaigns' => $recurring_campaigns,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/recurring-campaigns/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/recurring-campaigns');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/recurring-campaigns');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/recurring-campaigns');
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

                    foreach($_POST['selected'] as $recurring_campaign_id) {
                        if($recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->getOne('flows')) {
                            db()->where('recurring_campaign_id', $recurring_campaign_id)->delete('recurring_campaigns');

                            /* Clear the cache */
                            cache()->deleteItem('recurring_campaigns_dashboard?user_id=' . $recurring_campaign->user_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/recurring-campaigns');
    }

    public function delete() {

        $recurring_campaign_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->getOne('recurring_campaigns', ['recurring_campaign_id', 'user_id', 'name'])) {
            redirect('admin/recurring-campaigns');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            db()->where('recurring_campaign_id', $recurring_campaign_id)->delete('recurring_campaigns');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $recurring_campaign->name . '</strong>'));

            /* Clear the cache */
            cache()->deleteItem('recurring_campaigns_dashboard?user_id=' . $recurring_campaign->user_id);

        }

        redirect('admin/recurring-campaigns');
    }
}
