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

class RssAutomationCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.rss_automations')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('rss-automations');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `rss_automations` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->rss_automations_limit != -1 && $total_rows >= $this->user->plan_settings->rss_automations_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('rss-automations');
        }

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->user->user_id);

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* RSS automation check intervals */
        $rss_automations_check_intervals = require APP_PATH . 'includes/rss_automations_check_intervals.php';

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
            $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

            $_POST['rss_url'] = get_url($_POST['rss_url'], 512);
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

            /* RSS */
            $_POST['check_interval_seconds'] = array_key_exists($_POST['check_interval_seconds'], $rss_automations_check_intervals) ? (int) $_POST['check_interval_seconds'] : array_key_last($rss_automations_check_intervals);
            $_POST['items_count'] = isset($_POST['items_count']) && in_array($_POST['items_count'], range(1, 100)) ? (int) $_POST['items_count'] : 1;
            $_POST['campaigns_delay'] = isset($_POST['campaigns_delay']) && in_array($_POST['campaigns_delay'], range(5, 1440)) ? (int) $_POST['campaigns_delay'] : 1;
            $_POST['unique_item_identifier'] = in_array($_POST['unique_item_identifier'], ['url', 'publication_date', 'id']) ? $_POST['unique_item_identifier'] : 'url';

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['rss_url', 'name', 'content', 'device_id', 'sim_subscription_id'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $rss_data = rss_feed_parse_url($_POST['rss_url']);

            if(!$rss_data) {
                Alerts::add_error(l('rss_automations.error_message.invalid_rss_url'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                $settings = [
                    /* Rss */
                    'check_interval_seconds' => $_POST['check_interval_seconds'],
                    'items_count' => $_POST['items_count'],
                    'campaigns_delay' => $_POST['campaigns_delay'],
                    'unique_item_identifier' => $_POST['unique_item_identifier'],
                ];

                /* Database query */
                $rss_automation_id = db()->insert('rss_automations', [
                    'device_id' => $_POST['device_id'],
                    'sim_subscription_id' => $_POST['sim_subscription_id'],
                    'user_id' => $this->user->user_id,
                    'rss_url' => $_POST['rss_url'],
                    'name' => $_POST['name'],
                    'content' => $_POST['content'],
                    'segment' => $_POST['segment'],
                    'settings' => json_encode($settings),
                    'is_enabled' => $_POST['is_enabled'],
                    'next_check_datetime' => get_date(),
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('rss_automations_dashboard?user_id=' . $this->user->user_id);

                redirect('rss-automations');
            }

        }

        $values = [
            'rss_url' => $_POST['rss_url'] ?? null,
            'name' => $_POST['name'] ?? null,
            'content' => $_POST['content'] ?? null,
            'device_id' => $_POST['device_id'] ?? array_key_first($devices),
            'sim_subscription_id' => $_POST['sim_subscription_id'] ?? null,
            'segment' => $_POST['segment'] ?? 'all',
            'is_enabled' => $_POST['is_enabled'] ?? true,
            'check_interval_seconds' => $_POST['check_interval_seconds'] ?? array_key_last($rss_automations_check_intervals),
            'items_count' => $_POST['items_count'] ?? 1,
            'campaigns_delay' => $_POST['campaigns_delay'] ?? 15,
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'devices' => $devices,
            'segments' => $segments,
            'rss_automations_check_intervals' => $rss_automations_check_intervals,
        ];

        $view = new \Altum\View('rss-automation-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
