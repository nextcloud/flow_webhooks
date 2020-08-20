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

namespace OCA\FlowWebhooks\Service;

use OCA\FlowWebhooks\AppInfo\Application;
use OCP\IURLGenerator;

class Endpoint {
	/** @var IURLGenerator */
	private $urlGenerator;

	public function __construct(IURLGenerator $urlGenerator) {
		$this->urlGenerator = $urlGenerator;
	}

	public function getEndpointId(string $consumerType, ?string $consumerId) {
		if(!in_array($consumerType, [Application::CONSUMER_TYPE_INSTANCE, Application::CONSUMER_TYPE_USER])) {
			throw new \InvalidArgumentException('Invalid consumer type');
		}

		return substr(hash('sha256', $consumerType . '_' . (string)$consumerId), 0, 10);
	}

	public function getEndpointUrl(string $consumerType, ?string $consumerId) {
		if(!in_array($consumerType, [Application::CONSUMER_TYPE_INSTANCE, Application::CONSUMER_TYPE_USER])) {
			throw new \InvalidArgumentException('Invalid consumer type');
		}

		$routeName = Application::APP_ID . '.Trigger.onGet';
		$urlId = $this->getEndpointId($consumerType, $consumerId);

		return $this->urlGenerator->linkToOCSRouteAbsolute($routeName, ['urlId' => $urlId]);
	}
}
