<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\CountryRegistrationBlock;

/**
 * Contains event listener callbacks.
 */
class Listener
{
    /**
     * @var bool
     */
    protected static $countryBlocked = false;

    /**
     * @param \XF\Pub\App $app
     */
    public static function appPubSetup(\XF\Pub\App $app)
    {
        $visitor = \XF::visitor();
        if ($visitor->user_id !== 0) {
            return;
        }

        $options = $app->options();

        $blockOptions = $options->jCRB;
        $geoIpHeader = $blockOptions['geoIpHeader'];
        $disallowedCountries = preg_split(
            '/\r?\n/',
            $blockOptions['disallowedCountries'],
            PREG_SPLIT_NO_EMPTY
        );
        if (!empty($_SERVER[$geoIpHeader])) {
            static::$countryBlocked = in_array(
                $_SERVER[$geoIpHeader],
                $disallowedCountries
            );
        }

        if (static::$countryBlocked) {
            \XF\Pub\App::$allowPageCache = false;
            $options->registrationSetup['enabled'] = 0;
        }
    }

    /**
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
        if ($visitor->user_id !== 0) {
            return;
        }

        if (static::$countryBlocked) {
            $noticeList->addNotice(
                'j_crb_blocked',
                'block',
                $app->templater()->renderTemplate(
                    'public:j_crb_notice_blocked',
                    $pageParams
                )
            );
        }
    }
}
