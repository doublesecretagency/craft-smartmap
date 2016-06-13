<?php
namespace Craft;

class SmartMapController extends BaseController
{
	protected $allowAnonymous = true;

	/*
	// TEMPORARILY DISABLED:
	// smartMap->ajaxSearch needs to be re-written
	//
	// Conduct a search via AJAX
	public function actionSearch()
	{
		$this->requireAjaxRequest();
		$params = craft()->request->getPost();
		$response = craft()->smartMap->ajaxSearch($params);
		$this->returnJson($response);
	}
	*/

	// Lookup a target location, returning full JSON
	public function actionLookup()
	{
		$this->requireAjaxRequest();
		$target = craft()->request->getPost('target');
		$components = craft()->request->getPost('components');
		$components = $this->_explodeComponents($components);
		$response = craft()->smartMap->lookup($target, $components);
		$this->returnJson($response);
	}

    // Lookup a target location, returning only coordinates of first result
	public function actionLookupCoords()
	{
		$this->requireAjaxRequest();
		$target = craft()->request->getPost('target');
		$components = craft()->request->getPost('components');
		$components = $this->_explodeComponents($components);
		$response = craft()->smartMap->lookupCoords($target, $components);
		$this->returnJson($response);
	}

	// Get location information for debugging
	public function actionDebug()
	{
		craft()->smartMap->loadGeoData();
		$templatesPath = craft()->path->getPluginsPath().'smartmap/templates/';
		craft()->path->setTemplatesPath($templatesPath);
		$this->renderTemplate('_debug', array(
			'visitor' => craft()->smartMap->visitor
		));
	}

	// Convert components string into array
	private function _explodeComponents($componentsString)
	{
		$componentsArray = array();
		if (!trim($componentsString)) {
			return $componentsArray;
		}
		$mergedComponents = explode('|', trim($componentsString));
		foreach ($mergedComponents as $keyValue) {
			$c = explode(':', $keyValue);
			$componentsArray[$c[0]] = $c[1];
		}
		return $componentsArray;
	}

}
