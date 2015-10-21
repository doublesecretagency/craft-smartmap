<?php
namespace Craft;

class SmartMap_ModalController extends BaseController
{
	protected $allowAnonymous = true;

	// Load modal template for address search results
	public function actionAddressSearch()
	{
		$this->requireAjaxRequest();

		$address = craft()->request->getPost('address');
		if (!trim($address)) {
			$this->returnJson(array(
				'success' => false,
				'message' => Craft::t('No address provided. Please enter a partial address to search for.')
			));
		}

		$response = craft()->smartMap_main->addressSearch($address);
		if ($response['success']) {
			$this->renderTemplate('smartmap/_modals/address-search-results', array(
				'searchResults' => $response['results'],
			));
		} else {
			// Flash error message?
			//craft()->userSession->setError($response['message']);
			$this->returnJson($response);
		}

		//$response = craft()->smartMap->ajaxSearch($params);
		//$this->returnJson($response);
	}

	// Load modal template for drag & drop pin
	public function actionDragPin()
	{
		$this->requireAjaxRequest();
		$this->renderTemplate('smartmap/_modals/drag-pin', array(
			'coords' => craft()->request->getPost('coords'),
		));
	}

}
