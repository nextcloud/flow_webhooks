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

namespace OCA\FlowWebhooks\Listener;

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Service\Endpoint;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserDeletedEvent;

class UserDeletedListener implements IEventListener {

	/** @var Endpoint */
	private $endpoint;

	public function __construct(Endpoint $endpoint) {
		$this->endpoint = $endpoint;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(Event $event): void {
		if(!$event instanceof UserDeletedEvent) {
			return;
		}

		$uid = $event->getUser()->getUID();
		$this->endpoint->removeEndpointId(Application::CONSUMER_TYPE_USER,  $uid);
	}
}
