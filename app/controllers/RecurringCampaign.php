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

use Altum\Title;

defined('ALTUMCODE') || die();

class RecurringCampaign extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $recurring_campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$recurring_campaign = db()->where('recurring_campaign_id', $recurring_campaign_id)->where('user_id', $this->user->user_id)->getOne('recurring_campaigns')) {
            redirect('rss-automations');
        }

        $recurring_campaign->settings = json_decode($recurring_campaign->settings ?? '');

        /* Get the sms list for the user */
        $sms = db()->where('recurring_campaign_id', $recurring_campaign->recurring_campaign_id)
            ->join('contacts', 'sms.contact_id = contacts.contact_id', 'LEFT')
            ->orderBy('sms_id', 'DESC')
            ->get('sms', 5, 'sms.*, contacts.phone_number, contacts.country_code, contacts.has_opted_out, contacts.name');

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Set a custom title */
        Title::set(sprintf(l('recurring_campaign.title'), $recurring_campaign->name));

        /* Prepare the view */
        $data = [
            'recurring_campaign' => $recurring_campaign,
            'sms' => $sms,
            'devices' => $devices,
        ];

        $view = new \Altum\View('recurring-campaign/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
