<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m140330_000000_smartMap_addCountrySubfield extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$table = 'smartmap_addresses';
		$this->addColumnAfter($table, 'country', ColumnType::Varchar, 'zip');
		return true;
	}
}
