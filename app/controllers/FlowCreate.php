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

class FlowCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.flows')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('flows');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `flows` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;
        if($this->user->plan_settings->flows_limit != -1 && $total_rows >= $this->user->plan_settings->flows_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('flows');
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Get available segments */
        $segments = (new \Altum\Models\Segment())->get_segments_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 256);
            $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
            $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

            $_POST['wait_time'] = (int) $_POST['wait_time'];
            $_POST['wait_time_type'] = isset($_POST['wait_time_type']) && in_array($_POST['wait_time_type'], ['minutes', 'hours', 'days']) ? $_POST['wait_time_type'] : 'days';
            $_POST['is_enabled'] = (int) isset($_POST['is_enabled']);

            if($_POST['device_id']) {
                /* Get all sim_subscription_id values */
                $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

                /* Check if the provided subscription exists */
                $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
            }

            if($_POST['wait_time'] < 1) $_POST['wait_time'] = 1;

            /* Max is 90 days of ahead scheduling */
            switch ($_POST['wait_time_type']) {
                case 'minutes':
                    if($_POST['wait_time'] > 129600) $_POST['wait_time'] = 129600;
                    break;

                case 'hours':
                    if($_POST['wait_time'] > 2160) $_POST['wait_time'] = 2160;
                    break;

                case 'days':
                    if($_POST['wait_time'] > 90) $_POST['wait_time'] = 90;
                    break;
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
                $settings = [];

                /* Database query */
                $flow_id = db()->insert('flows', [
                    'device_id' => $_POST['device_id'],
                    'sim_subscription_id' => $_POST['sim_subscription_id'],
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'content' => $_POST['content'],
                    'segment' => $_POST['segment'],
                    'settings' => json_encode($settings),
                    'wait_time' => $_POST['wait_time'],
                    'wait_time_type' => $_POST['wait_time_type'],
                    'is_enabled' => $_POST['is_enabled'],
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('flows?user_id=' . $this->user->user_id);
                cache()->deleteItem('flows_dashboard?user_id=' . $this->user->user_id);

                redirect('flows');
            }

        }

        $values = [
            'name' => $_POST['name'] ?? null,
            'wait_time' => $_POST['wait_time'] ?? 1,
            'wait_time_type' => $_POST['wait_time'] ?? 'days',
            'content' => $_POST['content'] ?? null,
            'device_id' => $_POST['device_id'] ?? array_key_first($devices),
            'sim_subscription_id' => $_POST['sim_subscription_id'] ?? null,
            'is_enabled' => $_POST['is_enabled'] ?? true,
            'segment' => $_POST['segment'] ?? 'all',
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'devices' => $devices,
            'segments' => $segments,
        ];

        $view = new \Altum\View('flow-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
