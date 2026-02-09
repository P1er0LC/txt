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

class ApiFlows extends Controller {
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
        $filters->set_default_order_by($this->api_user->preferences->flows_default_order_by, $this->api_user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->api_user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `flows` WHERE `user_id` = {$this->api_user->user_id}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/flows?' . $filters->get_get() . '&page=%d')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `flows`
            WHERE
                `user_id` = {$this->api_user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");


        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                /* main */
                'id' => (int) $row->device_id ,
                'user_id' => (int) $row->user_id,
                'name' => $row->name,
                'segment' => $row->segment,
                'wait_time' => (int) $row->wait_time,
                'wait_time_type' => $row->wait_time_type,
                'is_enabled' => (bool) $row->is_enabled,

                /* json */
                'settings' => json_decode($row->settings ?? ''),

                /* counters */
                'total_sent_sms' => (int) $row->total_sent_sms,
                'total_pending_sms' => (int) $row->total_pending_sms,
                'total_failed_sms' => (int) $row->total_failed_sms,

                /* timestamps */
                'last_sent_datetime' => $row->last_sent_datetime,
                'last_datetime' => $row->last_datetime,
                'datetime' => $row->datetime,
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

        $flow_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $flow = db()->where('flow_id', $flow_id)->where('user_id', $this->api_user->user_id)->getOne('flows');

        /* We haven't found the resource */
        if(!$flow) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            /* main */
            'id' => (int) $flow->device_id ,
            'user_id' => (int) $flow->user_id,
            'name' => $flow->name,
            'segment' => $flow->segment,
            'wait_time' => (int) $flow->wait_time,
            'wait_time_type' => $flow->wait_time_type,
            'is_enabled' => (bool) $flow->is_enabled,

            /* json */
            'settings' => json_decode($flow->settings ?? ''),

            /* counters */
            'total_sent_sms' => (int) $flow->total_sent_sms,
            'total_pending_sms' => (int) $flow->total_pending_sms,
            'total_failed_sms' => (int) $flow->total_failed_sms,

            /* timestamps */
            'last_sent_datetime' => $flow->last_sent_datetime,
            'last_datetime' => $flow->last_datetime,
            'datetime' => $flow->datetime,
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
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('flows', 'count(*)');
        if($this->api_user->plan_settings->flows_limit != -1 && $total_rows >= $this->api_user->plan_settings->flows_limit) {
            $this->response_error(l('global.info_message.plan_feature_limit'), 401);
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'], 256);
        $_POST['content'] = normalize_sms_text(input_clean($_POST['content'], 1000));
        $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : null;

        $_POST['wait_time'] = (int) $_POST['wait_time'] ?? 1;
        $_POST['wait_time_type'] = isset($_POST['wait_time_type']) && in_array($_POST['wait_time_type'], ['minutes', 'hours', 'days']) ? $_POST['wait_time_type'] : 'days';
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) (bool) $_POST['is_enabled'] : 1;

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

        $settings = [];

        /* Database query */
        $flow_id = db()->insert('flows', [
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => json_encode($settings),
            'wait_time' => $_POST['wait_time'],
            'wait_time_type' => $_POST['wait_time_type'],
            'is_enabled' => (bool) $_POST['is_enabled'],
            'datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('flows?user_id=' . $this->api_user->user_id);
        cache()->deleteItem('flows_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $flow_id,
            'device_id' => (int) $_POST['device_id'],
            'sim_subscription_id' => (int) $_POST['sim_subscription_id'],
            'user_id' => (int) $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => $settings,
            'wait_time' => $_POST['wait_time'],
            'wait_time_type' => $_POST['wait_time_type'],
            'is_enabled' => (bool) $_POST['is_enabled'],
            'total_sent_sms' => 0,
            'total_pending_sms' => 0,
            'total_failed_sms' => 0,
            'last_sent_datetime' => null,
            'last_datetime' => null,
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data, null, 201);

    }

    private function patch() {

        /* Check for the plan limit */
        $total_rows = db()->where('user_id', $this->api_user->user_id)->getValue('flows', 'count(`flow_id`)');

        if($this->api_user->plan_settings->flows_limit != -1 && $total_rows > $this->api_user->plan_settings->flows_limit) {
            $this->response_error(sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), $total_rows - $this->user->plan_settings->flows_limit, mb_strtolower(l('flows.title')), l('global.info_message.plan_upgrade')), 401);
        }

        $flow_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $flow = db()->where('flow_id', $flow_id)->where('user_id', $this->api_user->user_id)->getOne('flows');

        /* We haven't found the resource */
        if(!$flow) {
            $this->return_404();
        }

        $flow->settings = json_decode($flow->settings ?? '');

        /* Check for any errors */
        $required_fields = [];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Filter some of the variables */
        $_POST['name'] = input_clean($_POST['name'] ?? $flow->name, 256);
        $_POST['content'] = input_clean($_POST['content'] ?? $flow->content, 1000);
        $_POST['device_id'] = isset($_POST['device_id']) && array_key_exists($_POST['device_id'], $devices) ? (int) $_POST['device_id'] : $flow->device_id;

        $_POST['wait_time'] = isset($_POST['wait_time']) ? (int) $_POST['wait_time'] : $flow->wait_time;
        $_POST['wait_time_type'] = isset($_POST['wait_time_type']) && in_array($_POST['wait_time_type'], ['minutes', 'hours', 'days']) ? $_POST['wait_time_type'] : $flow->wait_time_type;
        $_POST['is_enabled'] = isset($_POST['is_enabled']) ? (int) (bool) $_POST['is_enabled'] : $flow->is_enabled;

        if($_POST['device_id']) {
            /* Get all sim_subscription_id values */
            $sim_subscription_id_array = array_column($devices[$_POST['device_id']]->sims, 'subscription_id');

            /* Check if the provided subscription exists */
            $_POST['sim_subscription_id'] = isset($_POST['sim_subscription_id']) && in_array($_POST['sim_subscription_id'], $sim_subscription_id_array) ? input_clean($_POST['sim_subscription_id'], 20) : $flow->sim_subscription_id;
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
        $_POST['segment'] = isset($_POST['segment']) ? $_POST['segment'] : $flow->segment;
        if(is_numeric($_POST['segment'])) {

            /* Get settings from custom segments */
            $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($_POST['segment']);

            if(!$segment) {
                $_POST['segment'] = 'all';
            }

        } else {
            $_POST['segment'] = in_array($_POST['segment'], ['all']) ? input_clean($_POST['segment']) : 'all';
        }

        $settings = [];

        /* Database query */
        db()->where('flow_id', $flow->flow_id)->update('flows', [
            'device_id' => $_POST['device_id'],
            'sim_subscription_id' => $_POST['sim_subscription_id'],
            'user_id' => $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => json_encode($settings),
            'wait_time' => $_POST['wait_time'],
            'wait_time_type' => $_POST['wait_time_type'],
            'is_enabled' => $_POST['is_enabled'],
            'last_datetime' => get_date(),
        ]);

        /* Clear the cache */
        cache()->deleteItem('flow?flow_id=' . $flow->flow_id);
        cache()->deleteItem('flows?user_id=' . $flow->user_id);
        cache()->deleteItem('flows_dashboard?user_id=' . $this->api_user->user_id);

        /* Prepare the data */
        $data = [
            'id' => $flow->flow_id,
            'device_id' => (int) $_POST['device_id'],
            'sim_subscription_id' => (int) $_POST['sim_subscription_id'],
            'user_id' => (int) $this->api_user->user_id,
            'name' => $_POST['name'],
            'content' => $_POST['content'],
            'segment' => $_POST['segment'],
            'settings' => $settings,
            'wait_time' => $_POST['wait_time'],
            'wait_time_type' => $_POST['wait_time_type'],
            'is_enabled' => (bool) $_POST['is_enabled'],
            'total_sent_sms' => (int) $flow->total_sent_sms,
            'total_pending_sms' => (int) $flow->total_pending_sms,
            'total_failed_sms' => (int) $flow->total_failed_sms,
            'last_sent_datetime' => $flow->last_sent_datetime,
            'last_datetime' => get_date(),
            'datetime' => $flow->datetime,
        ];

        Response::jsonapi_success($data, null, 200);

    }

    private function delete() {

        $flow_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $flow = db()->where('flow_id', $flow_id)->where('user_id', $this->api_user->user_id)->getOne('flows');

        /* We haven't found the resource */
        if(!$flow) {
            $this->return_404();
        }

        /* Delete the resource */
        (new \Altum\Models\Flow())->delete($flow_id);

        http_response_code(200);
        die();

    }
}
