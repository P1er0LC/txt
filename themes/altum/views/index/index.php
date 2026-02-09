<?php defined('ALTUMCODE') || die() ?>

<div class="index-background py-7">
    <div class="container">
        <?= \Altum\Alerts::output_alerts() ?>

        <div class="row justify-content-center">
            <div class="col-11 col-md-10 col-lg-8 col-xl-7">
                <div class="text-center mb-2">
                    <span class="badge badge-primary badge-pill"><i class="fas fa-fw fa-sm fa-code mr-1"></i> <?= l('index.subheader2') ?></span>
                </div>

                <h1 class="index-header text-center mb-2"><?= l('index.header') ?></h1>
            </div>

            <div class="col-10 col-sm-8 col-lg-6">
                <p class="index-subheader text-center mb-5"><?= sprintf(l('index.subheader'), $data->total_sent_sms) ?></p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-10 col-sm-8 col-lg-6">
                <div class="d-flex flex-column flex-lg-row justify-content-center">
                    <?php if(settings()->users->register_is_enabled): ?>
                        <a href="<?= url('register') ?>" class="btn btn-primary index-button mb-3 mb-lg-0">
                            <?= l('index.register') ?> <i class="fas fa-fw fa-sm fa-arrow-right"></i>
                        </a>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center mt-7" data-aos="fade-up">
        <div class="col-12">
            <div class="index-hero-wrapper rounded-2x">
                <div class="index-hero-gradient"></div>

                <img
                        src="<?= get_custom_image_if_any('index/hero-' . \Altum\ThemeStyle::get() . '.webp') ?>"
                        class="img-fluid position-relative zoom-animation-subtle index-hero-image rounded-2x"
                        loading="lazy"
                        alt="<?= l('index.hero_image_alt') ?>"
                />
            </div>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="row">
        <div class="col-12 col-lg-4 p-3" data-aos="fade-up" data-aos-delay="100">
            <div class="card bg-gray-50 mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        1
                    </div>

                    <h2 class="h6 m-0"><?= l('index.tutorial.one') ?></h2>

                    <small class="text-muted m-0"><?= l('index.tutorial.one_text') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3" data-aos="fade-up" data-aos-delay="200">
            <div class="card bg-gray-50 mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        2
                    </div>

                    <h2 class="h6 m-0"><?= l('index.tutorial.two') ?></h2>

                    <small class="text-muted m-0"><?= l('index.tutorial.two_text') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 p-3" data-aos="fade-up" data-aos-delay="300">
            <div class="card bg-gray-50 mb-md-0 h-100 up-animation">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        3
                    </div>

                    <h2 class="h6 m-0"><?= l('index.tutorial.three') ?></h2>

                    <small class="text-muted m-0"><?= l('index.tutorial.three_text') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/sms.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.notification_example_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.sms.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.sms.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.sms.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.sms.scheduling') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.sms.custom_parameters') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.sms.spintax') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.sms.bulk') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.sms.api') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/contacts.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.subscribers_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.contacts.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.contacts.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.contacts.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.contacts.location') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.contacts.custom_parameters') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.contacts.statistics') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.contacts.logs') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/campaigns.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.campaigns_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.campaigns.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.campaigns.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.campaigns.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.spintax') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.custom_parameters') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.segments') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.campaigns.statistics') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/flows.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.flows_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.flows.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.flows.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.flows.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.one') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.two') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.three') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.flows.four') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/segments.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.segments_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.segments.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.segments.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.segments.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.custom') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.continents') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.countries') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.segments.custom_parameters') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/rss_automations.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.rss_automations_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.rss_automations.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.rss_automations.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.rss_automations.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.rss_automations.one') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.rss_automations.two') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.rss_automations.three') ?></div>
            </div>
        </div>
    </div>

    <div class="my-6">&nbsp;</div>

    <div class="row justify-content-between" data-aos="fade-up">
        <div class="col-12 col-md-5 text-center mb-5 mb-md-0" >
            <img src="<?= get_custom_image_if_any('index/recurring_campaigns.webp') ?>" class="inverse-colors-animation img-fluid rounded-2x" loading="lazy" alt="<?= l('index.recurring_campaigns_image_alt') ?>" />
        </div>

        <div class="col-12 col-md-6 d-flex flex-column justify-content-center">
            <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.recurring_campaigns.name') ?></div>

            <div>
                <h2 class="mb-4"><?= l('index.recurring_campaigns.header') ?></h2>

                <p class="text-muted mb-4"><?= l('index.recurring_campaigns.subheader') ?></p>

                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.recurring_campaigns.one') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.recurring_campaigns.two') ?></div>
                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('index.recurring_campaigns.three') ?></div>
            </div>
        </div>
    </div>
