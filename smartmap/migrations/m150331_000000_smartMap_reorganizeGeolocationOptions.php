<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m150331_000000_smartMap_reorganizeGeolocationOptions extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Get settings
		$settings = $this->_getSettings('SmartMap');

		// If settings already exist
		if (is_array($settings)) {

			// Default geolocation service
			$service = 'none';

			// If "enableService" value exists
			if (array_key_exists('enableService', $settings)) {

				// Get currently enabled
				$enabled = $settings['enableService'];

				// If geolocation is enabled
				if (in_array('geolocation', $enabled)) {

					// If MaxMind is not enabled, default to FreeGeoIp.net
					if (in_array('maxmind', $enabled)) {
						$service = 'maxmind';
					} else {
						$service = 'freegeoip';
					}

				}

			}

			// Set geolocation selection
			$settings['geolocation'] = $service;

			// Remove "enableService" value
			unset($settings['enableService']);

			// Save settings
			$this->_setSettings('SmartMap', $settings);
		}

		// Return true
		return true;
	}

	/**
	 * Get plugin settings
	 *
	 * @param string $class Plugin class
	 *
	 * @return array
	 */
	private function _getSettings($class)
	{
		// Get original settings value
		$query = craft()->db->createCommand()
			->select('settings')
			->from('plugins')
			->where('class="'.$class.'"')
		;
		$oldSettings = $query->queryRow();
		return @json_decode($oldSettings['settings'], true);
	}

	/**
	 * Save plugin settings
	 *
	 * @param string $class    Plugin class
	 * @param array  $settings Updated settings
	 *
	 * @return array
	 */
	private function _setSettings($class, $settings)
	{
		// Update settings field
		$newSettings = json_encode($settings);
		$this->update('plugins', array('settings'=>$newSettings), 'class="'.$class.'"');
	}

}
