<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if($this->user->plan_settings->rss_automations_limit != -1 && $data->total_rss_automations > $this->user->plan_settings->rss_automations_limit): ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-times-circle text-danger mr-2"></i> <?= sprintf(settings()->payment->is_enabled ? l('global.info_message.plan_feature_limit_removal_with_upgrade') : l('global.info_message.plan_feature_limit_removal'), '<strong>' . $data->total_rss_automations - $this->user->plan_settings->rss_automations_limit, mb_strtolower(l('rss_automations.title')) . '</strong>', '<a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '</a>') ?>
        </div>
    <?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-lg d-flex align-items-center mb-3 mb-lg-0 text-truncate">
            <h1 class="h4 m-0 text-truncate"><i class="fas fa-fw fa-xs fa-rss mr-1"></i> <?= l('rss_automations.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('rss_automations.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-lg-auto d-flex flex-wrap gap-3 d-print-none">
            <div>
                <?php if($this->user->plan_settings->rss_automations_limit != -1 && $data->total_rss_automations >= $this->user->plan_settings->rss_automations_limit): ?>
                    <button type="button" class="btn btn-primary disabled" <?= get_plan_feature_limit_reached_info() ?>>
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('rss_automations.create') ?>
                    </button>
                <?php else: ?>
                    <a href="<?= url('rss-automation-create') ?>" class="btn btn-primary" data-toggle="tooltip" data-html="true" title="<?= get_plan_feature_limit_info($data->total_rss_automations, $this->user->plan_settings->rss_automations_limit, isset($data->filters) ? !$data->filters->has_applied_filters : true) ?>">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('rss_automations.create') ?>
                    </a>
                <?php endif ?>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple <?= count($data->rss_automations) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('rss-automations?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('rss-automations?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                        <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : 'disabled pointer-events-all' : get_plan_feature_disabled_info() ?>>
                            <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div>
                <div class="dropdown">
                    <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple <?= count($data->rss_automations) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if($data->filters->has_applied_filters): ?>
                                <a href="<?= url(\Altum\Router::$original_request) ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="content" <?= $data->filters->search_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                                    <option value="rss_url" <?= $data->filters->search_by == 'rss_url' ? 'selected="selected"' : null ?>><?= l('rss_automations.rss_url') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                    <option value="rss_automation_id" <?= $data->filters->order_by == 'rss_automation_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                    <option value="last_check_datetime" <?= $data->filters->order_by == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('rss_automations.last_check_datetime') ?></option>
                                    <option value="next_check_datetime" <?= $data->filters->order_by == 'next_check_datetime' ? 'selected="selected"' : null ?>><?= l('rss_automations.next_check_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                    <option value="content" <?= $data->filters->order_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                                    <option value="total_campaigns" <?= $data->filters->order_by == 'total_campaigns' ? 'selected="selected"' : null ?>><?= l('rss_automations.total_campaigns') ?></option>
                                    <option value="total_sent_sms" <?= $data->filters->order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                                    <option value="total_pending_sms" <?= $data->filters->order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                                    <option value="total_failed_sms" <?= $data->filters->order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <div>
                <button id="bulk_enable" type="button" class="btn btn-light <?= count($data->rss_automations) ? null : 'disabled' ?>" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

                <div id="bulk_group" class="btn-group d-none" role="group">
                    <div class="btn-group dropdown" role="group">
                        <button id="bulk_actions" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                            <?= l('global.bulk_actions') ?> <span id="bulk_counter" class="d-none"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="bulk_actions">
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#bulk_delete_modal"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                        </div>
                    </div>

                    <button id="bulk_disable" type="button" class="btn btn-secondary" data-toggle="tooltip" title="<?= l('global.close') ?>"><i class="fas fa-fw fa-times"></i></button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data->rss_automations)): ?>
        <div class="row mt-n3 mb-3">
            <div class="col-12 col-md-6 col-xl-4 p-3 text-truncate position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=sent') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-paper-plane text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('contacts.x_sent_sms'), '<strong>' . nr($data->sms_stats['total_sent_sms']) . '</strong>') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4 p-3 text-truncate position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=pending') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-spinner <?= $data->sms_stats['total_pending_sms'] ? 'fa-spin' : null ?> text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('contacts.x_pending_sms'), '<strong>' . nr($data->sms_stats['total_pending_sms']) . '</strong>') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4 p-3 text-truncate position-relative">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=failed') ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-exclamation-circle text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= sprintf(l('contacts.x_failed_sms'), '<strong>' . nr($data->sms_stats['total_failed_sms']) . '</strong>') ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if($data->sms_chart): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="chart-container <?= !$data->sms_chart['is_empty'] ? null : 'd-none' ?>">
                    <canvas id="sms_chart"></canvas>
                </div>
                <?= !$data->sms_chart['is_empty'] ? null : include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>

                <?php if(!$data->sms_chart['is_empty'] && settings()->main->chart_cache ?? 12): ?>
                    <small class="text-muted">
                        <span data-toggle="tooltip" title="<?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?>"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i></span>
                        <span class="d-lg-none"><?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></span>
                    </small>
                <?php endif ?>
            </div>
        </div>

    <?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

    <?php ob_start() ?>
        <script>
    'use strict';

            if(document.getElementById('sms_chart')) {
                let css = window.getComputedStyle(document.body);
                let sent_sms_color = css.getPropertyValue('--primary');
                let sent_sms_color_gradient = null;

                let pending_sms_color = css.getPropertyValue('--warning');
                let pending_sms_color_gradient = null;

                let failed_sms_color = css.getPropertyValue('--danger');
                let failed_sms_color_gradient = null;

                /* Chart */
                let sms_chart = document.getElementById('sms_chart').getContext('2d');

                /* Colors */
                sent_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                sent_sms_color_gradient.addColorStop(0, set_hex_opacity(sent_sms_color, 0.9));
                sent_sms_color_gradient.addColorStop(1, set_hex_opacity(sent_sms_color, 0.6));

                pending_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                pending_sms_color_gradient.addColorStop(0, set_hex_opacity(pending_sms_color, 0.9));
                pending_sms_color_gradient.addColorStop(1, set_hex_opacity(pending_sms_color, 0.6));

                failed_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                failed_sms_color_gradient.addColorStop(0, set_hex_opacity(failed_sms_color, 0.9));
                failed_sms_color_gradient.addColorStop(1, set_hex_opacity(failed_sms_color, 0.6));

                new Chart(sms_chart, {
                    type: 'bar',
                    data: {
                        labels: <?= $data->sms_chart['labels'] ?? '[]' ?>,
                        datasets: [
                            {
                                label: <?= json_encode(l('sms.sent')) ?>,
                                data: <?= $data->sms_chart['sent'] ?? '[]' ?>,
                                backgroundColor: sent_sms_color_gradient,
                                borderColor: sent_sms_color,
                                fill: true
                            },

                            {
                                label: <?= json_encode(l('sms.pending')) ?>,
                                data: <?= $data->sms_chart['pending'] ?? '[]' ?>,
                                backgroundColor: pending_sms_color_gradient,
                                borderColor: pending_sms_color,
                                fill: true
                            },

                            {
                                label: <?= json_encode(l('sms.failed')) ?>,
                                data: <?= $data->sms_chart['failed'] ?? '[]' ?>,
                                backgroundColor: failed_sms_color_gradient,
                                borderColor: failed_sms_color,
                                fill: true
                            },
                        ]
                    },
                    options: chart_options
                });
            }
        </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
    <?php endif ?>

        <form id="table" action="<?= SITE_URL . 'rss-automations/bulk' ?>" method="post" role="form">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <input type="hidden" name="type" value="" data-bulk-type />
            <input type="hidden" name="original_request" value="<?= base64_encode(\Altum\Router::$original_request) ?>" />
            <input type="hidden" name="original_request_query" value="<?= base64_encode(\Altum\Router::$original_request_query) ?>" />

            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                <label class="custom-control-label" for="bulk_select_all"></label>
                            </div>
                        </th>
                        <th><?= l('rss_automations.rss_automation') ?></th>
                        <th><?= l('campaigns.segment') ?></th>
                        <th><?= l('rss_automations.total_campaigns') ?></th>
                        <th><?= l('sms.sms') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->rss_automations as $row): ?>

                        <tr>
                            <td data-bulk-table class="d-none">
                                <div class="custom-control custom-checkbox">
                                    <input id="selected_rss_automation_id_<?= $row->rss_automation_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->rss_automation_id ?>" />
                                    <label class="custom-control-label" for="selected_rss_automation_id_<?= $row->rss_automation_id ?>"></label>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div>
                                    <a href="<?= url('rss-automation/' . $row->rss_automation_id) ?>"><?= $row->name ?></a>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?php if(is_numeric($row->segment)): ?>
                                    <a href="<?= url('segment-update/' . $row->segment) ?>" class="badge badge-light">
                                        <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.saved') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-light">
                                        <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.' . $row->segment) ?>
                                    </span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <a href="<?= url('campaigns?rss_automation_id=' . $row->rss_automation_id) ?>" class="badge badge-secondary">
                                    <i class="fas fa-fw fa-sm fa-rocket mr-1"></i> <?= nr($row->total_campaigns) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <?php ob_start() ?>
                                <div class='d-flex flex-column text-left'>
                                    <div class='d-flex flex-column my-1'>
                                        <div><?= l('contacts.total_sent_sms') ?></div>
                                        <strong>
                                            <?= nr($row->total_sent_sms) ?>
                                        </strong>
                                    </div>

                                    <div class='d-flex flex-column my-1'>
                                        <div><?= l('contacts.total_pending_sms') ?></div>
                                        <strong>
                                            <?= nr($row->total_pending_sms) ?>
                                        </strong>
                                    </div>

                                    <div class='d-flex flex-column my-1'>
                                        <div><?= l('contacts.total_failed_sms') ?></div>
                                        <strong>
                                            <?= nr($row->total_failed_sms) ?>
                                        </strong>
                                    </div>
                                </div>
                                <?php $tooltip = ob_get_clean(); ?>

                                <a href="<?= url('sms?rss_automation_id=' . $row->rss_automation_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                    <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                </a>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->is_enabled): ?>
                                    <span class="badge badge-success" data-toggle="tooltip" title="<?= l('global.active') ?>"><i class="fas fa-fw fa-check"></i></span>
                                <?php else: ?>
                                    <span class="badge badge-warning" data-toggle="tooltip" title="<?= l('global.disabled') ?>"><i class="fas fa-fw fa-pause"></i></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('rss_automations.last_check_datetime') . ($row->last_check_datetime ? '<br />' . \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                        <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('rss_automations.next_check_datetime') . ($row->next_check_datetime ? '<br />' . \Altum\Date::get($row->next_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->next_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->next_check_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                        <i class="fas fa-fw fa-calendar-day text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                        <i class="fas fa-fw fa-clock text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                        <i class="fas fa-fw fa-history text-muted"></i>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/rss-automations/rss_automation_dropdown_button.php', ['id' => $row->rss_automation_id, 'resource_name' => $row->name]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>
        <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
            'filters_get' => $data->filters->get ?? [],
            'name' => 'rss_automations',
            'has_secondary_text' => true,
        ]); ?>
    <?php endif ?>
</div>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