</div>


<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="row m-n4">
        <div class="col-12 col-sm-6 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-chart-line"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.statistics.header') ?></h2>

                    <small class="text-muted m-0"><?= l('index.statistics.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-sms"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.receive_sms.header') ?></h2>

                    <small class="text-muted m-0"><?= l('index.receive_sms.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="300">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-address-book"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.contacts2.header') ?></h2>

                    <small class="text-muted m-0"><?= l('index.contacts2.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="500">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-mobile-alt"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.devices.header') ?></h2>

                    <small class="text-muted m-0"><?= l('index.devices.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="600">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-file-export"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.export.header') ?></h2>

                    <small class="text-muted m-0"><?= l('index.export.subheader') ?></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-4 p-3">
            <div class="card bg-gray-50 mb-md-0 h-100" data-aos="fade-up" data-aos-delay="700">
                <div class="card-body icon-zoom-animation">
                    <div class="index-icon-container mb-2">
                        <i class="fas fa-fw fa-mobile"></i>
                    </div>

                    <h2 class="h6 m-0"><?= l('index.app.header') ?></h2>

                    <small class="text-muted m-0"><?= l('index.app.subheader') ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="p-4">
    <div class="card rounded-2x index-stats-card border-0" style="background-image: url('<?= ASSETS_FULL_URL . 'images/index/numbers.webp' ?>');">
        <div class="card-body py-5 py-lg-6 text-center">
            <span class="h3"><?= sprintf(l('index.stats'), nr($data->total_devices, 0, true, true), nr($data->total_sent_sms, 0, true, true), nr($data->total_contacts, 0, true, true)) ?></span>
        </div>
    </div>
</div>

<div class="my-6">&nbsp;</div>

<div class="container">
    <div class="text-center mb-4">
        <h2><?= l('index.notifications_handlers.header') ?> <i class="fas fa-fw fa-xs fa-comment ml-1"></i> </h2>
    </div>

    <div class="row mx-n4">
        <?php $notification_handlers = require APP_PATH . 'includes/notification_handlers.php' ?>
        <?php $i = 0; ?>
        <?php foreach($notification_handlers as $key => $notification_handler): ?>
            <div class="col-6 col-lg-4 p-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="position-relative w-100 h-100 icon-zoom-animation">
                    <div class="position-absolute rounded-2x w-100 h-100" style="background: <?= $notification_handler['color'] ?>;opacity: 0.05;"></div>

                    <div class="rounded-2x w-100 p-4 text-truncate text-center">
                        <div><i class="<?= $notification_handler['icon'] ?> fa-fw fa-xl mx-1" style="color: <?= $notification_handler['color'] ?>"></i></div>

                        <div class="mt-3 mb-0 h6 text-truncate"><?= l('notification_handlers.type_' . $key) ?></div>
                    </div>
                </div>
            </div>
            <?php $i++ ?>
        <?php endforeach ?>
    </div>
</div>

