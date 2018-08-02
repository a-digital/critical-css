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

use adigital\criticalcss\CriticalCss;

use Craft;
use craft\base\Component;

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
     * @return mixed
     */
    public function letsGetCriticalCritical($url, $template)
    {
	    $result = "";
	    
	    $html = file_get_contents(Craft::getAlias('@web')."/".$url);
	    
	    $rendered = Craft::$app->getView()->renderTemplate(
	        'critical-css/template-test',
	        [
	            'html' => $html,
	            'url' => $url,
	            'template' => $template
	        ]
        );
        
        return $rendered;
    }
    
    public function saveCritical($template, $postedCss, $stylesheets)
    {
	    $stylesheets = json_decode($stylesheets);
	    $postedCss = array_unique(json_decode($postedCss));
	    
	    $cssFiles = [];
	    $mainCss = [];
	    $finalCss = "";
	    foreach($stylesheets as $stylesheet) {
		    $fileCss = file_get_contents($stylesheet);
		    $fileCss = str_replace("\n", "", $fileCss);
		    $fileCss = str_replace("*/", "*/\n", $fileCss);
		    $fileCss = preg_replace("/\/\*(.*)\*\//", "", $fileCss);
		    $fileCss = str_replace("\n", "", $fileCss);
		    $fileCss = str_replace('"', "'", $fileCss);
		    
		    $fileCss = str_replace(";", '","', $fileCss);
			$fileCss = str_replace("{", '":{"', $fileCss);
			$fileCss = substr(str_replace("}", '"},"', $fileCss), 0, -2);
			$fileCss = str_replace(',""', '', $fileCss);
			$fileCss = str_replace('{', "{\n", $fileCss);
			$fileCss = str_replace('},', "},\n", $fileCss);
			$fileCss = str_replace('}', "\n}", $fileCss);
			$fileCss = explode("\n", $fileCss);
			$pos = 0;
			$rules = [];
			$rule = [];
			$key = "";
			$type = "";
			
			$computedCss = [];
			$arrKey = "";
			$nextKey = "";
			$finalKey = "";
			
			foreach($fileCss as $key => $line) {
				$up = false;
				$down = false;
				if ($line) {
					if (strpos($line, "{") !== false) {
						if ($pos !== 0) {
							$line = str_replace('": "', ':', $line);
							$line = str_replace('":"', ':', $line);
						}
						$up = true;
					} elseif (strpos($line, "}") !== false) {
						$down = true;
					} else {
						$line = str_replace("[", "{", $line);
						$line = str_replace("]", "}", $line);
						$line = str_replace('": "', ':', $line);
						$line = str_replace('":"', ':', $line);
						if ($type == "array") {
							$line = str_replace(':', '": "', $line);
						}
						if (strpos($line, "{") !== false && strpos($line, "}") === false) {
							$up = true;
						} else if (strpos($line, "{") === false && strpos($line, "}") !== false) {
							$down = true;
						}
					}
					if ($type == "obj" && strpos($line, '":') === false) {
						$line = utf8_decode($line);
						$line = str_replace(':', '": "', $line);
						$jsonLine = "{".$line."}";
						if (json_decode($jsonLine) !== NULL) {
							$line = substr(json_encode(json_decode($jsonLine)), 1, -1);
						}
					}
					if (array_key_exists($key, $computedCss)) {
						array_merge($computedCss[$key], $line);
					} else {
						$computedCss[$key] = $line;
					}
					$fileCss[$key] = $line;
					$line = str_replace(":{", "", $line);
					$line = str_replace("\"", "", $line);
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
					} else {
						if ($down === false) {
							$rule[$arrKey][$nextKey][$finalKey][$line] = [];
						}
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
					if (strpos($line, "{") !== false) {
						$type = "obj";
					} else if (strpos($line, "[") !== false) {
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
						if (in_array($templateSelector, $commas) || in_array($templateSelector, $spaces) || in_array($templateSelector, $fullstops)) {
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
								if (in_array($templateSelector, $commas) || in_array($templateSelector, $spaces) || in_array($templateSelector, $fullstops)) {
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
										if (in_array($templateSelector, $commas) || in_array($templateSelector, $spaces) || in_array($templateSelector, $fullstops)) {
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
