<?php
/**
 * Critical CSS plugin for Craft CMS 3.x
 *
 * Generate critical css without the need for ssh access
 *
 * @link      https://adigital.agency
 * @copyright Copyright (c) 2018 A Digital
 */

namespace adigital\criticalcss\services;

use Craft;
use craft\base\Component;
use JsonException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * CriticalCssService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    A Digital
 * @package   CriticalCss
 * @since     1.0.0
 */
class CriticalCssService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     CriticalCss::$plugin->criticalCssService->letsGetCriticalCritical()
     *
     * @param $url
     * @param $template
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function letsGetCriticalCritical($url, $template): string
    {
	    $html = file_get_contents(Craft::getAlias('@web')."/".$url);

        return Craft::$app->getView()->renderTemplate(
            'critical-css/template-test',
            compact('html', 'url', 'template')
        );
    }

    /**
     * @param $template
     * @param $postedCss
     * @param $stylesheets
     * @return bool
     * @throws Exception
     * @throws JsonException
     */
    public function saveCritical($template, $postedCss, $stylesheets): bool
    {
	    $stylesheets = json_decode($stylesheets, true, 512, JSON_THROW_ON_ERROR);
	    $postedCss = array_unique(json_decode($postedCss, true, 512, JSON_THROW_ON_ERROR));

	    $finalCss = "";
	    foreach($stylesheets as $stylesheet) {
		    $fileCss = file_get_contents($stylesheet);
            $fileCss = str_replace(["\n", "*/"], ["", "*/\n"], $fileCss);
            $fileCss = preg_replace("/\/\*(.*)\*\//", "", $fileCss);
            $fileCss = str_replace(["\n", '"', ";", "{"], ["", "'", '","', '":{"'], $fileCss);
            $fileCss = substr(str_replace("}", '"},"', $fileCss), 0, -2);
            $fileCss = str_replace([',""', '{', '},', '}'], ['', "{\n", "},\n", "\n}"], $fileCss);
            $fileCss = explode("\n", $fileCss);
			$pos = 0;
			$rules = [];
			$rule = [];
			$type = "";

			$arrKey = "";
			$nextKey = "";
			$finalKey = "";
			
			foreach($fileCss as $key => $line) {
				$up = false;
				$down = false;
				if ($line) {
					if (str_contains($line, "{")) {
						if ($pos !== 0) {
                            $line = str_replace(['": "', '":"'], ':', $line);
                        }
						$up = true;
					} elseif (str_contains($line, "}")) {
						$down = true;
					} else {
                        $line = str_replace(["[", "]", '": "', '":"'], ["{", "}", ':', ':'], $line);
                        if ($type == "array") {
							$line = str_replace(':', '": "', $line);
						}
						if (str_contains($line, "{") && !str_contains($line, "}")) {
							$up = true;
						} else if (!str_contains($line, "{") && str_contains($line, "}")) {
							$down = true;
						}
					}
					if ($type == "obj" && !str_contains($line, '":')) {
						$line = utf8_decode($line);
						$line = str_replace(':', '": "', $line);
						$jsonLine = "{".$line."}";
						if (json_decode($jsonLine, true, 512, JSON_THROW_ON_ERROR) !== NULL) {
							$line = substr(json_encode(json_decode($jsonLine, true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR), 1, -1);
						}
					}
					$fileCss[$key] = $line;
                    $line = str_replace([":{", "\""], "", $line);
                    if ($pos === 0) {
						if ($down === false) {
							$rule[$line] = [];
						}
						if ($up === true) {
							$arrKey = $line;
						}
					} elseif ($pos === 1) {
						if ($down === false) {
							$rule[$arrKey][$line] = [];
						}
						if ($up === true) {
							$nextKey = $line;
						}
					} elseif ($pos === 2) {
						if ($down === false) {
							$rule[$arrKey][$nextKey][$line] = [];
						}
						if ($up === true) {
							$finalKey = $line;
						}
					} else if ($down === false) {
                        $rule[$arrKey][$nextKey][$finalKey][$line] = [];
                    }
					if ($up === true) {
						$pos++;
					}
					if ($down === true) {
						$pos--;
					}
					if ($pos === 0) {
						$rules[] = $rule;
						$rule = [];
					}
					if (str_contains($line, "{")) {
						$type = "obj";
					} else if (str_contains($line, "[")) {
						$type = "array";
					} else {
						$type = "rule";
					}
				}
			}
			
			foreach($rules as $cssRules) {
				foreach($cssRules as $selector => $rule) {
					$css = "";
					$add = false;
					
					$commas = explode(",", $selector);
					$spaces = explode(" ", $selector);
					$fullstops = explode(".", $selector);
					foreach($postedCss as $templateSelector) {
						if (in_array($templateSelector, $commas, true) || in_array($templateSelector, $spaces, true) || in_array($templateSelector, $fullstops, true)) {
							$add = true;
						}
					}
					
					$css .= $selector."{";
					foreach($rule as $property => $value) {
						if ((is_array($value) || is_object($value)) && count($value) > 0) {
							
							$commas = explode(",", $property);
							$spaces = explode(" ", $property);
							$fullstops = explode(".", $property);
							foreach($postedCss as $templateSelector) {
								if (in_array($templateSelector, $commas, true) || in_array($templateSelector, $spaces, true) || in_array($templateSelector, $fullstops, true)) {
									$add = true;
								}
							}
							
							$css .= $property."{";
							foreach($value as $nestedProperty => $nestedValue) {
								if ((is_array($nestedValue) || is_object($nestedValue)) && count($nestedValue) > 0) {
									
									$commas = explode(",", $nestedProperty);
									$spaces = explode(" ", $nestedProperty);
									$fullstops = explode(".", $nestedProperty);
									foreach($postedCss as $templateSelector) {
										if (in_array($templateSelector, $commas, true) || in_array($templateSelector, $spaces, true) || in_array($templateSelector, $fullstops, true)) {
											$add = true;
										}
									}
									
									$css .= $nestedProperty."{";
									foreach($nestedValue as $finalProperty => $finalValue) {
										$finalProperty = explode(",", $finalProperty);
										foreach($finalProperty as $finalProp) {
											$css .= $finalProp.";";
										}
									}
									$css .= "}";
								} else {
									$nestedProperty = explode(",", $nestedProperty);
									foreach($nestedProperty as $nestedProp) {
										$css .= $nestedProp.";";
									}
								}
							}
							$css .= "}";
						} else {
							$property = explode(",", $property);
							foreach($property as $prop) {
								$css .= $prop.";";
							}
						}
					}
					$css .= "}";
					if ($add === true) {
						$finalCss .= $css;
					}
				}
			}
	    }
	    
	    $filename = Craft::$app->path->getSiteTemplatesPath()."/".$template."_critical.min.css";
	    $result = file_put_contents($filename, $finalCss);
	    if ($result !== FALSE) {
		    $result = TRUE;
	    }
	    
	    return $result;
    }
}
