<?php
/**
 * Critical CSS plugin for Craft CMS 3.x
 *
 * Generate critical css without the need for ssh access
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\criticalcss\controllers;

use adigital\criticalcss\CriticalCss;

use Craft;
use craft\web\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    A Digital
 * @package   CriticalCss
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected int|bool|array $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/critical-css/default
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return 'Welcome to the DefaultController actionIndex() method';
    }

    /**
     * Handle a request going to our plugin's actionRegenerateSingle URL,
     * e.g.: actions/critical-css/default/regenerate-single
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function actionRegenerateSingle(): string
    {
        $request = Craft::$app->getRequest();
	    $url = $request->getParam('url');
	    $template = $request->getParam('template');

        return CriticalCss::$plugin->criticalCssService->letsGetCriticalCritical($url, $template);
    }

    /**
     * Handle a request going to our plugin's actionRegenerate URL,
     * e.g.: actions/critical-css/default/regenerate
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionRegenerate(): string
    {
        $result = 'Welcome to the DefaultController actionRegenerate() method';
        
        $settings = CriticalCss::$plugin->getSettings();
        if ($settings->pathConfig) {
	        foreach($settings->pathConfig as $row) {
		        $result = CriticalCss::$plugin->criticalCssService->letsGetCriticalCritical($row[0], $row[1]);
	        }
        }

        return $result;
    }

    /**
     * Handle a request going to our plugin's actionSaveCss URL,
     * e.g.: actions/critical-css/default/save-css
     *
     * @return bool
     * @throws Exception
     */
    public function actionSaveCss(): bool
    {
		$request = Craft::$app->getRequest();
	    $template = $request->getParam('template');
	    $postedCss = $request->getParam('css');
	    $stylesheets = $request->getParam('stylesheets');

        return CriticalCss::$plugin->criticalCssService->saveCritical($template, $postedCss, $stylesheets);
    }
}
