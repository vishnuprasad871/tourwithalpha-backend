<?php
/**
 * Tourwithalpha BookingCount Module
 * Upgrade schema to add offline bookings table
 */

declare(strict_types=1);

namespace Tourwithalpha\BookingCount\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade schema to create tourwithalpha_offline_bookings table for existing module
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade DB schema
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer = $setup;
        $installer->startSetup();

        // Create offline bookings table for versions before 1.2.0
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $tableName = $installer->getTable('tourwithalpha_offline_bookings');

            if (!$installer->tableExists($tableName)) {
                $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Record ID'
                    )
                    ->addColumn(
                        'sku',
                        Table::TYPE_TEXT,
                        64,
                        ['nullable' => false],
                        'Product SKU'
                    )
                    ->addColumn(
                        'booking_date',
                        Table::TYPE_DATE,
                        null,
                        ['nullable' => false],
                        'Booking Date'
                    )
                    ->addColumn(
                        'qty',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false, 'default' => 1],
                        'Offline Booking Quantity'
                    )
                    ->addColumn(
                        'notes',
                        Table::TYPE_TEXT,
                        '64k',
                        ['nullable' => true],
                        'Admin Notes'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Created At'
                    )
                    ->addColumn(
                        'updated_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                        'Updated At'
                    )
                    ->addIndex(
                        $installer->getIdxName($tableName, ['sku']),
                        ['sku']
                    )
                    ->addIndex(
                        $installer->getIdxName($tableName, ['booking_date']),
                        ['booking_date']
                    )
                    ->addIndex(
                        $installer->getIdxName($tableName, ['sku', 'booking_date']),
                        ['sku', 'booking_date']
                    )
                    ->setComment('Tourwithalpha Offline Bookings');

                $installer->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }
}
