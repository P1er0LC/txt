<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="d-flex align-items-center mb-3">
        <h1 class="h4 m-0"><?= l('account_preferences.header') ?></h1>

        <div class="ml-2">
            <span data-toggle="tooltip" title="<?= l('account_preferences.subheader') ?>">
                <i class="fas fa-fw fa-info-circle text-muted"></i>
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="account_preferences" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <?php if(settings()->main->white_labeling_is_enabled): ?>
                    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#white_labeling_container" aria-expanded="false" aria-controls="white_labeling_container">
                        <i class="fas fa-fw fa-cube fa-sm mr-1"></i> <?= l('account_preferences.white_labeling') ?>
                    </button>

                    <div class="collapse" data-parent="#account_preferences" id="white_labeling_container">
                        <div <?= $this->user->plan_settings->white_labeling_is_enabled ? null : get_plan_feature_disabled_info() ?>>
                            <div class="<?= $this->user->plan_settings->white_labeling_is_enabled ? null : 'container-disabled' ?>">
                                <div class="form-group">
                                    <label for="white_label_title"><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('account_preferences.white_label_title') ?></label>
                                    <input type="text" id="white_label_title" name="white_label_title" class="form-control <?= \Altum\Alerts::has_field_errors('white_label_title') ? 'is-invalid' : null ?>" value="<?= $this->user->preferences->white_label_title ?>" maxlength="32" />
                                    <?= \Altum\Alerts::output_field_error('white_label_title') ?>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_logo_light"><i class="fas fa-fw fa-sm fa-sun text-muted mr-1"></i> <?= l('account_preferences.white_label_logo_light') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_logo_light', 'already_existing_image' => $this->user->preferences->white_label_logo_light]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_logo_dark"><i class="fas fa-fw fa-sm fa-moon text-muted mr-1"></i> <?= l('account_preferences.white_label_logo_dark') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_logo_dark', 'already_existing_image' => $this->user->preferences->white_label_logo_dark]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>

                                <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
                                    <label for="white_label_favicon"><i class="fas fa-fw fa-sm fa-icons text-muted mr-1"></i> <?= l('account_preferences.white_label_favicon') ?></label>
                                    <?= include_view(THEME_PATH . 'views/partials/file_image_input.php', ['uploads_file_key' => 'users', 'file_key' => 'white_label_favicon', 'already_existing_image' => $this->user->preferences->white_label_favicon]) ?>
                                    <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('users')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#default_settings_container" aria-expanded="false" aria-controls="default_settings_container">
                    <i class="fas fa-fw fa-wrench fa-sm mr-1"></i> <?= l('account_preferences.default_settings') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="default_settings_container">
                    <div class="form-group">
                        <label for="default_results_per_page"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('account_preferences.default_results_per_page') ?></label>
                        <select id="default_results_per_page" name="default_results_per_page" class="custom-select <?= \Altum\Alerts::has_field_errors('default_results_per_page') ? 'is-invalid' : null ?>">
                            <?php foreach([10, 25, 50, 100, 250, 500, 1000] as $key): ?>
                                <option value="<?= $key ?>" <?= ($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page) == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                            <?php endforeach ?>
                        </select>
                        <?= \Altum\Alerts::output_field_error('default_results_per_page') ?>
                    </div>

                    <div class="form-group">
                        <label for="default_order_type"><i class="fas fa-fw fa-sm fa-sort text-muted mr-1"></i> <?= l('account_preferences.default_order_type') ?></label>
                        <select id="default_order_type" name="default_order_type" class="custom-select <?= \Altum\Alerts::has_field_errors('default_order_type') ? 'is-invalid' : null ?>">
                            <option value="ASC" <?= ($this->user->preferences->default_order_type ?? settings()->main->default_order_type) == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                            <option value="DESC" <?= ($this->user->preferences->default_order_type ?? settings()->main->default_order_type) == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('default_order_type') ?>
                    </div>

                    <div class="form-group">
                        <label for="contacts_default_order_by"><i class="fas fa-fw fa-sm fa-address-book text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('contacts.title')) ?></label>
                        <select id="contacts_default_order_by" name="contacts_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('contacts_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="contact_id" <?= $this->user->preferences->contacts_default_order_by == 'contact_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="name" <?= $this->user->preferences->contacts_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="phone_number" <?= $this->user->preferences->contacts_default_order_by == 'phone_number' ? 'selected="selected"' : null ?>><?= l('contacts.phone_number') ?></option>
                            <option value="datetime" <?= $this->user->preferences->contacts_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->contacts_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_sent_datetime" <?= $this->user->preferences->contacts_default_order_by == 'last_sent_datetime' ? 'selected="selected"' : null ?>><?= l('contacts.last_sent_datetime') ?></option>
                            <option value="last_received_datetime" <?= $this->user->preferences->contacts_default_order_by == 'last_received_datetime' ? 'selected="selected"' : null ?>><?= l('contacts.last_received_datetime') ?></option>
                            <option value="total_sent_sms" <?= $this->user->preferences->contacts_default_order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                            <option value="total_pending_sms" <?= $this->user->preferences->contacts_default_order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                            <option value="total_failed_sms" <?= $this->user->preferences->contacts_default_order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                            <option value="total_received_sms" <?= $this->user->preferences->contacts_default_order_by == 'total_received_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_received_sms') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('contacts_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="sms_default_order_by"><i class="fas fa-fw fa-sm fa-comment text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('sms.title')) ?></label>
                        <select id="sms_default_order_by" name="sms_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('sms_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="sms_id" <?= $this->user->preferences->sms_default_order_by == 'sms_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="scheduled_datetime" <?= $this->user->preferences->sms_default_order_by == 'scheduled_datetime' ? 'selected="selected"' : null ?>><?= l('sms.scheduled_datetime') ?></option>
                            <option value="datetime" <?= $this->user->preferences->sms_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('sms_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="devices_default_order_by"><i class="fas fa-fw fa-sm fa-address-book text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('devices.title')) ?></label>
                        <select id="devices_default_order_by" name="devices_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('devices_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="device_id" <?= $this->user->preferences->devices_default_order_by == 'device_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="name" <?= $this->user->preferences->devices_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="datetime" <?= $this->user->preferences->devices_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->devices_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_ping_datetime" <?= $this->user->preferences->devices_default_order_by == 'last_ping_datetime' ? 'selected="selected"' : null ?>><?= l('devices.last_ping_datetime') ?></option>
                            <option value="last_sent_datetime" <?= $this->user->preferences->devices_default_order_by == 'last_sent_datetime' ? 'selected="selected"' : null ?>><?= l('contacts.last_sent_datetime') ?></option>
                            <option value="last_received_datetime" <?= $this->user->preferences->devices_default_order_by == 'last_received_datetime' ? 'selected="selected"' : null ?>><?= l('contacts.last_received_datetime') ?></option>
                            <option value="total_sent_sms" <?= $this->user->preferences->devices_default_order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                            <option value="total_pending_sms" <?= $this->user->preferences->devices_default_order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                            <option value="total_failed_sms" <?= $this->user->preferences->devices_default_order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                            <option value="total_received_sms" <?= $this->user->preferences->devices_default_order_by == 'total_received_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_received_sms') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('devices_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="campaigns_default_order_by"><i class="fas fa-fw fa-sm fa-rocket text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('campaigns.title')) ?></label>
                        <select id="campaigns_default_order_by" name="campaigns_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('campaigns_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="campaign_id" <?= $this->user->preferences->campaigns_default_order_by == 'campaign_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->campaigns_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->campaigns_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="scheduled_datetime" <?= $this->user->preferences->campaigns_default_order_by == 'scheduled_datetime' ? 'selected="selected"' : null ?>><?= l('campaigns.scheduled_datetime') ?></option>
                            <option value="last_sent_datetime" <?= $this->user->preferences->campaigns_default_order_by == 'last_sent_datetime' ? 'selected="selected"' : null ?>><?= l('contacts.last_sent_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->campaigns_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="content" <?= $this->user->preferences->campaigns_default_order_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                            <option value="total_sent_sms" <?= $this->user->preferences->campaigns_default_order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                            <option value="total_pending_sms" <?= $this->user->preferences->campaigns_default_order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                            <option value="total_failed_sms" <?= $this->user->preferences->campaigns_default_order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('campaigns_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="flows_default_order_by"><i class="fas fa-fw fa-sm fa-tasks text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('flows.title')) ?></label>
                        <select id="flows_default_order_by" name="flows_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('flows_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="flow_id" <?= $this->user->preferences->flows_default_order_by == 'flow_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->flows_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->flows_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_sent_datetime" <?= $this->user->preferences->flows_default_order_by == 'last_sent_datetime' ? 'selected="selected"' : null ?>><?= l('contacts.last_sent_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->flows_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="content" <?= $this->user->preferences->flows_default_order_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                            <option value="total_sent_sms" <?= $this->user->preferences->flows_default_order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                            <option value="total_pending_sms" <?= $this->user->preferences->flows_default_order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                            <option value="total_failed_sms" <?= $this->user->preferences->flows_default_order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('flows_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="rss_automations_default_order_by"><i class="fas fa-fw fa-sm fa-rss text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('rss_automations.title')) ?></label>
                        <select id="rss_automations_default_order_by" name="rss_automations_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('rss_automations_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="rss_automation_id" <?= $this->user->preferences->rss_automations_default_order_by == 'rss_automation_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->rss_automations_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->rss_automations_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_check_datetime" <?= $this->user->preferences->rss_automations_default_order_by == 'last_check_datetime' ? 'selected="selected"' : null ?>><?= l('rss_automations.last_check_datetime') ?></option>
                            <option value="next_check_datetime" <?= $this->user->preferences->rss_automations_default_order_by == 'next_check_datetime' ? 'selected="selected"' : null ?>><?= l('rss_automations.next_check_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->rss_automations_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="content" <?= $this->user->preferences->rss_automations_default_order_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                            <option value="total_campaigns" <?= $this->user->preferences->rss_automations_default_order_by == 'total_campaigns' ? 'selected="selected"' : null ?>><?= l('rss_automations.total_campaigns') ?></option>
                            <option value="total_sent_sms" <?= $this->user->preferences->rss_automations_default_order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                            <option value="total_pending_sms" <?= $this->user->preferences->rss_automations_default_order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                            <option value="total_failed_sms" <?= $this->user->preferences->rss_automations_default_order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('rss_automations_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="recurring_campaigns_default_order_by"><i class="fas fa-fw fa-sm fa-retweet text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('recurring_campaigns.title')) ?></label>
                        <select id="recurring_campaigns_default_order_by" name="recurring_campaigns_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('recurring_campaigns_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="recurring_campaign_id" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'recurring_campaign_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="last_run_datetime" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'last_run_datetime' ? 'selected="selected"' : null ?>><?= l('recurring_campaigns.last_run_datetime') ?></option>
                            <option value="next_run_datetime" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'next_run_datetime' ? 'selected="selected"' : null ?>><?= l('recurring_campaigns.next_run_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="content" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'content' ? 'selected="selected"' : null ?>><?= l('sms.content') ?></option>
                            <option value="total_campaigns" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'total_campaigns' ? 'selected="selected"' : null ?>><?= l('recurring_contacts.total_campaigns') ?></option>
                            <option value="total_sent_sms" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'total_sent_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_sent_sms') ?></option>
                            <option value="total_pending_sms" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'total_pending_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_pending_sms') ?></option>
                            <option value="total_failed_sms" <?= $this->user->preferences->recurring_campaigns_default_order_by == 'total_failed_sms' ? 'selected="selected"' : null ?>><?= l('contacts.total_failed_sms') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('recurring_campaigns_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="segments_default_order_by"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('segments.title')) ?></label>
                        <select id="segments_default_order_by" name="segments_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('segments_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="segment_id" <?= $this->user->preferences->segments_default_order_by == 'segment_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->segments_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->segments_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->segments_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                            <option value="total_contacts" <?= $this->user->preferences->segments_default_order_by == 'total_contacts' ? 'selected="selected"' : null ?>><?= l('contacts.total_contacts') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('segments_default_order_by') ?>
                    </div>

                    <div class="form-group">
                        <label for="notification_handlers_default_order_by"><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= sprintf(l('account_preferences.default_order_by_x'), l('notification_handlers.title')) ?></label>
                        <select id="notification_handlers_default_order_by" name="notification_handlers_default_order_by" class="custom-select <?= \Altum\Alerts::has_field_errors('notification_handlers_default_order_by') ? 'is-invalid' : null ?>">
                            <option value="notification_handler_id" <?= $this->user->preferences->notification_handlers_default_order_by == 'notification_handler_id' ? 'selected="selected"' : null ?>><?= l('global.id') ?></option>
                            <option value="datetime" <?= $this->user->preferences->notification_handlers_default_order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                            <option value="last_datetime" <?= $this->user->preferences->notification_handlers_default_order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                            <option value="name" <?= $this->user->preferences->notification_handlers_default_order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                        </select>
                        <?= \Altum\Alerts::output_field_error('notification_handlers_default_order_by') ?>
                    </div>
                </div>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#dashboard_settings_container" aria-expanded="false" aria-controls="dashboard_settings_container">
                    <i class="fas fa-fw fa-table-cells fa-sm mr-1"></i> <?= l('account_preferences.dashboard_features') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="dashboard_settings_container">
                    <div class="form-group">
                        <label><i class="fas fa-fw fa-sm fa-table-cells text-muted mr-1"></i> <?= l('account_preferences.dashboard_features') ?></label>
                    </div>

                    <div id="dashboard_features">
                        <?php $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(['contacts', 'devices', 'sms', 'campaigns', 'rss_automations', 'recurring_campaigns', 'flows', 'segments'], true) ?>
                        <?php $index = 0; ?>
                        <?php foreach($dashboard_features as $feature => $is_enabled): ?>
                            <div class="d-flex">
                            <span class="mr-2">
                                <i class="fas fa-fw fa-sm fa-bars text-muted cursor-grab drag"></i>
                            </span>

                                <div class="form-group custom-control custom-checkbox" data-dashboard-feature>
                                    <input id="<?= 'dashboard_' . $feature ?>" name="dashboard[<?= $index++ ?>]" value="<?= $feature ?>" type="checkbox" class="custom-control-input" <?= $is_enabled ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="<?= 'dashboard_' . $feature ?>"><?= l('dashboard.' . $feature . '.header') ?></label>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#sms_settings_container" aria-expanded="false" aria-controls="sms_settings_container">
                    <i class="fas fa-fw fa-comment fa-sm mr-1"></i> <?= l('account_preferences.sms_features') ?>
                </button>

                <div class="collapse" data-parent="#account_preferences" id="sms_settings_container">
                    <div class="form-group">
                        <label for="start_words"><i class="fas fa-fw fa-sm fa-user-check text-muted mr-1"></i> <?= l('account_preferences.start_words') ?></label>
                        <input type="text" id="start_words" name="start_words" class="form-control <?= \Altum\Alerts::has_field_errors('start_words') ? 'is-invalid' : null ?>" value="<?= implode(',', $this->user->preferences->start_words ?? []) ?>" maxlength="1000" />
                        <?= \Altum\Alerts::output_field_error('start_words') ?>
                        <small class="form-text text-muted"><?= l('account_preferences.start_words_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="stop_words"><i class="fas fa-fw fa-sm fa-user-slash text-muted mr-1"></i> <?= l('account_preferences.stop_words') ?></label>
                        <input type="text" id="stop_words" name="stop_words" class="form-control <?= \Altum\Alerts::has_field_errors('stop_words') ? 'is-invalid' : null ?>" value="<?= implode(',', $this->user->preferences->stop_words ?? []) ?>" maxlength="1000" />
                        <?= \Altum\Alerts::output_field_error('stop_words') ?>
                        <small class="form-text text-muted"><?= l('account_preferences.stop_words_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>
        </div>
    </div>
</div>


<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/sortable.js?v=' . PRODUCT_CODE ?>"></script>
<script>
    'use strict';
    
    let sortable = Sortable.create(document.getElementById('dashboard_features'), {
        animation: 150,
        handle: '.drag',
        onUpdate: event => {

            document.querySelectorAll('#dashboard_features > div').forEach((elm, i) => {
                let input = elm.querySelector('input[type="checkbox"]');
                if(input) {
                    input.setAttribute('name', `dashboard[${i}]`);
                }
            });

        }
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
