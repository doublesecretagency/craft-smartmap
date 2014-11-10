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
		return '2.0.10';
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
		return craft()->templates->render('smartmap/_settings', array(
			'settings' => craft()->smartMap->settings
		));
	}

	protected function defineSettings()
	{
		return array(
			'debugRoute'        => array(AttributeType::String, 'required' => true, 'label' => 'Debug Route', 'default' => 'map/debug'),
			'enableService'     => array(AttributeType::Mixed,  'label' => 'Enable Optional Service(s)'),
			'googleApiKey'      => array(AttributeType::String, 'label' => 'API Key'),
			'maxmindUserId'     => array(AttributeType::String, 'label' => 'Max Mind User ID'),
			'maxmindLicenseKey' => array(AttributeType::String, 'label' => 'Max Mind License Key'),
			'maxmindService'    => array(AttributeType::String, 'label' => 'Max Mind Service'),
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
		craft()->request->redirect(UrlHelper::getCpUrl('smartmap/thanks'));
	}

}
