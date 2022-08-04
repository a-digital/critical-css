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

use adigital\criticalcss\assetbundles\CriticalCssUtilityUtility\CriticalCssUtilityUtilityAsset;

use Craft;
use craft\base\Utility;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

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
     * @return string|null
     */
    public static function iconPath(): ?string
    {
        return Craft::getAlias("@adigital/criticalcss/assetbundles/CriticalCssUtilityUtility/dist/img/CriticalCssUtility-icon.svg");
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws InvalidConfigException
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