<?php if(settings()->main->api_is_enabled): ?>
    <div class="py-6"></div>

    <div class="container">
        <div class="row align-items-center justify-content-between" data-aos="fade-up">
            <div class="col-12 col-lg-5 mb-5 mb-lg-0 d-flex flex-column justify-content-center">
                <div class="text-uppercase font-weight-bold text-primary mb-3"><?= l('index.api.name') ?></div>

                <div>
                    <h2 class="mb-2"><?= l('index.api.header') ?></h2>
                    <p class="text-muted mb-4"><?= l('index.api.subheader') ?></p>

                    <div class="position-relative">
                        <div class="index-fade"></div>
                        <div class="row">
                            <div class="col">
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('devices.title') ?></div>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('contacts.title') ?></div>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('api_documentation.contacts_statistics') ?></div>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('campaigns.title') ?></div>
                            </div>

                            <div class="col">
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('sms.title') ?></div>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('flows.title') ?></div>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('segments.title') ?></div>
                                <div class="small mb-2"><i class="fas fa-fw fa-check-circle text-success mr-1"></i> <?= l('notification_handlers.title') ?></div>
                            </div>
                        </div>
                    </div>

                    <a href="<?= url('api-documentation') ?>" class="btn btn-block btn-outline-primary mt-5">
                        <?= l('api_documentation.menu') ?> <i class="fas fa-fw fa-xs fa-code ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card rounded-2x bg-dark text-white">
                    <div class="card-body p-4 text-monospace reveal-effect font-size-small" style="line-height: 1.75">
                        curl --request POST \<br />
                        --url '<?= SITE_URL ?>api/sms' \<br />
                        --header 'Authorization: Bearer <span class="text-primary" <?= is_logged_in() ? 'data-toggle="tooltip" title="' . l('api_documentation.api_key') . '"' : null ?>><?= is_logged_in() ? $this->user->api_key : '{api_key}' ?></span>' \<br />
                        --header 'Content-Type: multipart/form-data' \<br />
                        --form 'content=<span class="text-primary">Hello world</span>' \<br />
                        --form 'phone_number=<span class="text-primary">+123 123 123</span>' \<br />
                        --form 'device_id=<span class="text-primary">1</span>' \<br />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script defer>
        /* wrap words in a text node while preserving existing HTML */
        const wrap_words_in_text_node = (text_node) => {
            /* split into words + spaces, keep spacing intact */
            const tokens = text_node.textContent.split(/(\s+)/);
            const fragment = document.createDocumentFragment();

            tokens.forEach((token) => {
                if (token.trim().length === 0) {
                    fragment.appendChild(document.createTextNode(token));
                } else {
                    const span_node = document.createElement('span');
                    span_node.className = 'reveal-effect-word';
                    span_node.textContent = token;
                    fragment.appendChild(span_node);
                }
            });

            text_node.parentNode.replaceChild(fragment, text_node);
        };

        /* prepare a container: wrap only pure text nodes, not tags */
        const prepare_reveal_container = (container_node) => {
            /* collect first to avoid live-walking issues while replacing */
            const walker = document.createTreeWalker(
                container_node,
                NodeFilter.SHOW_TEXT,
                { acceptNode: (node) => node.textContent.trim().length ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT }
            );
            const text_nodes = [];
            while (walker.nextNode()) { text_nodes.push(walker.currentNode); }
            text_nodes.forEach(wrap_words_in_text_node);

            /* add stagger */
            const word_nodes = container_node.querySelectorAll('.reveal-effect-word');
            word_nodes.forEach((word_node, index) => {
                word_node.style.transitionDelay = (index * 40) + 'ms';
            });

            /* mark as prepared and reveal visibility */
            container_node.classList.add('reveal-effect-prepared');
            container_node.style.visibility = 'visible';
        };

        /* set up scroll trigger */
        document.addEventListener('DOMContentLoaded', () => {
            const container_node = document.querySelector('.reveal-effect');
            if (!container_node) { return; }

            /* prepare once (preserves HTML) */
            prepare_reveal_container(container_node);

            /* trigger when in view */
            const on_intersect = (entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        /* start the animation */
                        container_node.classList.add('reveal-effect-in');
                        observer.unobserve(container_node);
                    }
                });
            };

            const intersection_observer = new IntersectionObserver(on_intersect, {
                root: null,
                rootMargin: '0px 0px -10% 0px',
                threshold: 0.1
            });

            intersection_observer.observe(container_node);
        });
    </script>
<?php endif ?>

