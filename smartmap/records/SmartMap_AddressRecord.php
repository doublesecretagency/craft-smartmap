<?php
namespace Craft;

class SmartMap_AddressRecord extends BaseRecord
{

    const TABLE_NAME = 'smartmap_addresses';

    public function getTableName()
    {
        return static::TABLE_NAME;
    }

    protected function defineAttributes()
    {

        // Creates SQL column of "decimal(12,8)"
        $coordColumn = array(
            AttributeType::Number,
            'column'   => ColumnType::Decimal,
            'length'   => 12,
            'decimals' => 8,
        );

        return array(
            'street1' => AttributeType::String,
            'street2' => AttributeType::String,
            'city'    => AttributeType::String,
            'state'   => AttributeType::String,
            'zip'     => AttributeType::String,
            'country' => AttributeType::String,
            'lat'     => $coordColumn,
            'lng'     => $coordColumn,
        );

    }

    public function defineRelations()
    {
        return array(
            'element' => array(static::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => static::CASCADE),
            'field'   => array(static::BELONGS_TO, 'FieldRecord',   'required' => true, 'onDelete' => static::CASCADE),
        );
    }

}