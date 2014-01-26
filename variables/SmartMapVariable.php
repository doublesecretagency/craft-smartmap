<?php
namespace Craft;

class SmartMapVariable
{

	// Display a dynamic Google map
	public function map($target, $options = array())
	{
		return craft()->smartMap_variables->googleMap($target, $options);
	}

	// Display a static map image
	public function img($target, $options = array())
	{
		return craft()->smartMap_variables->image($target, $options);
	}

	// Render the source for a static map image
	public function imgSrc($target, $options = array())
	{
		return craft()->smartMap_variables->imageSource($target, $options);
	}
	
}