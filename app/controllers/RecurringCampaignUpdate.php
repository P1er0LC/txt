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

class RecurringCampaignUpdate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.recurring_campaign')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('flows');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `recurring_campaigns` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->recurring_campaigns_limit != -1 && $total_rows > $this->user->plan_settings->recurring_campaigns_limit) {
            redirect('recurring-campaigns');
        }

        $recurring_campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->where('user_id', $this->user->user_id)->getOne('recurring_campaigns')) {
            redirect('recurring-campaigns');
        }

        $recurring_campaign->settings = json_decode($recurring_campaign->settings ?? '');

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
            $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            if($_POST['device_id']) {
                /* Get all sim_subscription_id values */
                $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

                /* Check if the provided subscription exists */
                $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
            }

            /* Segment */
            if(is_numeric($_POST['segment'])) {

                /* Get settings from custom segments */
                $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($_POST['segment']);

                if(!$segment) {
                    $_POST['segment'] = 'all';
                }

            } else {
                $_POST['segment'] = in_array($_POST['segment'], ['all']) ? input_clean($_POST['segment']) : 'all';
            }

            /* Recurring settings */
            $_POST['frequency'] = isset($_POST['frequency']) && in_array($_POST['frequency'], ['daily', 'weekly', 'monthly']) ? $_POST['frequency'] : 'monthly';
            $_POST['time'] = preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $_POST['time']) ? $_POST['time'] : '00:00';
            $_POST['week_days'] = array_map('intval', array_filter($_POST['week_days'] ?? [], fn($key) => in_array($key, range(1, 7))));
            $_POST['month_days'] = array_map('intval', array_filter($_POST['month_days'] ?? [], fn($key) => in_array($key, range(1, 31))));

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'content', 'device_id', 'sim_subscription_id'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = [
                    /* Recurring settings */
                    'frequency' => $_POST['frequency'],
                    'time' => $_POST['time'],
                    'week_days' => $_POST['week_days'],
                    'month_days' => $_POST['month_days'],
                ];

                /* Calculate the next run */
                $next_run_datetime = get_next_run_datetime($_POST['frequency'], $_POST['time'], $_POST['week_days'], $_POST['month_days'], $this->user->timezone, '-15 minutes');

                /* Database query */
                db()->where('recurring_campaign_id', $recurring_campaign->recurring_campaign_id)->update('recurring_campaigns', [
                    'device_id' => $_POST['device_id'],
                    'sim_subscription_id' => $_POST['sim_subscription_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'content' => $_POST['content'],
                    'segment' => $_POST['segment'],
                    'settings' => json_encode($settings),
                    'is_enabled' => $_POST['is_enabled'],
                    'next_run_datetime' => $next_run_datetime,
                    'last_datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('recurring_campaigns_dashboard?user_id=' . $this->user->user_id);

                /* Refresh the page */
                redirect('recurring-campaign-update/' . $recurring_campaign_id);
            }
        }

        /* Prepare the view */
        $data = [
            'recurring_campaign' => $recurring_campaign,
            'segments' => $segments,
            'devices' => $devices,
        ];

        $view = new \Altum\View('recurring-campaign-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
