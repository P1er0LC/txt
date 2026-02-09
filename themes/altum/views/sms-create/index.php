<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('sms') ?>"><?= l('sms.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('sms_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-comment mr-1"></i> <?= l('sms_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group" data-character-counter="textarea">
                    <label for="content" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('sms.content') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="content" name="content" class="form-control <?= \Altum\Alerts::has_field_errors('content') ? 'is-invalid' : null ?>" maxlength="1000" required="required"><?= $data->values['content'] ?></textarea>
                    <?= \Altum\Alerts::output_field_error('content') ?>
                    <small class="form-text text-muted"><?= l('campaigns.content_help') ?></small>
                    <small class="form-text text-muted"><?= sprintf(l('global.variables'), '<code data-copy>' . implode('</code> , <code data-copy>',  ['{{NAME}}', '{{PHONE_NUMBER}}', '{{CONTINENT_NAME}}', '{{COUNTRY_NAME}}', '{{CUSTOM_PARAMETERS:KEY}}']) . '</code>') ?></small>
                    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
                </div>

                <?php if(!$this->user->plan_settings->removable_branding_is_enabled && settings()->sms->branding): ?>
                <div class="form-group">
                    <label for="branding"><i class="fas fa-fw fa-sm fa-shuffle text-muted mr-1"></i> <?= l('sms.branding') ?></label>
                    <input type="text" id="branding" name="branding" class="form-control <?= \Altum\Alerts::has_field_errors('branding') ? 'is-invalid' : null ?>" value="<?= settings()->sms->branding ?>" disabled="disabled" />
                    <?= \Altum\Alerts::output_field_error('branding') ?>
                    <small class="form-text text-muted"><?= l('sms.branding_help') ?></small>
                </div>
                <?php endif ?>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="device_id"><i class="fas fa-fw fa-sm fa-mobile text-muted mr-1"></i> <?= l('devices.device') ?></label>
                        <a href="<?= url('device-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('devices.create') ?></a>
                    </div>
                    <select id="device_id" name="device_id" class="form-control <?= \Altum\Alerts::has_field_errors('device_id') ? 'is-invalid' : null ?>" required="required">
                        <?php foreach($data->devices as $device): ?>
                        <option
                                value="<?= $device->device_id ?>"
                                data-sims='<?= json_encode($device->sims) ?>'
                                <?= $data->values['device_id'] == $device->device_id ? 'selected="selected"' : null ?>
                        >
                            <?= $device->name ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('device_id') ?>
                </div>

                <div class="form-group">
                    <label for="sim_subscription_id"><i class="fas fa-fw fa-sm fa-sim-card text-muted mr-1"></i> <?= l('devices.sim_subscription_id') ?></label>
                    <select id="sim_subscription_id" name="sim_subscription_id" class="form-control <?= \Altum\Alerts::has_field_errors('sim_subscription_id') ? 'is-invalid' : null ?>" required="required" data-selected-sim-subscription-id="<?= $data->values['sim_subscription_id'] ?>"></select>
                    <?= \Altum\Alerts::output_field_error('sim_subscription_id') ?>
                </div>

                <div class="form-group">
                    <label for="phone_numbers"><i class="fas fa-fw fa-sm fa-address-book text-muted mr-1"></i> <?= l('campaigns.phone_numbers') ?></label>
                    <textarea id="phone_numbers" name="phone_numbers" class="form-control <?= \Altum\Alerts::has_field_errors('phone_numbers') ? 'is-invalid' : null ?>" required="required" placeholder="<?= l('contacts.phone_number_placeholder') . "\r\n" . l('contacts.phone_number_placeholder') ?>"><?= $data->values['phone_numbers'] ?></textarea>
                    <?= \Altum\Alerts::output_field_error('phone_numbers') ?>
                    <small class="form-text text-muted"><?= l('campaigns.phone_numbers_help') ?></small>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#scheduling_container" aria-expanded="false" aria-controls="scheduling_container">
                    <i class="fas fa-fw fa-calendar-day fa-sm mr-1"></i> <?= l('campaigns.scheduling') ?>
                </button>

                <div class="collapse" id="scheduling_container">
                    <div class="form-group custom-control custom-switch">
                        <input
                                id="is_scheduled"
                                name="is_scheduled"
                                type="checkbox"
                                class="custom-control-input"
                            <?= $data->values['is_scheduled'] && !empty($data->values['scheduled_datetime']) ? 'checked="checked"' : null ?>
                        >
                        <label class="custom-control-label" for="is_scheduled"><?= l('sms.is_scheduled') ?></label>
                    </div>

                    <div id="is_scheduled_container" class="d-none">
                        <div class="form-group">
                            <label for="scheduled_datetime"><i class="fas fa-fw fa-calendar-day fa-sm text-muted mr-1"></i> <?= l('campaigns.scheduled_datetime') ?></label>
                            <input
                                    id="scheduled_datetime"
                                    type="text"
                                    class="form-control"
                                    name="scheduled_datetime"
                                    value="<?= (new \DateTime($data->values['scheduled_datetime'], new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>"
                                    placeholder="<?= l('campaigns.scheduled_datetime') ?>"
                                    autocomplete="off"
                                    data-daterangepicker
                            />
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-block btn-primary mt-3"><?= l('sms.send') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';
    
/* Schedule */
    let schedule_handler = () => {
        if(document.querySelector('#is_scheduled').checked) {
            document.querySelector('#is_scheduled_container').classList.remove('d-none');
        } else {
            document.querySelector('#is_scheduled_container').classList.add('d-none');
        }
    };

    document.querySelector('#is_scheduled').addEventListener('change', schedule_handler);

    schedule_handler();

    /* Device */
    let device_handler = () => {
        let device = document.querySelector('#device_id');
        let selected_device = device.options[device.selectedIndex];
        let sims = JSON.parse(selected_device.getAttribute('data-sims'));

        let sim_subscription_id = document.querySelector('#sim_subscription_id');

        /* Clear all existing options */
        sim_subscription_id.options.length = 0;

        if(sims && sims.length) {
            sims.forEach(sim => {
                sim_subscription_id.options.add(new Option(sim.display_name, sim.subscription_id))
            })
        }

        /* Selected */
        let selected_sim_subscription_id = document.querySelector('#sim_subscription_id').getAttribute('data-selected-sim-subscription-id');
        if(!selected_sim_subscription_id) {
            document.querySelector('#sim_subscription_id').selectedIndex = 0;
        } else {
            document.querySelector('#sim_subscription_id').value = selected_sim_subscription_id;
        }
    };

    document.querySelector('#device_id').addEventListener('change', device_handler);

    device_handler();

    /* Daterangepicker */
    let locale = <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>;
    $('[data-daterangepicker]').daterangepicker({
        minDate: "<?= (new \DateTime('', new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>",
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {...locale, format: 'YYYY-MM-DD HH:mm:ss'},
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
    }, (start, end, label) => {});
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
