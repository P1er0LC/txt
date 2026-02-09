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
use Altum\Response;

defined('ALTUMCODE') || die();

class Segments extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['type', 'segment_id', 'user_id'], ['name'], ['segment_id', 'name', 'datetime', 'last_datetime', 'total_contacts']));
        $filters->set_default_order_by($this->user->preferences->segments_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `segments` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('segments?' . $filters->get_get() . '&page=%d')));

        /* Get the segments list for the user */
        $segments = [];
        $segments_result = database()->query("SELECT * FROM `segments` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $segments_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $segments[] = $row;
        }

        /* Export handler */
        process_export_json($segments, ['segment_id', 'user_id', 'name', 'type', 'total_contacts', 'settings', 'datetime', 'last_datetime',]);
        process_export_csv_new($segments, ['segment_id', 'user_id', 'name', 'type', 'total_contacts', 'settings', 'datetime', 'last_datetime',], ['settings']);

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'segments' => $segments,
            'total_segments' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('segments/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_segment_count() {

        if(!empty($_POST)) {
            redirect();
        }

        \Altum\Authentication::guard();

        $type = isset($_GET['type']) ? input_clean($_GET['type']) : 'all';

        /* Get settings from custom segments */
        if(is_numeric($type)) {
            $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($_GET['type']);

            $type = $segment->type;

            /* Set the custom filters of the custom segment for processing */
            switch($type) {
                case 'bulk':
                case 'custom':
                    $_GET['contacts_ids'] = $segment->settings->contacts_ids;
                    break;

                case 'filter':
                    if(isset($segment->settings->filters_countries)) $_GET['filters_countries'] = $segment->settings->filters_countries ?? [];
                    if(isset($segment->settings->filters_continents)) $_GET['filters_continents'] = $segment->settings->filters_continents ?? [];
                    if(isset($segment->settings->filters_custom_parameters) && count($segment->settings->filters_custom_parameters)) {
                        foreach($segment->settings->filters_custom_parameters as $key => $custom_parameter) {
                            $_GET['filters_custom_parameter_key'][$key] = $custom_parameter->key;
                            $_GET['filters_custom_parameter_condition'][$key] = $custom_parameter->condition;
                            $_GET['filters_custom_parameter_value'][$key] = $custom_parameter->value;
                        }
                    }
                    break;
            }

        }

        switch($type) {
            case 'all':

                $count = db()->where('user_id', $this->user->user_id)->getValue('contacts', 'COUNT(*)');

                break;

            case 'bulk':
            case 'custom':

                if(empty($_GET['contacts_ids'])) {
                    $count = 0;
                } else {
                    $count = db()->where('user_id', $this->user->user_id)->where('contact_id', $_GET['contacts_ids'], 'IN')->getValue('contacts', 'COUNT(*)');
                }

                break;

            case 'filter':

                $query = db()->where('user_id', $this->user->user_id);

                $has_filters = false;

                /* Custom parameters */
                if(!isset($_GET['filters_custom_parameter_key'])) {
                    $_GET['filters_custom_parameter_key'] = [];
                    $_GET['filters_custom_parameter_condition'] = [];
                    $_GET['filters_custom_parameter_value'] = [];
                }

                $custom_parameters = [];

                foreach($_GET['filters_custom_parameter_key'] as $key => $value) {
                    if(empty(trim($value))) continue;
                    if($key >= 50) continue;

                    $custom_parameters[] = [
                        'key' => input_clean($value, 64),
                        'condition' => isset($_GET['filters_custom_parameter_condition'][$key]) && in_array($_GET['filters_custom_parameter_condition'][$key], ['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than']) ? $_GET['filters_custom_parameter_condition'][$key] : 'exact',
                        'value' => input_clean($_GET['filters_custom_parameter_value'][$key], 512)
                    ];
                }

                if(count($custom_parameters)) {
                    $has_filters = true;

                    foreach($custom_parameters as $custom_parameter) {
                        $key = $custom_parameter['key'];
                        $condition = $custom_parameter['condition'];
                        $value = $custom_parameter['value'];

                        /* reference JSON value once; unquote JSON for string ops, cast for numeric ops */
                        $json_value_expression = 'JSON_UNQUOTE(JSON_EXTRACT(`custom_parameters`, \'$."'.$key.'"\'))';
                        $numeric_expression = 'CAST('.$json_value_expression.' AS DECIMAL(65,10))';

                        switch($condition) {
                            case 'exact':
                                $query->where($json_value_expression.' = \''.$value.'\'');
                                break;

                            case 'not_exact':
                                $query->where($json_value_expression.' != \''.$value.'\'');
                                break;

                            case 'contains':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'%\'');
                                break;

                            case 'not_contains':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'%\'');
                                break;

                            case 'starts_with':
                                $query->where($json_value_expression.' LIKE \''.$value.'%\'');
                                break;

                            case 'not_starts_with':
                                $query->where($json_value_expression.' NOT LIKE \''.$value.'%\'');
                                break;

                            case 'ends_with':
                                $query->where($json_value_expression.' LIKE \'%'.$value.'\'');
                                break;

                            case 'not_ends_with':
                                $query->where($json_value_expression.' NOT LIKE \'%'.$value.'\'');
                                break;

                            case 'bigger_than':
                                $query->where($numeric_expression.' > '.(is_numeric($value) ? $value : '0'));
                                break;

                            case 'lower_than':
                                $query->where($numeric_expression.' < '.(is_numeric($value) ? $value : '0'));
                                break;
                        }
                    }
                }

                /* Countries */
                if(isset($_GET['filters_countries'])) {
                    $_GET['filters_countries'] = array_filter($_GET['filters_countries'] ?? [], function($country) {
                        return array_key_exists($country, get_countries_array());
                    });

                    $has_filters = true;
                    $query->where('country_code', $_GET['filters_countries'], 'IN');
                }

                /* Continents */
                if(isset($_GET['filters_continents'])) {
                    $_GET['filters_continents'] = array_filter($_GET['filters_continents'] ?? [], function($country) {
                        return array_key_exists($country, get_continents_array());
                    });

                    $has_filters = true;
                    $query->where('continent_code', $_GET['filters_continents'], 'IN');
                }

                $count = $has_filters ? $query->getValue('contacts', 'COUNT(*)') : 0;

                break;

            default:
                $count = null;
                break;
        }

        Response::json('', 'success', ['count' => $count]);
    }

    public function bulk() {

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('segments');
        }

        if(empty($_POST['selected'])) {
            redirect('segments');
        }

        if(!isset($_POST['type'])) {
            redirect('segments');
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
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.segments')) {
                        Alerts::add_error(l('global.info_message.team_no_access'));
                        redirect('segments');
                    }

                    foreach($_POST['selected'] as $segment_id) {
                        db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->delete('segments');

                        /* Clear the cache */
                        cache()->deleteItem('segment?segment_id=' . $segment_id);
                    }

                    /* Clear the cache */
                    cache()->deleteItem('segments?user_id=' . $this->user->user_id);
                    cache()->deleteItem('segments_dashboard?user_id=' . $this->user->user_id);

                    break;
            }

            session_start();

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('segments');
    }

    public function delete() {

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.segments')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('segments');
        }

        if(empty($_POST)) {
            redirect('segments');
        }

        $segment_id = (int) query_clean($_POST['segment_id']);

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$segment = db()->where('segment_id', $segment_id)->where('user_id', $this->user->user_id)->getOne('segments', ['segment_id', 'name'])) {
            redirect('segments');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Database query */
            db()->where('segment_id', $segment_id)->delete('segments');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $segment->name . '</strong>'));

            /* Clear the cache */
            cache()->deleteItem('segments?user_id=' . $this->user->user_id);
            cache()->deleteItem('segment?segment_id=' . $segment_id);
            cache()->deleteItem('segments_dashboard?user_id=' . $this->user->user_id);

            redirect('segments');
        }

        redirect('segments');
    }
}
