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

class ApiRecurringCampaigns extends Controller {
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
        $filters->set_default_order_by($this->api_user->preferences->recurring_campaigns_default_order_by, $this->api_user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->api_user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `recurring_campaigns` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/recurring-campaigns?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `recurring_campaigns`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->recurring_campaign_id,
                'device_id' => (int) $row->device_id,
                'sim_subscription_id' => (int) $row->sim_subscription_id,
                'user_id' => (int) $row->user_id,
                'name' => $row->name,
                'content' => $row->content,
                'segment' => $row->segment,
                'settings' => json_decode($row->settings),
                'is_enabled' => (bool) $row->is_enabled,
                'total_campaigns' => (int) $row->total_campaigns,
                'total_sent_sms' => (int) $row->total_sent_sms,
                'total_pending_sms' => (int) $row->total_pending_sms,
                'total_failed_sms' => (int) $row->total_failed_sms,
                'last_run_datetime' => $row->last_run_datetime,
                'next_run_datetime' => $row->next_run_datetime,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime
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

        $recurring_campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->where('user_id', $this->api_user->user_id)->getOne('recurring_campaigns');

        /* We haven't found the resource */
        if(!$recurring_campaign) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $recurring_campaign->recurring_campaign_id,
            'device_id' => (int) $recurring_campaign->device_id,
            'sim_subscription_id' => (int) $recurring_campaign->sim_subscription_id,
            'user_id' => (int) $recurring_campaign->user_id,
            'name' => $recurring_campaign->name,
            'content' => $recurring_campaign->content,
            'segment' => $recurring_campaign->segment,
            'settings' => json_decode($recurring_campaign->settings),
            'is_enabled' => (bool) $recurring_campaign->is_enabled,
            'total_campaigns' => (int) $recurring_campaign->total_campaigns,
            'total_sent_sms' => (int) $recurring_campaign->total_sent_sms,
            'total_pending_sms' => (int) $recurring_campaign->total_pending_sms,
            'total_failed_sms' => (int) $recurring_campaign->total_failed_sms,
            'last_run_datetime' => $recurring_campaign->last_run_datetime,
            'next_run_datetime' => $recurring_campaign->next_run_datetime,
            'last_datetime' => $recurring_campaign->last_datetime,
            'datetime' => $recurring_campaign->datetime
        ];

