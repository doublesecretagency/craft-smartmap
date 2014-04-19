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
		// Events
		craft()->on('content.saveContent', function(Event $event) {
			craft()->smartMap->contentSaved($event->params['content'], $event->params['isNewContent']);
		});
	}

	public function getName()
	{
		return Craft::t('Smart Map');
	}

	public function getVersion()
	{
		return '1.2.0';
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
        return false;
        //return craft()->smartMap->settings['showDocs'];
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
			//'showDocs' => array(AttributeType::Bool, 'label' => 'Enable documentation?', 'default' => 'on'),
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
