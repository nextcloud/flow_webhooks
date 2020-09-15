<?php

declare(strict_types=1);

namespace OCA\FlowWebhooks\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCA\WorkflowEngine\AppInfo\Application;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version010000Date20200820110332 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if($schema->hasTable('flow_webhooks_endpoints'))  {
			return null;
		}

		$table = $schema->createTable('flow_webhooks_endpoints');
		$table->addColumn('id', Types::INTEGER,
			[
				'autoincrement' => true,
				'notnull' => true,
				'length' => 10,
				'unsigned' => true,
			]
		);
		$table->addColumn('endpoint', Types::STRING,
			[
				'notnull' => true,
				'length' => 10,
			]
		);
		$table->addColumn('consumer_type', Types::STRING,
			[
				'notnull' => true,
				'length' => 20,
				'default' => \OCA\FlowWebhooks\AppInfo\Application::CONSUMER_TYPE_USER
			]
		);
		$table->addColumn('consumer_id', Types::STRING,
			[
				'notnull' => false,
				'length' => 128,
				'default' => ''
			]
		);
		$table->setPrimaryKey(['id'], 'flow_wh_pri');
		$table->addUniqueIndex(['endpoint'], 'flow_wh_endpoints');
		$table->addIndex(['consumer_type', 'consumer_id'], 'flow_wh_consumer');

		return $schema;
	}

}
