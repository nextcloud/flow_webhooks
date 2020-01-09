<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019 Arthur Schiwon <blizzz@arthur-schiwon.de>
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

namespace OCA\FlowHttpRequests\AppInfo;

use OCA\FlowHttpRequests\Flow\ParameterCheck;
use OCA\FlowHttpRequests\Flow\RequestEntity;
use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\WorkflowEngine\IManager;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App {
	public const APP_ID = 'flow_http_requests';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function registerComponents() {
		/** @var IEventDispatcher $eventDispatcher */
		$eventDispatcher = \OC::$server->query(IEventDispatcher::class);

		$eventDispatcher->addListener(IManager::EVENT_NAME_REG_ENTITY, function (GenericEvent $event) {
			/** @var RequestEntity $entity */
			$entity = $this->getContainer()->query(RequestEntity::class);

			/** @var IManager $flowManager */
			$flowManager = $event->getSubject();
			$flowManager->registerEntity($entity);
		});

		$eventDispatcher->addListener(IManager::EVENT_NAME_REG_CHECK, function (GenericEvent $event) {
			$check = $this->getContainer()->query(ParameterCheck::class);

			/** @var IManager $flowManager */
			$flowManager = $event->getSubject();
			$flowManager->registerCheck($check);

			\OCP\Util::addScript(self::APP_ID, self::APP_ID);
		});
	}
}
