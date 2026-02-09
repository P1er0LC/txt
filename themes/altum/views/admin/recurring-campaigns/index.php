<?php defined('ALTUMCODE') || die() ?>

<div class="d-flex flex-column flex-md-row justify-content-between mb-4">
    <h1 class="h3 mb-3 mb-md-0 text-truncate"><i class="fas fa-fw fa-xs fa-retweet text-primary-900 mr-2"></i> <?= l('admin_recurring_campaigns.header') ?></h1>

    <div class="d-flex position-relative d-print-none">
        <div class="">
            <div class="dropdown">
                <button type="button" class="btn btn-gray-300 dropdown-toggle-simple <?= count($data->recurring_campaigns) ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>" data-tooltip-hide-on-click>
                    <i class="fas fa-fw fa-sm fa-download"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right d-print-none">
                    <a href="<?= url('admin/recurring-campaigns?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->csv ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->csv ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-csv mr-2"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                    </a>
                    <a href="<?= url('admin/recurring-campaigns?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item <?= $this->user->plan_settings->export->json ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->json ? null : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-code mr-2"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                    </a>
                    <a href="#" class="dropdown-item <?= $this->user->plan_settings->export->pdf ? null : 'disabled pointer-events-all' ?>" <?= $this->user->plan_settings->export->pdf ? $this->user->plan_settings->export->pdf ? 'onclick="event.preventDefault(); window.print();"' : 'disabled pointer-events-all' : get_plan_feature_disabled_info() ?>>
                        <i class="fas fa-fw fa-sm fa-file-pdf mr-2"></i> <?= sprintf(l('global.export_to'), 'PDF') ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="ml-3">
            <div class="dropdown">
                <button type="button" class="btn <?= $data->filters->has_applied_filters ? 'btn-dark' : 'btn-gray-300' ?> filters-button dropdown-toggle-simple <?= count($data->recurring_campaigns) || $data->filters->has_applied_filters ? null : 'disabled' ?>" data-toggle="dropdown" data-boundary="viewport" data-tooltip data-html="true" title="<?= l('global.filters.tooltip') ?>" data-tooltip-hide-on-click>
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
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                            <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                <option value="recurring_campaign_id" <?= $data->filters->order_by == 'recurring_campaign_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                                <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="last_run_datetime" <?= $data->filters->order_by == 'last_run_datetime' ? 'selected="selected"' : null ?>><?= l('recurring_campaigns.last_run_datetime') ?></option>
                                <option value="next_run_datetime" <?= $data->filters->order_by == 'next_run_datetime' ? 'selected="selected"' : null ?>><?= l('recurring_campaigns.next_run_datetime') ?></option>
                                <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                <option value="content" <?= $data->filters->order_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                                <option value="total_campaigns" <?= $data->filters->order_by == 'total_campaigns' ? 'selected="selected"' : null ?>><?= l('recurring_campaigns.total_campaigns') ?></option>
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

        <div class="ml-3">
            <button id="bulk_enable" type="button" class="btn btn-gray-300" data-toggle="tooltip" title="<?= l('global.bulk_actions') ?>"><i class="fas fa-fw fa-sm fa-list"></i></button>

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

<?= \Altum\Alerts::output_alerts() ?>

<?php if (!empty($data->recurring_campaigns)): ?>
    <form id="table" action="<?= SITE_URL . 'admin/recurring-campaigns/bulk' ?>" method="post" role="form">
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
                    <th><?= l('global.user') ?></th>
                    <th><?= l('recurring_campaigns.recurring_campaign') ?></th>
                    <th><?= l('campaigns.segment') ?></th>
                    <th><?= l('recurring_campaigns.total_campaigns') ?></th>
                    <th><?= l('sms.sms') ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach($data->recurring_campaigns as $row): ?>

                    <tr>
                        <td data-bulk-table class="d-none">
                            <div class="custom-control custom-checkbox">
                                <input id="selected_recurring_campaign_id_<?= $row->recurring_campaign_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->recurring_campaign_id ?>" />
                                <label class="custom-control-label" for="selected_recurring_campaign_id_<?= $row->recurring_campaign_id ?>"></label>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <div class="d-flex">
                                <a href="<?= url('admin/user-view/' . $row->user_id) ?>">
                                    <img src="<?= get_user_avatar($row->user_avatar, $row->user_email) ?>" referrerpolicy="no-referrer" loading="lazy" class="user-avatar rounded-circle mr-3" alt="" />
                                </a>

                                <div class="d-flex flex-column">
                                    <div>
                                        <a href="<?= url('admin/user-view/' . $row->user_id) ?>"><?= $row->user_name ?></a>
                                    </div>

                                    <span class="text-muted small"><?= $row->user_email ?></span>
                                </div>
                            </div>
                        </td>

                        <td class="text-nowrap">
                            <div>
                                <?= $row->name ?>
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
                            <a href="<?= url('admin/campaigns?recurring_campaign_id=' . $row->recurring_campaign_id) ?>" class="badge badge-secondary">
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

                            <a href="<?= url('admin/sms?rss_automation_id=' . $row->rss_automation_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
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
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('recurring_campaigns.last_run_datetime') . ($row->last_run_datetime ? '<br />' . \Altum\Date::get($row->last_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_run_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                        <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                    </span>

                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('recurring_campaigns.next_run_datetime') . ($row->next_run_datetime ? '<br />' . \Altum\Date::get($row->next_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->next_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->next_run_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
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
                                <?= include_view(THEME_PATH . 'views/admin/recurring-campaigns/admin_recurring_campaign_dropdown_button.php', ['id' => $row->recurring_campaign_id, 'resource_name' => $row->title, ]) ?>
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
        'name' => 'recurring_campaigns',
        'has_secondary_text' => true,
    ]); ?>
<?php endif ?>

<?php require THEME_PATH . 'views/partials/js_bulk.php' ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/bulk_delete_modal.php'), 'modals'); ?>