<?php if(settings()->main->display_index_testimonials): ?>
    <div class="my-5">&nbsp;</div>

    <div class="p-4">
        <div class="mt-5 py-7 bg-primary-100 rounded-2x">
            <div class="container">
                <div class="text-center">
                    <h2><?= l('index.testimonials.header') ?> <i class="fas fa-fw fa-xs fa-check-circle text-primary"></i></h2>
                </div>

                <?php
                $language_array = \Altum\Language::get(\Altum\Language::$name);
                if(\Altum\Language::$main_name != \Altum\Language::$name) {
                    $language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
                }

                $testimonials_language_keys = [];
                foreach ($language_array as $key => $value) {
                    if(preg_match('/index\.testimonials\.(\w+)\./', $key, $matches)) {
                        $testimonials_language_keys[] = $matches[1];
                    }
                }

                $testimonials_language_keys = array_unique($testimonials_language_keys);
                ?>

                <div class="row mt-8 mx-n3">
                    <?php foreach($testimonials_language_keys as $key => $value): ?>
                        <div class="col-12 col-lg-4 mb-7 mb-lg-0 px-4" data-aos="fade-up" data-aos-delay="<?= $key * 100 ?>">
                            <div class="card border-0 zoom-animation-subtle">
                                <div class="card-body">
                                    <img src="<?= get_custom_image_if_any('index/testimonial-' . $value . '.webp') ?>" class="img-fluid index-testimonial-avatar" alt="<?= l('index.testimonials.' . $value . '.name') . ', ' . l('index.testimonials.' . $value . '.attribute') ?>" loading="lazy" />

                                    <p class="mt-5">
                                        <span class="text-gray-800 font-weight-bold text-muted h5">“</span>
                                        <span><?= l('index.testimonials.' . $value . '.text') ?></span>
                                        <span class="text-gray-800 font-weight-bold text-muted h5">”</span>
                                    </p>

                                    <div class="blockquote-footer mt-4">
                                        <span class="font-weight-bold"><?= l('index.testimonials.' . $value . '.name') ?></span><br /> <span class="text-muted index-testimonial-comment"><?= l('index.testimonials.' . $value . '.attribute') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_plans): ?>
    <div class="my-5">&nbsp;</div>

    <div id="plans" class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.pricing.header') ?></h2>
            <p class="text-muted"><?= l('index.pricing.subheader') ?></p>
        </div>

        <?= $this->views['plans'] ?>
    </div>
<?php endif ?>

<?php if(settings()->main->display_index_faq): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= l('index.faq.header') ?></h2>
        </div>

        <?php
        $language_array = \Altum\Language::get(\Altum\Language::$name);
        if(\Altum\Language::$main_name != \Altum\Language::$name) {
            $language_array = array_merge(\Altum\Language::get(\Altum\Language::$main_name), $language_array);
        }

        $faq_language_keys = [];
        foreach ($language_array as $key => $value) {
            if(preg_match('/index\.faq\.(\w+)\./', $key, $matches)) {
                $faq_language_keys[] = $matches[1];
            }
        }

        $faq_language_keys = array_unique($faq_language_keys);
        ?>

        <div class="accordion index-faq" id="faq_accordion">
            <?php foreach($faq_language_keys as $key): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="" id="<?= 'faq_accordion_' . $key ?>">
                            <h3 class="mb-0">
                                <button class="btn btn-lg font-weight-500 btn-block d-flex justify-content-between text-gray-800 px-0 icon-zoom-animation no-focus" type="button" data-toggle="collapse" data-target="<?= '#faq_accordion_answer_' . $key ?>" aria-expanded="true" aria-controls="<?= 'faq_accordion_answer_' . $key ?>">
                                    <span class="text-left"><?= l('index.faq.' . $key . '.question') ?></span>

                                    <span data-icon>
                                        <i class="fas fa-fw fa-circle-chevron-down"></i>
                                    </span>
                                </button>
                            </h3>
                        </div>

                        <div id="<?= 'faq_accordion_answer_' . $key ?>" class="collapse text-muted mt-2" aria-labelledby="<?= 'faq_accordion_' . $key ?>" data-parent="#faq_accordion">
                            <?= l('index.faq.' . $key . '.answer') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>

    <?php ob_start() ?>
    <script>
        'use strict';

        $('#faq_accordion').on('show.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.transform = 'rotate(180deg)';
            svg.style.color = 'var(--primary)';
        })

        $('#faq_accordion').on('hide.bs.collapse', event => {
            let svg = event.target.parentElement.querySelector('[data-icon] svg')
            svg.style.color = 'var(--primary-800)';
            svg.style.removeProperty('transform');
        })
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
<?php endif ?>

