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

class AdminSms extends Controller {

    public function index() {

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['sms_id', 'user_id', 'device_id', 'contact_id', 'sim_subscription_id', 'campaign_id', 'flow_id', 'rss_automation_id', 'recurring_campaign_id', 'type', 'status',], ['phone_number', 'content'], ['sms_id', 'scheduled_datetime', 'datetime']));
        $filters->set_default_order_by($this->user->preferences->sms_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `sms` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/sms?' . $filters->get_get() . '&page=%d')));

        /* Get the sms list */
        $sms = [];
        $sms_result = database()->query("
            SELECT
                `sms`.*, 
                `users`.`name` AS `user_name`, `users`.`email` AS `user_email`, `users`.`avatar` AS `user_avatar`,
                `contacts`.`phone_number`, `contacts`.`has_opted_out`, `contacts`.`country_code`, `contacts`.`name`
            FROM
                `sms`
            LEFT JOIN
                `users` ON `sms`.`user_id` = `users`.`user_id`
            LEFT JOIN 
                `contacts` ON `contacts`.`contact_id` = `sms`.`contact_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('sms')}
                {$filters->get_sql_order_by('sms')}
            
            {$paginator->get_sql_limit()}
        ");

        /* Store unique users to get cached devices */
        $devices = [];
        $unique_users_ids = [];

        while($row = $sms_result->fetch_object()) {
            if(!in_array($row->user_id, $unique_users_ids)) {
                $unique_users_ids[] = $row->user_id;
                $user_devices = (new \Altum\Models\Devices())->get_devices_by_user_id($row->user_id);
                $devices += $user_devices;
            }

            $sms[] = $row;
        }

        /* Export handler */
        process_export_json($sms, ['sms_id', 'user_id', 'device_id', 'sim_subscription_id', 'contact_id', 'campaign_id', 'flow_id', 'rss_automation_id', 'recurring_campaign_id', 'type', 'status', 'content', 'error', 'notifications', 'scheduled_datetime', 'datetime']);
        csv_exporter_new($sms, ['sms_id', 'user_id', 'device_id', 'sim_subscription_id', 'contact_id', 'campaign_id', 'flow_id', 'rss_automation_id', 'recurring_campaign_id', 'type', 'status', 'content', 'error', 'notifications', 'scheduled_datetime', 'datetime'], ['notifications']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'sms' => $sms,
            'pagination' => $pagination,
            'devices' => $devices,
            'filters' => $filters,
        ];

        $view = new \Altum\View('admin/sms/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('admin/sms');
        }

        if(empty($_POST['selected'])) {
            redirect('admin/sms');
        }

        if(!isset($_POST['type'])) {
            redirect('admin/sms');
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

                    foreach($_POST['selected'] as $sms_id) {
                        if($sms = db()->where('sms_id', $sms_id)->getOne('sms')) {

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
                            cache()->deleteItem('sms_total?user_id=' . $sms->user_id);
                            cache()->deleteItem('sms_dashboard?user_id=' . $sms->user_id);
                        }
                    }

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('admin/sms');
    }

    public function delete() {

        $sms_id = (isset($this->params[0])) ? (int) $this->params[0] : null;

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$sms = db()->where('sms_id', $sms_id)->getOne('sms')) {
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

            /* Clear the cache */
            cache()->deleteItem('sms_total?user_id=' . $sms->user_id);
            cache()->deleteItem('sms_dashboard?user_id=' . $sms->user_id);

            /* Set a nice success message */
            Alerts::add_success(l('global.success_message.delete2'));

        }

        redirect('admin/sms');
    }
}
