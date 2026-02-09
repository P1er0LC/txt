<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('contacts') ?>"><?= l('contacts.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('contact_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-address-book mr-1"></i> <?= l('contact_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form action="" method="post" role="form">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="phone_number"><i class="fas fa-fw fa-sm fa-phone text-muted mr-1"></i> <?= l('contacts.phone_number') ?></label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control <?= \Altum\Alerts::has_field_errors('phone_number') ? 'is-invalid' : null ?>" value="<?= $data->values['phone_number'] ?>" placeholder="<?= l('contacts.phone_number_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('phone_number') ?>
                </div>

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="custom_parameters"><i class="fas fa-fw fa-sm fa-fingerprint text-muted mr-1"></i> <?= l('contacts.custom_parameters') ?></label>

                    <div id="custom_parameters">
                        <?php foreach($data->values['custom_parameters'] as $key => $value): ?>
                            <div class="form-row">
                                <div class="form-group col-lg-5">
                                    <input type="text" name="custom_parameter_key[<?= $key ?>]" class="form-control" value="<?= $key ?>" maxlength="64" placeholder="<?= l('contacts.custom_parameter_key') ?>" />
                                </div>

                                <div class="form-group col-lg-5">
                                    <input type="text" name="custom_parameter_value[<?= $key ?>]" class="form-control" value="<?= $value ?>" maxlength="512" placeholder="<?= l('contacts.custom_parameter_value') ?>" />
                                </div>

                                <div class="form-group col-lg-2 text-center">
                                    <button type="button" data-remove class="btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="mb-3">
                        <button data-add type="button" class="btn btn-sm btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('contacts.custom_parameter_add') ?></button>
                    </div>
                    <?= \Altum\Alerts::output_field_error('custom_parameters') ?>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<template id="template_custom_parameter">
    <div class="form-row">
        <div class="form-group col-lg-5">
            <input type="text" name="custom_parameter_key[]" class="form-control" value="" max="64" placeholder="<?= l('contacts.custom_parameter_key') ?>" />
        </div>

        <div class="form-group col-lg-5">
            <input type="text" name="custom_parameter_value[]" class="form-control" value="" max="512" placeholder="<?= l('contacts.custom_parameter_value') ?>" />
        </div>

        <div class="form-group col-lg-2 text-center">
            <button type="button" data-remove class="btn btn-block btn-outline-danger" title="<?= l('global.delete') ?>"><i class="fas fa-fw fa-times"></i></button>
        </div>
    </div>
</template>

<?php ob_start() ?>
<script>
    'use strict';
    
/* Add new custom parameter */
    let custom_parameter_add = event => {
        let clone = document.querySelector(`#template_custom_parameter`).content.cloneNode(true);

        let custom_parameters_count = document.querySelectorAll(`#custom_parameters .form-row`).length;

        if(custom_parameters_count > 20) {
            return;
        }

        clone.querySelector(`input[name="custom_parameter_key[]"`).setAttribute('name', `custom_parameter_key[${custom_parameters_count}]`);
        clone.querySelector(`input[name="custom_parameter_value[]"`).setAttribute('name', `custom_parameter_value[${custom_parameters_count}]`);

        document.querySelector(`#custom_parameters`).appendChild(clone);

        custom_parameter_remove_initiator();
    };

    document.querySelectorAll('[data-add]').forEach(element => {
        element.addEventListener('click', custom_parameter_add);
    })

    /* remove custom parameter */
    let custom_parameter_remove = event => {
        event.currentTarget.closest('.form-row').remove();
    };

    let custom_parameter_remove_initiator = () => {
        document.querySelectorAll('#custom_parameters [data-remove]').forEach(element => {
            element.removeEventListener('click', custom_parameter_remove);
            element.addEventListener('click', custom_parameter_remove)
        })
    };

    custom_parameter_remove_initiator();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
