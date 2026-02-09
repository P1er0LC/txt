<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="alert alert-info mb-3"><?= sprintf(l('admin_settings.documentation'), '<a href="' . PRODUCT_DOCUMENTATION_URL . '#android' . '" target="_blank">', '</a>') ?></div>
    <?php //ALTUMCODE:DEMO if(DEMO) {settings()->sms->apk = null; settings()->sms->firebase_service_account_json = null; settings()->sms->firebase_project_id = 'hidden on demo'; } ?>

    <div class="form-group">
        <label for="app_name"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.sms.app_name') ?></label>
        <input id="app_name" type="text" name="app_name" class="form-control" value="<?= settings()->sms->app_name ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.sms.app_name_help') ?></small>
    </div>

    <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
        <label for="apk"><i class="fab fa-fw fa-sm fa-android text-muted mr-1"></i> <?= l('admin_settings.sms.apk') ?></label>
        <?= include_view(THEME_PATH . 'views/partials/file_input.php', ['uploads_file_key' => 'apk', 'file_key' => 'apk', 'already_existing_file' => settings()->sms->apk ?? null]) ?>
        <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('apk')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
    </div>

    <div class="form-group">
        <label for="branding"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('admin_settings.sms.branding') ?></label>
        <textarea id="branding" name="branding" class="form-control"><?= settings()->sms->branding ?></textarea>
        <small class="form-text text-muted"><?= l('admin_settings.sms.branding_help') ?></small>
    </div>

    <div class="form-group custom-control custom-switch">
        <input id="email_notices_is_enabled" name="email_notices_is_enabled" type="checkbox" class="custom-control-input" <?= settings()->sms->email_notices_is_enabled ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="email_notices_is_enabled"><?= l('admin_settings.sms.email_notices_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.sms.email_notices_is_enabled_help') ?></small>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#firebase_settings_container" aria-expanded="false" aria-controls="firebase_settings_container">
        <i class="fas fa-fw fa-fire fa-sm mr-1"></i> <?= l('admin_settings.sms.firebase_settings') ?>
    </button>

    <div class="collapse" id="firebase_settings_container">
        <div class="form-group">
            <label for="firebase_project_id"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('admin_settings.sms.firebase_project_id') ?></label>
            <input id="firebase_project_id" type="text" name="firebase_project_id" class="form-control" value="<?= settings()->sms->firebase_project_id ?>" />
        </div>

        <div class="form-group" data-file-image-input-wrapper data-file-input-wrapper-size-limit="<?= get_max_upload() ?>" data-file-input-wrapper-size-limit-error="<?= sprintf(l('global.error_message.file_size_limit'), get_max_upload()) ?>">
            <label for="firebase_service_account_json"><i class="fas fa-fw fa-sm fa-sun text-muted mr-1"></i> <?= l('admin_settings.sms.firebase_service_account_json') ?></label>
            <?= include_view(THEME_PATH . 'views/partials/file_input.php', ['uploads_file_key' => 'firebase', 'file_key' => 'firebase_service_account_json', 'already_existing_file' => settings()->sms->firebase_service_account_json ?? null]) ?>
            <small class="form-text text-muted"><?= sprintf(l('global.accessibility.whitelisted_file_extensions'), \Altum\Uploads::get_whitelisted_file_extensions_accept('firebase')) . ' ' . sprintf(l('global.accessibility.file_size_limit'), get_max_upload()) ?></small>
        </div>
    </div>

    <button class="btn btn-block btn-gray-200 font-size-little-small font-weight-450 mb-4" type="button" data-toggle="collapse" data-target="#cron_settings_container" aria-expanded="false" aria-controls="cron_settings_container">
        <i class="fas fa-fw fa-arrows-rotate fa-sm mr-1"></i> <?= l('admin_settings.cron.cron_settings') ?>
    </button>

    <div class="collapse" id="cron_settings_container">
        <div class="alert alert-danger mb-3"><?= l('admin_settings.cron.cron_settings_help') ?></div>

        <div class="form-group">
            <label for="scheduled_and_flows_sms_per_cron"><?= l('admin_settings.sms.scheduled_and_flows_sms_per_cron') ?></label>
            <input id="scheduled_and_flows_sms_per_cron" type="number" min="0" name="scheduled_and_flows_sms_per_cron" class="form-control" value="<?= settings()->sms->scheduled_and_flows_sms_per_cron ?? 500 ?>" />
        </div>

        <div class="form-group">
            <label for="campaigns_sms_inserts_per_cron"><?= l('admin_settings.sms.campaigns_sms_inserts_per_cron') ?></label>
            <input id="campaigns_sms_inserts_per_cron" type="number" min="0" name="campaigns_sms_inserts_per_cron" class="form-control" value="<?= settings()->sms->campaigns_sms_inserts_per_cron ?? 500 ?>" />
        </div>

        <div class="form-group">
            <label for="flows_contacts_per_cron"><?= l('admin_settings.sms.flows_contacts_per_cron') ?></label>
            <input id="flows_contacts_per_cron" type="number" min="0" name="flows_contacts_per_cron" class="form-control" value="<?= settings()->sms->flows_contacts_per_cron ?? 100 ?>" />
        </div>

        <div class="form-group">
            <label for="rss_automations_per_cron"><?= l('admin_settings.sms.rss_automations_per_cron') ?></label>
            <input id="rss_automations_per_cron" type="number" min="0" name="rss_automations_per_cron" class="form-control" value="<?= settings()->sms->rss_automations_per_cron ?? 10 ?>" />
        </div>

        <div class="form-group">
            <label for="recurring_campaigns_per_cron"><?= l('admin_settings.sms.recurring_campaigns_per_cron') ?></label>
            <input id="recurring_campaigns_per_cron" type="number" min="0" name="recurring_campaigns_per_cron" class="form-control" value="<?= settings()->sms->recurring_campaigns_per_cron ?? 10 ?>" />
        </div>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>
