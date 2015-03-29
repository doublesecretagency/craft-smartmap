<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m140330_000001_smartMap_autofillCountryForExistingAddresses extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$table = 'smartmap_addresses';
		$query = craft()->db->createCommand()
			->select()
			->from($table)
			->where('(country IS NULL OR country = "") AND (lat IS NOT NULL AND lng IS NOT NULL)')
		;
		$locations = $query->queryAll();
		// Loop through locations
		foreach ($locations as $address) {
			$latlng = $address['lat'].','.$address['lng'];
			$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latlng.'&sensor=false';
			$response = @file_get_contents($url);
			$json = json_decode($response, true);
			// Skip if no match found
			if ('OK' != $json['status']) {continue;}
			$components = $json['results'][0]['address_components'];
			// Skip if components can't be collected
			if (!$components) {continue;}
			// Loop through components
			foreach ($components as $c) {
				// If country component
				if (in_array('country', $c['types'])) {
					$data = array('country'=>$c['long_name']);
					$this->update($table, $data, 'id=:id', array(':id'=>$address['id']));
				}
			}
		}
		return true;
	}
}
