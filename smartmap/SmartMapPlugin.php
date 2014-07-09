<?php
namespace Craft;

class SmartMapPlugin extends BasePlugin
{

	private $_settings;
	
	public function init()
	{
		parent::init();
		// Enums
		$this->_loadEnums();
		// Plugin Settings
		craft()->smartMap->settings  = $this->getSettings();
		craft()->smartMap->mapApiKey = '';
		//craft()->smartMap->mapApiKey = craft()->smartMap->settings['apiKey'];
		craft()->smartMap->loadGeoData();
	}

	public function getName()
	{
		return Craft::t('Smart Map');
	}

	public function getVersion()
	{
		return '1.3.1';
	}

	public function getDeveloper()
	{
		return 'Double Secret Agency';
	}

	public function getDeveloperUrl()
	{
		return 'https://github.com/lindseydiloreto/craft-smartmap';
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
		return craft()->templates->render('smartmap/_settings', array(
			'settings' => craft()->smartMap->settings
		));
	}

	protected function defineSettings()
	{
		return array(
			'maxmindUserId'     => array(AttributeType::String, 'label' => 'Max Mind User ID'),
			'maxmindLicenseKey' => array(AttributeType::String, 'label' => 'Max Mind License Key'),
			'maxmindService'    => array(AttributeType::String, 'label' => 'Max Mind Service'),
			'debugRoute'        => array(AttributeType::String, 'required' => true, 'label' => 'Debug Route', 'default' => 'map/debug'),
			//'apiKey'   => array(AttributeType::String, 'required' => true, 'label' => 'API Key'),
		);
	}

	private function _loadEnums()
	{
		require('enums/MapApi.php');
		require('enums/MeasurementUnit.php');
		require('enums/ImageFormat.php');
		require('enums/MapType.php');
	}
	
}
