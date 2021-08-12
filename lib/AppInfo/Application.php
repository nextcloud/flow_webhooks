<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\FlowWebhooks\AppInfo;

use OCA\FlowWebhooks\Listener\RegisterChecksListener;
use OCA\FlowWebhooks\Listener\RegisterEntityListener;
use OCA\FlowWebhooks\Listener\UserDeletedListener;
use OCA\FlowWebhooks\Middleware\ProfileAccess;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserDeletedEvent;
use OCP\WorkflowEngine\Events\RegisterChecksEvent;
use OCP\WorkflowEngine\Events\RegisterEntitiesEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'flow_webhooks';

	public const CONSUMER_TYPE_INSTANCE = 'instance';
	public const CONSUMER_TYPE_USER = 'user';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(RegisterEntitiesEvent::class, RegisterEntityListener::class);
		$context->registerEventListener(RegisterChecksEvent::class, RegisterChecksListener::class);
		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);
		$context->registerMiddleware(ProfileAccess::class);
	}

	public function boot(IBootContext $context): void {
	}
}
