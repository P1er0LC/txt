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

class Campaign extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $campaign_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$campaign = db()->where('campaign_id', $campaign_id)->where('user_id', $this->user->user_id)->getOne('campaigns')) {
            redirect('campaigns');
        }

        $campaign->settings = json_decode($campaign->settings ?? '');

        /* Get the sms list for the user */
        $sms = db()->where('campaign_id', $campaign->campaign_id)
            ->join('contacts', 'sms.contact_id = contacts.contact_id', 'LEFT')
            ->orderBy('sms_id', 'DESC')
            ->get('sms', 5, 'sms.*, contacts.phone_number, contacts.country_code, contacts.has_opted_out, contacts.name');

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Set a custom title */
        Title::set(sprintf(l('campaign.title'), $campaign->name));

        /* Prepare the view */
        $data = [
            'campaign' => $campaign,
            'sms' => $sms,
            'devices' => $devices,
        ];

        $view = new \Altum\View('campaign/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
