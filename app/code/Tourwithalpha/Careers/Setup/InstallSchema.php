<?php
/**
 * Tourwithalpha Careers Module
 * Database schema installation
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create tourwithalpha_careers and tourwithalpha_job_applications tables
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $installer = $setup;
        $installer->startSetup();

        $this->createCareersTable($installer);
        $this->createJobApplicationsTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createCareersTable(SchemaSetupInterface $installer): void
    {
        $tableName = $installer->getTable('tourwithalpha_careers');

        if ($installer->getConnection()->isTableExists($tableName)) {
            return;
        }

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Career ID'
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Job Title')
            ->addColumn('department', Table::TYPE_TEXT, 100, ['nullable' => false], 'Department')
            ->addColumn('location', Table::TYPE_TEXT, 255, ['nullable' => false], 'Job Location')
            ->addColumn('employment_type', Table::TYPE_TEXT, 50, ['nullable' => false, 'default' => 'full_time'], 'Employment Type')
            ->addColumn('description', Table::TYPE_TEXT, '64k', ['nullable' => false], 'Job Description')
            ->addColumn('requirements', Table::TYPE_TEXT, '64k', ['nullable' => false], 'Job Requirements')
            ->addColumn('salary_range', Table::TYPE_TEXT, 100, ['nullable' => true], 'Salary Range')
            ->addColumn(
                'is_active',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1],
                'Is Active'
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
            ->addIndex($installer->getIdxName($tableName, ['is_active']), ['is_active'])
            ->setComment('Tourwithalpha Career Listings');

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createJobApplicationsTable(SchemaSetupInterface $installer): void
    {
        $tableName = $installer->getTable('tourwithalpha_job_applications');

        if ($installer->getConnection()->isTableExists($tableName)) {
            return;
        }

        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Application ID'
            )
            ->addColumn(
                'career_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Career Listing ID'
            )
            ->addColumn('first_name', Table::TYPE_TEXT, 100, ['nullable' => false], 'First Name')
            ->addColumn('last_name', Table::TYPE_TEXT, 100, ['nullable' => false], 'Last Name')
            ->addColumn('email', Table::TYPE_TEXT, 255, ['nullable' => false], 'Email Address')
            ->addColumn('phone', Table::TYPE_TEXT, 30, ['nullable' => true], 'Phone Number')
            ->addColumn('cover_letter', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Cover Letter')
            ->addColumn('resume_path', Table::TYPE_TEXT, 512, ['nullable' => true], 'Resume File Path')
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false, 'default' => 'new'],
                'Application Status'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex($installer->getIdxName($tableName, ['career_id']), ['career_id'])
            ->addIndex($installer->getIdxName($tableName, ['email']), ['email'])
            ->setComment('Tourwithalpha Job Applications');

        $installer->getConnection()->createTable($table);
    }
}
