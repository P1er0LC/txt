<?php defined('ALTUMCODE') || die() ?>

<div class="card mb-5">
    <div class="card-body">
        <div class="chart-container">
            <canvas id="contacts_chart"></canvas>
        </div>
    </div>
</div>

<div class="d-flex align-items-center">
    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3" data-toggle="tooltip" title="<?= sprintf(l('contacts_statistics.data_preview_info'), $this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page) ?>">
        <i class="fas fa-fw fa-sm fa-info-circle mr-1"></i> <?= l('contacts_statistics.data_preview') ?>
    </h2>

    <div class="flex-fill">
        <hr class="border-gray-100" />
    </div>
</div>

<div class="row mb-4">
    <div class="col-12 col-lg-6 my-3">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h5"><?= l('global.continents') ?></h3>
                <p></p>

                <?php $i = 0; foreach($data->statistics['continent_code'] as $key => $value): $i++; if($i > 5) break; ?>
                    <?php $percentage = round($value / $data->statistics['continent_code_total_sum'] * 100, 1) ?>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <div class="text-truncate">
                                <?php if($key): ?>
                                    <a href="<?= url('contacts-statistics?type=country&continent_code=' . $key . '&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" title="<?= $key ?>" class="">
                                        <?= get_continent_from_continent_code($key) ?>
                                    </a>
                                <?php else: ?>
                                    <span class=""><?= $key ? get_continent_from_continent_code($key) : l('global.unknown') ?></span>
                                <?php endif ?>
                            </div>

                            <div>
                                <small class="text-muted"><?= nr($percentage, 2, false) . '%' ?></small>
                                <span class="ml-3"><?= nr($value) ?></span>
                            </div>
                        </div>

                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="card-body small py-3 d-flex align-items-end">
                <a href="<?= url('contacts-statistics?type=continent_code&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?></a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 my-3">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h5"><?= l('global.countries') ?></h3>
                <p></p>

                <?php $i = 0; foreach($data->statistics['country_code'] as $key => $value): $i++; if($i > 5) break; ?>
                    <?php $percentage = round($value / $data->statistics['country_code_total_sum'] * 100, 1) ?>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <div class="text-truncate">
                                <img src="<?= ASSETS_FULL_URL . 'images/countries/' . ($key ? mb_strtolower($key) : 'unknown') . '.svg' ?>" class="img-fluid icon-favicon mr-1" />
                                <?php if($key): ?>
                                    <a href="<?= url('contacts-statistics?type=city_name&country_code=' . $key . '&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" title="<?= $key ?>" class=""><?= get_country_from_country_code($key) ?></a>
                                <?php else: ?>
                                    <span class=""><?= $key ? get_country_from_country_code($key) : l('global.unknown') ?></span>
                                <?php endif ?>
                            </div>

                            <div>
                                <small class="text-muted"><?= nr($percentage, 2, false) . '%' ?></small>
                                <span class="ml-3"><?= nr($value) ?></span>
                            </div>
                        </div>

                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="card-body small py-3 d-flex align-items-end">
                <a href="<?= url('contacts-statistics?type=country&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="card h-100">
    <div class="card-body">
        <h3 class="h5"><?= l('contacts_statistics.has_opted_out') ?></h3>
        <p></p>

        <?php $i = 0; foreach($data->statistics['has_opted_out'] as $key => $value): $i++; if($i > 5) break; ?>
            <?php $percentage = round($value / $data->statistics['has_opted_out_total_sum'] * 100, 1) ?>

            <div class="mt-4">
                <div class="d-flex justify-content-between mb-1">
                    <div class="text-truncate">
                        <i class="fas fa-fw fa-sm <?= $key ? 'fa-user-slash text-warning' : 'fa-user-check' ?> mr-1"></i>

                        <?= ($key ? l('contacts.opted_out') : l('contacts.opted_in')) ?>
                    </div>

                    <div>
                        <small class="text-muted"><?= nr($percentage, 2, false) . '%' ?></small>
                        <span class="ml-3"><?= nr($value) ?></span>
                    </div>
                </div>

                <div class="progress" style="height: 6px;">
                    <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <div class="card-body small py-3 d-flex align-items-end">
        <a href="<?= url('contacts-statistics?type=language&start_date=' . $data->datetime['start_date'] . '&end_date=' . $data->datetime['end_date']) ?>" class="text-muted text-decoration-none"><i class="fas fa-angle-right fa-sm fa-fw mr-1"></i> <?= l('global.view_more') ?></a>
    </div>
</div>

<?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>

<?php ob_start() ?>

    <script>
        'use strict';

        <?php if($data->has_data): ?>
        let css = window.getComputedStyle(document.body);
        let contacts_color = css.getPropertyValue('--primary');
        let contacts_color_gradient = null;

        /* Chart */
        let contacts_chart = document.getElementById('contacts_chart').getContext('2d');

        /* Colors */
        contacts_color_gradient = contacts_chart.createLinearGradient(0, 0, 0, 250);
        contacts_color_gradient.addColorStop(0, set_hex_opacity(contacts_color, 0.6));
        contacts_color_gradient.addColorStop(1, set_hex_opacity(contacts_color, 0.1));

        /* Display chart */
        new Chart(contacts_chart, {
            type: 'line',
            data: {
                labels: <?= $data->contacts_chart['labels'] ?>,
                datasets: [
                    {
                        label: <?= json_encode(l('contacts.contacts')) ?>,
                        data: <?= $data->contacts_chart['total'] ?? '[]' ?>,
                        backgroundColor: contacts_color_gradient,
                        borderColor: contacts_color,
                        fill: true
                    }
                ]
            },
            options: chart_options
        });
        <?php endif ?>
    </script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
