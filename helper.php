<?php
/**
 * @version    1-1-0-0 // Y-m-d 2017-04-06
 * @author     HR IT-Solutions Florian Häusler https://www.hr-it-solutions.com
 * @copyright  Copyright (C) 2011 - 2017 Didldu e.K. | HR IT-Solutions
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 **/

defined('_JEXEC') or die;

/**
 * Helper for mod_dd_gmaps_module
 *
 * @since  Version 1.0.0.0
 */
class ModDD_GMaps_Module_Helper
{

	protected $params;

	/**
	 * getItems
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	public function isDDGMapsLocationsExtended()
	{
		// If DD GMaps Locations
		if (JComponentHelper::getComponent('com_dd_gmaps_locations', true)->enabled
			&& JFactory::getApplication()->input->get('option') == 'com_dd_gmaps_locations')
		{
			return true;
		}

		return false;
	}

	/**
	 * getItems
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	public function getItems()
	{
		if ($this->isDDGMapsLocationsExtended())
		{
			$items = $this->getDDGMapsLocatiosItems();
		}
		else
		{
			$items = $this->getItem();
		}

		return $items;
	}

	/**
	 * Addon Module > Extension Access
	 *
	 * Get DD GMaps Locations Items
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	protected function getDDGMapsLocatiosItems()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$select = $db->qn(
			array(
				'a.id',
				'a.title',
				'a.alias',
				'a.catid',
				'a.state',
				'a.profileimage',
				'a.company',
				'a.street',
				'a.location',
				'a.zip',
				'a.country',
				'a.federalstate',
				'a.latitude',
				'a.longitude'
			)
		);

		$query->select($select)->from($db->qn('#__dd_gmaps_locations', 'a'));

		// Filter state
		$query->where('a.state = 1');

		// Join over categories
		$query->select($db->qn('c.title', 'category_title'))
			->leftJoin($db->qn('#__categories', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('a.catid') . ')');

		$query->order('a.id DESC');

		return $db->setQuery($query)->loadObjectList();
	}

	/**
	 * Get DDG Maps Maps Item
	 *
	 * @since Version 1.0.0.0
	 *
	 * @return mixed
	 */
	protected function getItem()
	{
		$return = array();
		$return[0] = new stdClass;

		$module = JModuleHelper::getModule('mod_dd_gmaps_module');
		$params = new JRegistry($module->params);

		$return[0]->category_title = 'standalone';
		$return[0]->alias          = '';

		$return[0]->title          = $params->get('location_name', '');
		$return[0]->profileimage   = $params->get('location_image', '');
		$return[0]->street         = $params->get('street', '');
		$return[0]->location       = $params->get('location', '');
		$return[0]->zip            = $params->get('zip', '');
		$return[0]->country        = $params->get('country', '');

		// Get latitude and longitude
		$latlng = $this->Geocode_Location_To_LatLng($return, $params->get('google_api_key_geocode'));

		// Set latitude and longitude
		$params->set('latitude', $latlng['latitude']);
		$params->set('longitude', $latlng['longitude']);

		$return[0]->latitude   = $latlng['latitude'];
		$return[0]->longitude  = $latlng['longitude'];

		return $return;
	}

	/**
	 * Get latitude and longitude by address from Google GeoCode API
	 *
	 * @param   mixed   $data                    The form data which must include 'street' 'zip' 'location' 'federalstate' and 'country'
	 * @param   string  $google_api_key_geocode  GeoCode API code
	 *
	 * @return  array   latitude and longitude
	 *
	 * @since   Version 1.1.0.0
	 */
	protected function Geocode_Location_To_LatLng($data, $google_api_key_geocode)
	{
		// Get Location Data
		$address = array(
			'street'        => $data[0]->street,
			'zip'           => $data[0]->zip ,
			'location'      => $data[0]->location,
			'country'       => JText::_($data[0]->country) // Convert language string to country name
		);

		// Get API Key if key is set
		$google_api_URL_pram = '';

		if ($google_api_key_geocode)
		{
			$google_api_URL_pram    = '&key=' . trim($google_api_key_geocode);
		}
		// Prepare Address
		$prepAddr = implode('+', $address);

		// Get Contents and decode
		$geoCode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($prepAddr) . '&sensor=false' . $google_api_URL_pram);
		$output  = json_decode($geoCode);

		if (@$output->error_message != "") // If Error on API Connection, display error not
		{
			JFactory::getApplication()->enqueueMessage($output->error_message, 'Note');

			return false;
		}

		// Build array latitude and longitude
		$latlng = array("latitude"  => $output->results[0]->geometry->location->lat,
						"longitude" => $output->results[0]->geometry->location->lng);

		// Return Array
		return $latlng;
	}

	/**
	 * isset_Script checks if a subString src exists in script header
	 *
	 * @param   array   $doc_scripts  JFactory Document $doc->_scripts
	 * @param   string  $subString    Substring to check
	 *
	 * @return  boolean
	 *
	 * @since   Version 1.1.0.0
	 */
	public static function isset_Script($doc_scripts, $subString)
	{
		$return = false;

		foreach ($doc_scripts as $key => $value)
		{
			$pos = strpos($key, $subString);

			if ($pos === false)
			{
				$return = false;
			}
			else
			{
				// String found in key
				$return = true;
				break;
			}
		}

		return $return;
	}
}
