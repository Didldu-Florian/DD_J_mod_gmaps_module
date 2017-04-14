<?php
/**
 * @version    1-1-0-0 // Y-m-d 2017-04-06
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$doc = JFactory::getDocument();

// Include the functions only once
JLoader::register('ModDD_GMaps_Module_Helper', __DIR__ . '/helper.php');

$google_PlacesAPI = 'js?&libraries=places&v=3';
$google_PlacesAPI_Key = '&key=' . $params->get('google_api_key_js_places','');

if(!ModDD_GMaps_Module_Helper::isset_Script($doc->_scripts, $google_PlacesAPI))
{
	$doc->addScript('https://maps.google.com/maps/api/' . $google_PlacesAPI . '&key=' . $google_PlacesAPI_Key);
}

$doc->addScript(JUri::base() . 'media/mod_dd_gmaps_module/js/markerclusterer_compiled.min.js');
$doc->addScript(JUri::base() . 'media/mod_dd_gmaps_module/js/dd_gmaps_module.min.js');
$doc->addStyleSheet(JUri::base() . 'media/mod_dd_gmaps_module/css/dd_gmaps_module.min.css');

require JModuleHelper::getLayoutPath('mod_dd_gmaps_module', $params->get('layout', 'default'));
