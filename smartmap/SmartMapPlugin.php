<?php
namespace Craft;

class SmartMapPlugin extends BasePlugin
{

	public function init()
	{
		parent::init();
		// Enums
		$this->_loadEnums();
		// Plugin Settings
		craft()->smartMap->settings = $this->getSettings();
	}

	public function getName()
	{
		return Craft::t('Smart Map');
	}

	public function getVersion()
	{
		return '2.2.1 rc';
	}

	public function getDeveloper()
	{
		return 'Double Secret Agency';
	}

	public function getDeveloperUrl()
	{
		return 'https://craftpl.us/plugins/smart-map';
		//return 'http://doublesecretagency.com';
	}

	public function registerSiteRoutes()
	{
		$debugRoute = craft()->smartMap->settings->debugRoute;
		if (!$debugRoute) {$debugRoute = 'map/debug';}
		return array(
			$debugRoute => array('action' => 'smartMap/debug'),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('smartmap/settings', array(
			'settings' => craft()->smartMap->settings
		));
	}

	protected function defineSettings()
	{
		return array(
			'googleServerKey'   => array(AttributeType::String, 'label' => 'Google API Server Key'),
			'googleBrowserKey'  => array(AttributeType::String, 'label' => 'Google API Browser Key'),
			'geolocation'       => array(AttributeType::String, 'label' => 'Geolocation Service'),
			'maxmindService'    => array(AttributeType::String, 'label' => 'MaxMind Service'),
			'maxmindUserId'     => array(AttributeType::String, 'label' => 'MaxMind User ID'),
			'maxmindLicenseKey' => array(AttributeType::String, 'label' => 'MaxMind License Key'),
			'debugRoute'        => array(AttributeType::String, 'required' => true, 'label' => 'Debug Route', 'default' => 'map/debug'),
		);
	}

	private function _loadEnums()
	{
		require('enums/MapApi.php');
		require('enums/MeasurementUnit.php');
		require('enums/ImageFormat.php');
		require('enums/MapType.php');
	}

	public function onAfterInstall()
	{
		craft()->request->redirect(UrlHelper::getCpUrl('smartmap/welcome'));
	}


	// =========================================================================== //
	// For compatibility with Import plugin

	public function registerImportOptionPaths()
	{
		return array(
			'SmartMap_Address' => 'smartmap/_importOption.html',
		);
	}

	public function modifyImportRow($element, $map, $data)
	{
		// Map data to fields
		$fields = array_combine($map, $data);

		// Initialize content array
		$content = array();

		// Loop through subfields
		foreach ($fields as $key => $value) {
			// Get handle & subfield from key
			if (preg_match('/^(.*)\[(.*)]$/', $key, $matches)) {
				$handle   = $matches[1];
				$subfield = $matches[2];
				// Ensure it's a Smart Map Address field
				$f = craft()->fields->getFieldByHandle($handle);
				if ('SmartMap_Address' == $f->fieldType->classHandle) {
					// Ensure address array exists
					if (!array_key_exists($handle, $content)) {
						$content[$handle] = array();
					}
					// Set value to subfield of correct address array
					$content[$handle][$subfield] = $value;
				}
			}
		}

		// Set new content
		$element->setContentFromPost($content);
	}

}
