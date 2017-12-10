<?php

/*
 * This file is part of a XenForo add-on.
 *
 * (c) Jeremy P <https://xenforo.com/community/members/jeremy-p.450/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\CountryRegistrationBlock;

/**
 * Contains event listener callbacks.
 *
 * @author Jeremy P <https://xenforo.com/community/members/jeremy-p.450/>
 */
class Listener
{
    /**
     * A flag indicating whether or not the visitor's country is blocked from
     * registering.
     *
     * @var bool
     */
    protected static $countryBlocked = false;

    /**
     * Disables registration for guest users visiting from a blocked country.
     *
     * @param \XF\Pub\App $app
     */
    public static function appPubSetup(\XF\Pub\App $app)
    {
        $visitor = \XF::visitor();

        if ($visitor->user_id === 0) {
            $options = $app->options();

            $blockOptions = $options->jCRB;
            $geoIpHeader = $blockOptions['geoIpHeader'];
            $disallowedCountries = $blockOptions['disallowedCountries'];

            if (!empty($_SERVER[$geoIpHeader])) {
                $country = $_SERVER[$geoIpHeader];
                $blockedCountries = preg_split(
                    '/\r?\n/',
                    $disallowedCountries,
                    PREG_SPLIT_NO_EMPTY
                );

                static::$countryBlocked = in_array($country, $blockedCountries);
            }

            if (static::$countryBlocked) {
                $options->registrationSetup['enabled'] = 0;
            }
        }
    }

    /**
     * Appends a notice to the notice list if a guest user is visiting from a
     * blocked country.
     *
     * @param \XF\Pub\App    $app
     * @param \XF\NoticeList $noticeList
     * @param array          $pageParams
     */
    public static function noticesSetup(
        \XF\Pub\App $app,
        \XF\NoticeList $noticeList,
        array $pageParams
    ) {
        $visitor = \XF::visitor();

        if (($visitor->user_id === 0) and static::$countryBlocked) {
            $templater = $app->templater();

            $noticeList->addNotice(
                'j_crb_blocked',
                'block',
                $templater->renderTemplate(
                    'public:j_crb_notice_blocked',
                    $pageParams
                )
            );
        }
    }
}
