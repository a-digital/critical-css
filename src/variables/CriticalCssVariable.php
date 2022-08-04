<?php
/**
 * Critical CSS plugin for Craft CMS 3.x
 *
 * Generate critical css without the need for ssh access
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\criticalcss\variables;

use adigital\criticalcss\CriticalCss;

/**
 * Critical CSS Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.criticalCss }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    A Digital
 * @package   CriticalCss
 * @since     1.0.0
 */
class CriticalCssVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.criticalCss.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.criticalCss.exampleVariable(twigValue) }}
     *
     * @return array|bool
     */
    public function getConfigSettings(): array|bool
    {
        $settings = CriticalCss::$plugin->getSettings();
        if ($settings->pathConfig) {
	        return $settings->pathConfig;
        }
        return false;
    }
}
