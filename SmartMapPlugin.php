<?php
namespace Craft;

class SmartMapPlugin extends BasePlugin
{

	private $_settings;
	
	public function init()
	{
		// Table prefix & name
		craft()->smartMap->dbPrefix    = craft()->db->tablePrefix;
		craft()->smartMap->pluginTable = 'smartmap_addresses';
		// Enums
		$this->_loadEnums();
		// Events
		craft()->on('content.saveContent', function(Event $event) {
			craft()->smartMap->contentSaved($event->params['content'], $event->params['isNewContent']);
		});
		// Plugin Settings
    	$this->_settings = $this->getSettings();
    	craft()->smartMap->mapApiKey = $this->_settings['apiKey'];
	}

	public function getName()
	{
		return Craft::t('Smart Map');
	}

	public function getVersion()
	{
		return '1.0.3';
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

    public function hasCpSection()
    {
        return $this->_settings['showDocs'];
    }

	public function getSettingsHtml()
	{
		return craft()->templates->render('smartmap/_settings', array(
			'settings' => $this->_settings
		));
	}

	protected function defineSettings()
	{
		return array(
			'showDocs' => array(AttributeType::Bool, 'label' => 'Enable documentation?', 'default' => true),
			'apiKey'   => array(AttributeType::String, 'required' => true, 'label' => 'API Key'),
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
