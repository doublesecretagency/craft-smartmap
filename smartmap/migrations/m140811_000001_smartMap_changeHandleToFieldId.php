<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m140811_000001_smartMap_changeHandleToFieldId extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$table = 'smartmap_addresses';
		// Create foreign key
		$this->addColumnAfter($table, 'fieldId', ColumnType::Int, 'elementId');
		$this->addForeignKey($table, 'fieldId', 'fields', 'id', 'CASCADE', 'CASCADE');
		// Get all Address fields
		$query = craft()->db->createCommand()
			->select()
			->from('fields')
			->where('type = "SmartMap_Address"')
		;
		$fields = $query->queryAll();
		// Update existing Address data
		foreach ($fields as $field) {
			$newFieldId = array('fieldId'=>$field['id']);
			$this->update($table, $newFieldId, 'handle=:handle', array(':handle'=>$field['handle']));
		}
		// Remove old column
		$this->dropColumn($table, 'handle');
		// Finish
		return true;
	}
}
