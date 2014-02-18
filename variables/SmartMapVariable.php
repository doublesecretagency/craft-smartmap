<?php
namespace Craft;

class SmartMapVariable
{

	// Display a dynamic Google map
	public function map($markers = false, $options = array())
	{
		return craft()->smartMap_variables->googleMap($markers, $options);
	}

	// Display a static map image
	public function img($markers, $options = array())
	{
		return craft()->smartMap_variables->staticImg($markers, $options);
	}

	// Render the source for a static map image
	public function imgSrc($markers, $options = array())
	{
		return craft()->smartMap_variables->staticImgSource($markers, $options);
	}
	
}