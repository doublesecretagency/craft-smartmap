<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m150329_000000_smartMap_splitGoogleApiKeys extends BaseMigration
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

			// Validation
			$enabled = (array_key_exists('enableService', $settings) && in_array('google', $settings['enableService']));
			$apiKeyExists = array_key_exists('googleApiKey', $settings);

			// Get existing API key
			$existingKey = '';
			if ($enabled && $apiKeyExists) {
				$existingKey = $settings['googleApiKey'];
			}

			// Modify settings
			$settings['googleServerKey']  = $existingKey;
			$settings['googleBrowserKey'] = $existingKey;
			unset($settings['googleApiKey']);

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
