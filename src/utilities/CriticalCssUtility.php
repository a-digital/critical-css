<?php
/**
 * Critical CSS plugin for Craft CMS 3.x
 *
 * Generate critical css without the need for ssh access
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\criticalcss\utilities;

use adigital\criticalcss\CriticalCss;
use adigital\criticalcss\assetbundles\criticalcssutilityutility\CriticalCssUtilityUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * Critical CSS Utility
 *
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * https://craftcms.com/docs/plugins/utilities
 *
 * @author    A Digital
 * @package   CriticalCss
 * @since     1.0.0
 */
class CriticalCssUtility extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('critical-css', 'CriticalCssUtility');
    }

    /**
     * Returns the utility’s unique identifier.
     *
     * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
     *
     * @return string
     */
    public static function id(): string
    {
        return 'criticalcss-critical-css-utility';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@adigital/criticalcss/assetbundles/criticalcssutilityutility/dist/img/CriticalCssUtility-icon.svg");
    }

    /**
     * Returns the number that should be shown in the utility’s nav item badge.
     *
     * If `0` is returned, no badge will be shown
     *
     * @return int
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(CriticalCssUtilityUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'critical-css/_components/utilities/CriticalCssUtility_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}
