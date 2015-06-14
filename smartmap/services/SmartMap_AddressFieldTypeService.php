<?php
namespace Craft;

class SmartMap_AddressFieldTypeService extends BaseApplicationComponent
{

	// Get the subfield label from its handle
	public function labelFromHandle($handle)
	{
		switch ($handle) {
			case 'street1': $label = 'Street Address';     break;
			case 'street2': $label = 'Apartment or Suite'; break;
			case 'city':    $label = 'City';               break;
			case 'state':   $label = 'State';              break;
			case 'zip':     $label = 'Zip Code';           break;
			case 'country': $label = 'Country';            break;
			case 'lat':     $label = 'Latitude';           break;
			case 'lng':     $label = 'Longitude';          break;
		}
		return Craft::t($label);
	}

}
