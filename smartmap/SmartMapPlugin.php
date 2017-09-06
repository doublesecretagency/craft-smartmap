<?php
namespace Craft;

class SmartMapPlugin extends BasePlugin
{

	// Collection of valid address fields for export
	private $_exportValidAddressFields = array();

	public function init()
	{
		parent::init();
		// Enums
		$this->_loadEnums();
		// Plugin Settings
		craft()->smartMap->settings = $this->getSettings();
		// Feed Me integration
		Craft::import('plugins.smartmap.integrations.feedme.fields.SmartMap_AddressFeedMeFieldType');
	}

	public function getName()
	{
		return Craft::t('Smart Map');
	}

	public function getDescription()
	{
		return 'Adds a powerful "Address" field, to easily manage locations.';
	}

	public function getVersion()
	{
		return '2.4.1';
	}

	public function getSchemaVersion()
	{
		return '2.3.0';
	}

	public function getDeveloper()
	{
		return 'Double Secret Agency';
	}

	public function getDeveloperUrl()
	{
		return 'https://www.doublesecretagency.com/plugins';
	}

	public function getDocumentationUrl()
	{
		return 'https://www.doublesecretagency.com/plugins/smart-map/docs';
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
	// For compatibility with Feed Me plugin (v2.x)

	public function registerFeedMeFieldTypes()
	{
		return array(
			new SmartMap_AddressFeedMeFieldType(),
		);
	}


	// =========================================================================== //
	// For compatibility with Feed Me plugin (v1.3.7)

	public function registerFeedMeMappingOptions()
	{
		return array(
			'SmartMap_Address' => 'smartmap/_plugins/feedMeOptions',
		);
	}

	public function prepForFeedMeFieldType($field, &$data, $handle)
	{
		// Ensure it's a Smart Map Address field
		if ($field->type == 'SmartMap_Address') {

			// Initialize content array
			$content = array();

			// Separate field handle & subfield handle
			if (preg_match('/^(.*)\[(.*)]$/', $handle, $matches)) {
				$fieldHandle    = $matches[1];
				$subfieldHandle = $matches[2];
				// Ensure address array exists
				if (!array_key_exists($fieldHandle, $content)) {
					$content[$fieldHandle] = array();
				}
				// Set value to subfield of correct address array
				$content[$fieldHandle][$subfieldHandle] = $data[$fieldHandle];
			}

			// Modify data
			$data = $content;
		}
	}

	// =========================================================================== //
	// For compatibility with Import plugin (v0.8.26)

	public function registerImportOptionPaths()
	{
		return array(
			'SmartMap_Address' => 'smartmap/_plugins/importOptions',
		);
	}

	public function modifyImportRow($element, $map, $data)
	{
		// Initialize content array
		$content = array();

		// Map data to fields
		$fields = array_combine($map, $data);

		// Log import attempt
		SmartMapPlugin::log('Importing address data: '.var_export($fields, true), LogLevel::Info);

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

	// =========================================================================== //
	// For compatibility with Export plugin (v0.5.8)

	public function registerExportTableRowPaths()
	{
		return array(
			'SmartMap_Address' => 'smartmap/_plugins/exportTableRow',
		);
	}

	public function modifyExportAttributes(&$attributes, BaseElementModel $element)
	{
		// Loop through attributes
		foreach ($attributes as $handle => $value) {

			// Separate at "."
			$handleParts = explode('.', $handle);

			// If multiple parts
			if (1 < count($handleParts)) {

				// Set real handle and subfield
				$realHandle = $handleParts[0];
				$subfield   = $handleParts[1];

				// If field type not already validated
				if (!array_key_exists($handle, $this->_exportValidAddressFields)) {

					// Invalid by default
					$valid = false;

					// Get field model
					$field = craft()->fields->getFieldByHandle($realHandle);

					// If field exists
					if ($field) {

						// Set validity of whether it's a Smart Map Address field or not
						$valid = ('SmartMap_Address' == $field->type);

					}

					// Add to validation array
					$this->_exportValidAddressFields[$handle] = $valid;
				}

				// If valid address field
				if ($this->_exportValidAddressFields[$handle]) {

					// Set proper value for attribute
					$attributes[$handle] = $element->$realHandle->$subfield;
				}
			}
		}
	}

	// =========================================================================== //

}
