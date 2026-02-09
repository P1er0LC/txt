<?php defined('ALTUMCODE') || die() ?>

<ul class="list-style-none m-0">
    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->sent_sms_per_month_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->sent_sms_per_month_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.sent_sms_per_month_limit'), '<strong>' . ($data->plan_settings->sent_sms_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->sent_sms_per_month_limit)) . '</strong>') ?>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->campaigns_per_month_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->campaigns_per_month_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.campaigns_per_month_limit'), '<strong>' . ($data->plan_settings->campaigns_per_month_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->campaigns_per_month_limit)) . '</strong>') ?>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->contacts_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->contacts_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.contacts_limit'), '<strong>' . ($data->plan_settings->contacts_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->contacts_limit)) . '</strong>') ?>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->devices_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->devices_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.devices_limit'), '<strong>' . ($data->plan_settings->devices_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->devices_limit)) . '</strong>') ?>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->rss_automations_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->rss_automations_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.rss_automations_limit'), '<strong>' . ($data->plan_settings->rss_automations_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->rss_automations_limit)) . '</strong>') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.rss_automations_limit_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->recurring_campaigns_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->recurring_campaigns_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.recurring_campaigns_limit'), '<strong>' . ($data->plan_settings->recurring_campaigns_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->recurring_campaigns_limit)) . '</strong>') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.recurring_campaigns_limit_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->segments_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->segments_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.segments_limit'), '<strong>' . ($data->plan_settings->segments_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->segments_limit)) . '</strong>') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.segments_limit_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->flows_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->flows_limit ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.flows_limit'), '<strong>' . ($data->plan_settings->flows_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->flows_limit)) . '</strong>') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.flows_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <?php ob_start() ?>
    <?php $notification_handlers_icon = 'fa-times-circle text-muted'; ?>
    <div class='d-flex flex-column'>
        <?php foreach(array_keys(require APP_PATH . 'includes/notification_handlers.php') as $notification_handler): ?>
            <span class='my-1'><?= sprintf(l('global.plan_settings.notification_handlers_' . $notification_handler . '_limit'), '<strong>' . ($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'} == -1 ? l('global.unlimited') : nr($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'})) . '</strong>') ?></span>
            <?php if($data->plan_settings->{'notification_handlers_' . $notification_handler . '_limit'} != 0) $notification_handlers_icon = 'fa-check-circle text-success'  ?>
        <?php endforeach ?>
    </div>
    <?php $html = ob_get_clean() ?>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $notification_handlers_icon ?>"></i>
        <div>
            <?= l('global.plan_settings.notification_handlers_limit') ?>
            <span class="ml-1" data-toggle="tooltip" data-html="true" title="<?= $html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <?php if(\Altum\Plugin::is_active('teams')): ?>
        <li class="d-flex align-items-baseline mb-2">
            <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->teams_limit ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
            <div class="<?= $data->plan_settings->teams_limit ? null : 'text-muted' ?>">
                <?= sprintf(l('global.plan_settings.teams_limit'), '<strong>' . ($data->plan_settings->teams_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->teams_limit)) . '</strong>') ?>

                <span class="ml-1" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.plan_settings.team_members_limit'), '<strong>' . ($data->plan_settings->team_members_limit == -1 ? l('global.unlimited') : nr($data->plan_settings->team_members_limit)) . '</strong>') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
            </div>
        </li>
    <?php endif ?>

    <?php if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled): ?>
        <li class="d-flex align-items-baseline mb-2">
            <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->affiliate_commission_percentage ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
            <div class="<?= $data->plan_settings->affiliate_commission_percentage ? null : 'text-muted' ?>">
                <?= sprintf(l('global.plan_settings.affiliate_commission_percentage'), '<strong>' . nr($data->plan_settings->affiliate_commission_percentage) . '%</strong>') ?>
            </div>
        </li>
    <?php endif ?>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->sms_retention ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->sms_retention ? null : 'text-muted' ?>" data-toggle="tooltip" title="<?= ($data->plan_settings->sms_retention == -1 ? '' : $data->plan_settings->sms_retention . ' ' . l('global.date.days')) ?>">
            <?= sprintf(l('global.plan_settings.sms_retention'), '<strong>' . ($data->plan_settings->sms_retention == -1 ? l('global.unlimited') : \Altum\Date::days_format($data->plan_settings->sms_retention)) . '</strong>') ?>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->analytics_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->analytics_is_enabled ? null : 'text-muted' ?>">
            <?= l('global.plan_settings.analytics_is_enabled') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.analytics_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <?php if(settings()->main->api_is_enabled): ?>
        <li class="d-flex align-items-baseline mb-2">
            <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->api_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
            <div class="<?= $data->plan_settings->api_is_enabled ? null : 'text-muted' ?>">
                <?= l('global.plan_settings.api_is_enabled') ?>
                <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.api_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
            </div>
        </li>
    <?php endif ?>

    <?php if(settings()->main->white_labeling_is_enabled): ?>
        <li class="d-flex align-items-baseline mb-2">
            <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->white_labeling_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
            <div class="<?= $data->plan_settings->white_labeling_is_enabled ? null : 'text-muted' ?>">
                <?= l('global.plan_settings.white_labeling_is_enabled') ?>
                <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.white_labeling_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
            </div>
        </li>
    <?php endif ?>

    <?php $enabled_exports_count = count(array_filter((array) $data->plan_settings->export)); ?>

    <?php ob_start() ?>
    <div class='d-flex flex-column'>
        <?php foreach(['csv', 'json', 'pdf'] as $key): ?>
            <?php if($data->plan_settings->export->{$key}): ?>
                <span class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($key)) ?></span>
            <?php else: ?>
                <s class='my-1'><?= sprintf(l('global.export_to'), mb_strtoupper($key)) ?></s>
            <?php endif ?>
        <?php endforeach ?>
    </div>
    <?php $html = ob_get_clean() ?>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $enabled_exports_count ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $enabled_exports_count ? null : 'text-muted' ?>">
            <?= sprintf(l('global.plan_settings.export'), $enabled_exports_count) ?>
            <span class="mr-1" data-html="true" data-toggle="tooltip" title="<?= $html ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->no_ads ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->no_ads ? null : 'text-muted' ?>">
            <?= l('global.plan_settings.no_ads') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.no_ads_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>

    <li class="d-flex align-items-baseline mb-2">
        <i class="fas fa-fw fa-sm mr-3 <?= $data->plan_settings->removable_branding_is_enabled ? 'fa-check-circle text-success' : 'fa-times-circle text-muted' ?>"></i>
        <div class="<?= $data->plan_settings->removable_branding_is_enabled ? null : 'text-muted' ?>">
            <?= l('global.plan_settings.removable_branding_is_enabled') ?>
            <span class="ml-1" data-toggle="tooltip" title="<?= l('global.plan_settings.removable_branding_is_enabled_help') ?>"><i class="fas fa-fw fa-xs fa-circle-question text-gray-500"></i></span>
        </div>
    </li>
</ul>
