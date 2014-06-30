<?php
namespace Craft;

class SmartMapController extends BaseController
{
	protected $allowAnonymous = true;

	// Conduct a search via AJAX
	public function actionSearch()
	{
		$this->requireAjaxRequest();
		$params = craft()->request->getPost();
		$response = craft()->smartMap->ajaxSearch($params);
		$this->returnJson($response);
	}

	// Get location information for debugging
	public function actionDebug()
	{
		$templatesPath = craft()->path->getPluginsPath().'smartmap/templates/';
		craft()->path->setTemplatesPath($templatesPath);
		$this->renderTemplate('_debug', array(
			'my' => craft()->smartMap->here
		));
	}

}