<?php if(settings()->users->register_is_enabled): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="position-relative">


            <div class="card border-gray-100 index-cta py-5 py-lg-6" data-aos="fade-up">
                <div class="index-cta-blob index-cta-blob-one"></div>
                <div class="index-cta-blob index-cta-blob-two"></div>
                <div class="index-cta-blob index-cta-blob-three"></div>

                <div class="card-body">
                    <div class="row align-items-center justify-content-center">
                        <div class="col-12 col-lg-5">
                            <div class="text-center text-lg-left mb-4 mb-lg-0">
                                <h2 class="h1"><?= l('index.cta.header') ?></h2>
                                <p class="h5"><?= l('index.cta.subheader') ?></p>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5 mt-4 mt-lg-0">
                            <div class="text-center text-lg-right">
                                <?php if(is_logged_in()): ?>
                                    <a href="<?= url('dashboard') ?>" class="btn btn-outline-primary zoom-animation">
                                        <?= l('dashboard.menu') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('register') ?>" class="btn btn-outline-primary zoom-animation">
                                        <?= l('index.cta.register') ?> <i class="fas fa-fw fa-arrow-right"></i>
                                    </a>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>


<?php if (!empty($data->blog_posts)): ?>
    <div class="my-5">&nbsp;</div>

    <div class="container">
        <div class="text-center mb-5">
            <h2><?= sprintf(l('index.blog.header'), '<span class="text-primary">', '</span>') ?></h2>
        </div>

        <div class="row">
            <?php foreach($data->blog_posts as $blog_post): ?>
                <div class="col-12 col-lg-4 p-4">
                    <div class="card h-100 zoom-animation-subtle position-relative">
                        <div class="card-body">
                            <?php if($blog_post->image): ?>
                                <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" aria-label="<?= $blog_post->title ?>">
                                    <img src="<?= \Altum\Uploads::get_full_url('blog') . $blog_post->image ?>" class="blog-post-image-small img-fluid w-100 rounded mb-4" alt="<?= $blog_post->image_description ?>" loading="lazy" />
                                </a>
                            <?php endif ?>

                            <a href="<?= SITE_URL . ($blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : null) . 'blog/' . $blog_post->url ?>" class="stretched-link text-decoration-none">
                                <h3 class="h5 card-title mb-2 d-inline"><?= $blog_post->title ?></h3>
                            </a>

                            <p class="text-muted mb-0"><?= $blog_post->description ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
<?php endif ?>


<?php ob_start() ?>
<link rel="stylesheet" href="<?= ASSETS_FULL_URL . 'css/libraries/aos.min.css?v=' . PRODUCT_CODE ?>">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/aos.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';
    
    AOS.init({
        duration: 600
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?= settings()->main->title ?>",
        "url": "<?= url() ?>",
    <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()}): ?>
        "logo": "<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>",
        <?php endif ?>
    "slogan": "<?= l('index.header') ?>",
        "contactPoint": {
            "@type": "ContactPoint",
            "url": "<?= url('contact') ?>",
            "contactType": "Contact us"
        }
    }
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "<?= l('index.title') ?>",
                    "item": "<?= url() ?>"
                }
            ]
        }
</script>

<?php if(settings()->main->display_index_faq): ?>
    <?php
    $faqs = [];
    foreach($faq_language_keys as $key) {
        $faqs[] = [
                '@type' => 'Question',
                'name' => l('index.faq.' . $key . '.question'),
                'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => l('index.faq.' . $key . '.answer'),
                ]
        ];
    }
    ?>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": <?= json_encode($faqs) ?>
        }
    </script>
<?php endif ?>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/index-custom.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>
