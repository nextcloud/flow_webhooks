<?php

declare(strict_types=1);

namespace OCA\FlowWebhooks\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version010000Date20200820110332 extends SimpleMigrationStep {
	private $dirty = false;

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$this->ensureEndpointTable($schema);
		$this->ensureProfilesTable($schema);

		return $this->dirty ? $schema : null;
	}

	protected function ensureProfilesTable(ISchemaWrapper $schema): void {
		if($schema->hasTable('flow_webhooks_profiles'))  {
			return;
		}

		$table = $schema->createTable('flow_webhooks_profiles');
		$table->addColumn('id', Types::INTEGER,
			[
				'autoincrement' => true,
				'notnull' => true,
				'length' => 10,
				'unsigned' => true,
			]
		);
		$table->addColumn('name', Types::STRING,
			[
				'notnull' => true,
				'length' => 64,
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
		$table->addColumn('header_constraints', Types::TEXT,
			[
				'notnull' => false,
			]
		);
		$table->addColumn('param_constraints', Types::TEXT,
			[
				'notnull' => false,
			]
		);
		$table->addColumn('display_text_templates', Types::TEXT,
			[
				'notnull' => false,
			]
		);
		$table->addColumn('url_template', Types::TEXT,
			[
				'notnull' => false,
			]
		);
		$table->addColumn('icon_url_template', Types::TEXT,
			[
				'notnull' => false,
			]
		);

		$table->setPrimaryKey(['id'], 'profilesIdx');
		// a unique index on owner is not enforced on DB level to be able to
		// support multiple endpoints per consumer in a future version
		$table->addIndex(['consumer_type', 'consumer_id'], 'profilesOwnerIdx');

		$this->dirty = true;
	}

	protected function ensureEndpointTable(ISchemaWrapper $schema): void {
		if($schema->hasTable('flow_webhooks_endpoints'))  {
			return;
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

		$this->dirty = true;
	}

}
