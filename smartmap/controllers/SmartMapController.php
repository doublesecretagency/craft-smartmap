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
		$response = craft()->smartMap->lookup($target);
		$this->returnJson($response);
	}

	// Get location information for debugging
	public function actionDebug()
	{
		craft()->smartMap->loadGeoData();
		$templatesPath = craft()->path->getPluginsPath().'smartmap/templates/';
		craft()->path->setTemplatesPath($templatesPath);
		$this->renderTemplate('_debug', array(
			'my' => craft()->smartMap->here
		));
	}

}