        Response::jsonapi_success($data);

    }

    private function post() {

        /* Check for any errors */
        $required_fields = ['name', 'content', 'device_id', 'sim_subscription_id'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('recurring_campaigns', 'count(*)');
        if($this->api_user->plan_settings->recurring_campaigns_limit != -1 && $total_rows >= $this->api_user->plan_settings->recurring_campaigns_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->api_user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['content'] = input_clean($_POST['content'] ?? null, 1000);
        $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

        if($_POST['device_id']) {
            /* Get all sim_subscription_id values */
            $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

            /* Check if the provided subscription exists */
            $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : null;
        }

        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) (bool) $_POST['is_enabled'] : 1;

        /* Segment */
        $_POST['segment'] = $_POST['segment'] ?? null;
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
        $_POST['time'] = preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $_POST['time'] ?? '12:00') ? $_POST['time'] : '00:00';
        $_POST['week_days'] = array_map('intval', array_filter($_POST['week_days'] ?? [], fn($key) => in_array($key, range(1, 7))));
        $_POST['month_days'] = array_map('intval', array_filter($_POST['month_days'] ?? [], fn($key) => in_array($key, range(1, 31))));

        $settings = [
            /* Recurring settings */
            'frequency' => $_POST['frequency'],
            'time' => $_POST['time'],
            'week_days' => $_POST['week_days'],
            'month_days' => $_POST['month_days'],
        ];

        /* Calculate the next run */
        $next_run_datetime = get_next_run_datetime($_POST['frequency'], $_POST['time'], $_POST['week_days'], $_POST['month_days'], $this->api_user->timezone, '-15 minutes');

        /* Database query */
        $recurring_campaign_id = db()->insert('recurring_campaigns', [
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => json_encode($settings),
            'is_enabled' => $_POST['is_enabled'],
            'next_run_datetime' => $next_run_datetime,
            'datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => $recurring_campaign_id,
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => (int) $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => $settings,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'total_campaigns' => 0,
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
            'last_run_datetime' => null,
            'next_run_datetime' => $next_run_datetime,
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        /* Clear the cache */
        cache()->deleteItem('recurring_campaigns_dashboard?user_id=' . $this->api_user->user_id);

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('recurring_campaigns', 'count(`recurring_campaign_id`)');

        if($this->api_user->plan_settings->recurring_campaigns_limit != -1 && $total_rows > $this->api_user->plan_settings->recurring_campaigns_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->recurring_campaigns_limit, mb_strtolower(l('recurring_campaigns.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $recurring_campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->where('user_id', $this->api_user->user_id)->getOne('recurring_campaigns');

        /* We haven't found the resource */
        if(!$recurring_campaign) {
            $this->return_404();
        }

        $recurring_campaign->settings = json_decode($recurring_campaign->settings ?? '');

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->api_user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'] ?? $recurring_campaign->name, 256);
        $_POST['content'] = input_clean($_POST['content'] ?? $recurring_campaign->content, 1000);
        $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : $recurring_campaign->device_id;

        if($_POST['device_id']) {
            /* Get all sim_subscription_id values */
            $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

            /* Check if the provided subscription exists */
            $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : $recurring_campaign->sim_subscription_id;
        }

        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) (bool) $_POST['is_enabled'] : $recurring_campaign->is_enabled;

        /* Segment */
        $_POST['segment'] = isset($_POST['segment']) ? $_POST['segment'] : $recurring_campaign->segment;
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
        $_POST['frequency'] = isset($_POST['frequency']) && in_array($_POST['frequency'], ['daily', 'weekly', 'monthly']) ? $_POST['frequency'] : $recurring_campaign->settings->frequency;
        $_POST['time'] = isset($_POST['time']) && preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $_POST['time']) ? $_POST['time'] : $recurring_campaign->settings->time;
        $_POST['week_days'] = array_map('intval', array_filter($_POST['week_days'] ?? $recurring_campaign->settings->week_days, fn($key) => in_array($key, range(1, 7))));
        $_POST['month_days'] = array_map('intval', array_filter($_POST['month_days'] ?? $recurring_campaign->settings->month_days, fn($key) => in_array($key, range(1, 31))));

        $settings = [
            /* Recurring settings */
            'frequency' => $_POST['frequency'],
            'time' => $_POST['time'],
            'week_days' => $_POST['week_days'],
            'month_days' => $_POST['month_days'],
        ];

        /* Calculate the next run */
        $next_run_datetime = get_next_run_datetime($_POST['frequency'], $_POST['time'], $_POST['week_days'], $_POST['month_days'], $this->api_user->timezone, '-15 minutes');

        /* Database query */
        db()->where('recurring_campaign_id', $recurring_campaign->recurring_campaign_id)->update('recurring_campaigns', [
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => json_encode($settings),
            'is_enabled' => $_POST['is_enabled'],
            'next_run_datetime' => $next_run_datetime,
            'last_datetime' => get_date(),
        ]);

        /* Prepare the data */
        $data = [
            'id' => $recurring_campaign->recurring_campaign_id,
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => (int) $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => $settings,
            'is_enabled' => (bool) $_POST['is_enabled'],
            'total_campaigns' => (int) $recurring_campaign->total_campaigns,
            'total_sent_sms' => (int) $recurring_campaign->total_sent_sms,
            'total_pending_sms' => (int) $recurring_campaign->total_pending_sms,
            'total_failed_sms' => (int) $recurring_campaign->total_failed_sms,
            'last_run_datetime' => $recurring_campaign->last_run_datetime,
            'next_run_datetime' => $next_run_datetime,
            'last_datetime' => get_date(),
            'datetime' => $recurring_campaign->datetime,
        ];

        /* Clear the cache */
        cache()->deleteItem('recurring_campaigns_dashboard?user_id=' . $this->api_user->user_id);

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $recurring_campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->where('user_id', $this->api_user->user_id)->getOne('recurring_campaigns');

        /* We haven't found the resource */
        if(!$recurring_campaign) {
            $this->return_404();
        }

        /* Delete the resource */
        db()->where('recurring_campaign_id', $recurring_campaign_id)->delete('recurring_campaigns');

        /* Clear the cache */
        cache()->deleteItem('rss_automations_dashboard?user_id=' . $this->api_user->user_id);

        http_response_code(200);
        die();

    }
}
