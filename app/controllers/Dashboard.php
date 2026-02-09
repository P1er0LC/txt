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

defined('ALTUMCODE') || die();

class Dashboard extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(['contacts', 'devices', 'sms', 'campaigns', 'rss_automations', 'recurring_campaigns', 'flows', 'segments'], true);

        /* Get sms */
        if($dashboard_features['sms']) {
            $sms = \Altum\Cache::cache_function_result('sms_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $sms = [];
                $sms_result = database()->query("SELECT * FROM `sms` WHERE `user_id` = {$this->user->user_id} ORDER BY `sms_id` DESC LIMIT 5");
                while ($row = $sms_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $sms[] = $row;
                }

                return $sms;
            });
        }

        /* Get devices */
        if($dashboard_features['devices']) {
            $devices = \Altum\Cache::cache_function_result('devices_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $devices = [];
                $devices_result = database()->query("SELECT * FROM `devices` WHERE `user_id` = {$this->user->user_id} ORDER BY `device_id` DESC LIMIT 5");
                while ($row = $devices_result->fetch_object()) {
                    $row->sims = json_decode($row->sims ?? '');
                    $devices[] = $row;
                }

                return $devices;
            });
        }

        /* Get contacts */
        if($dashboard_features['contacts']) {
            $contacts = \Altum\Cache::cache_function_result('contacts_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $contacts = [];
                $contacts_result = database()->query("SELECT * FROM `contacts` WHERE `user_id` = {$this->user->user_id} ORDER BY `contact_id` DESC LIMIT 5");
                while ($row = $contacts_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $contacts[] = $row;
                }

                return $contacts;
            });
        }

        /* Get campaigns */
        if($dashboard_features['campaigns']) {
            $campaigns = \Altum\Cache::cache_function_result('campaigns_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $campaigns = [];
                $campaigns_result = database()->query("SELECT * FROM `campaigns` WHERE `user_id` = {$this->user->user_id} ORDER BY `campaign_id` DESC LIMIT 5");
                while ($row = $campaigns_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $campaigns[] = $row;
                }

                return $campaigns;
            });
        }

        /* Get RSS automations */
        if($dashboard_features['rss_automations']) {
            $rss_automations = \Altum\Cache::cache_function_result('rss_automations_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $rss_automations = [];
                $rss_automations_result = database()->query("SELECT * FROM `rss_automations` WHERE `user_id` = {$this->user->user_id} ORDER BY `rss_automation_id` DESC LIMIT 5");
                while ($row = $rss_automations_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $rss_automations[] = $row;
                }

                return $rss_automations;
            });
        }

        /* Get recurring campaigns */
        if($dashboard_features['recurring_campaigns']) {
            $recurring_campaigns = \Altum\Cache::cache_function_result('recurring_campaigns_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $recurring_campaigns = [];
                $recurring_campaigns_result = database()->query("SELECT * FROM `recurring_campaigns` WHERE `user_id` = {$this->user->user_id} ORDER BY `recurring_campaign_id` DESC LIMIT 5");
                while ($row = $recurring_campaigns_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $recurring_campaigns[] = $row;
                }

                return $recurring_campaigns;
            });
        }

        /* Get flows */
        if($dashboard_features['flows']) {
            $flows = \Altum\Cache::cache_function_result('flows_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $flows = [];
                $flows_result = database()->query("SELECT * FROM `flows` WHERE `user_id` = {$this->user->user_id} ORDER BY `flow_id` DESC LIMIT 5");
                while ($row = $flows_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $flows[] = $row;
                }

                return $flows;
            });
        }

        /* Get segments */
        if($dashboard_features['segments']) {
            $segments = \Altum\Cache::cache_function_result('segments_dashboard?user_id=' . $this->user->user_id, 'user_id=' . $this->user->user_id, function () {
                $segments = [];
                $segments_result = database()->query("SELECT * FROM `segments` WHERE `user_id` = {$this->user->user_id} ORDER BY `segment_id` DESC LIMIT 5");
                while ($row = $segments_result->fetch_object()) {
                    $row->settings = json_decode($row->settings ?? '');
                    $segments[] = $row;
                }

                return $segments;
            });
        }

        /* Get current monthly usage */
        $usage = db()->where('user_id', $this->user->user_id)->getOne('users', ['text_sent_sms_current_month', 'text_campaigns_current_month',]);

        /* Prepare the view */
        $data = [
            'sms' => $sms ?? null,
            'contacts' => $contacts ?? null,
            'devices' => $devices ?? null,
            'campaigns' => $campaigns ?? null,
            'rss_automations' => $rss_automations ?? null,
            'recurring_campaigns' => $recurring_campaigns ?? null,
            'flows' => $flows ?? null,
            'segments' => $segments ?? null,
            'usage' => $usage,
        ];

        $view = new \Altum\View('dashboard/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_stats_ajax() {

        session_write_close();

        \Altum\Authentication::guard();

        if(!empty($_POST)) {
            redirect();
        }

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

        $sms_chart = \Altum\Cache::cache_function_result('dashboard_chart?user_id=' . $this->user->user_id, null, function() use ($sms_result_query) {
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

        /* Widgets stats */
        $total_devices = \Altum\Cache::cache_function_result('devices_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('devices', 'count(*)');
        });

        $total_contacts = \Altum\Cache::cache_function_result('contacts_total?user_id=' . $this->user->user_id, null, function() {
            return (int) db()->where('user_id', $this->user->user_id)->getValue('contacts', 'count(*)');
        });

        $total_campaigns = \Altum\Cache::cache_function_result('campaigns_total?user_id=' . $this->user->user_id, null, function() {
            return db()->where('user_id', $this->user->user_id)->getValue('campaigns', 'count(*)');
        });

        $total_sent_sms = \Altum\Cache::cache_function_result('total_sent_sms_total?user_id=' . $this->user->user_id, null, function() {
            return (int) db()->where('user_id', $this->user->user_id)->getValue('devices', 'SUM(total_sent_sms)');
        });

        /* Get current monthly usage */
        $usage = db()->where('user_id', $this->user->user_id)->getOne('users', ['text_sent_sms_current_month', 'text_campaigns_current_month']);

        /* Prepare the data */
        $data = [
            'sms_chart' => $sms_chart,

            'usage' => $usage,

            /* Widgets */
            'total_devices' => $total_devices,
            'total_contacts' => $total_contacts,
            'total_campaigns' => $total_campaigns,
            'total_sent_sms' => $total_sent_sms,
        ];

        /* Set a nice success message */
        Response::json('', 'success', $data);

    }

}
