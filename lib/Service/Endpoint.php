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

use Doctrine\DBAL\DBALException;
use DomainException;
use InvalidArgumentException;
use LogicException;
use OCA\FlowWebhooks\AppInfo\Application;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;
use Psr\Log\LoggerInterface;

class Endpoint {
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IDBConnection */
	private $dbc;
	/** @var ISecureRandom */
	private $random;
	/** @var LoggerInterface */
	private $logger;

	public function __construct(
		IURLGenerator $urlGenerator,
		IDBConnection $dbc,
		ISecureRandom $random,
		LoggerInterface $logger
	) {
		$this->urlGenerator = $urlGenerator;
		$this->dbc = $dbc;
		$this->random = $random;
		$this->logger = $logger;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function getEndpointId(string $consumerType, ?string $consumerId): string {
		if(!in_array($consumerType, [Application::CONSUMER_TYPE_INSTANCE, Application::CONSUMER_TYPE_USER])) {
			throw new InvalidArgumentException('Invalid consumer type');
		}

		try {
			$endpoint = $this->getEndpointFromDb($consumerType, $consumerId);
		} catch (DomainException $e) {
			$attempts = 0;
			do {
				$endpoint = $this->random->generate(10, 'abcdefghijklmnopqrstuvwxyz0123456789');
				$success = $this->setEndpoint($endpoint, $consumerType, $consumerId);
				$attempts++;
			} while(!$success && !($attempts === 5));
			if(!$success) {
				throw new LogicException('Could not create new endpoint');
			}
		}

		return $endpoint;
	}

	public function removeEndpointId(string $consumerType, ?string $consumerId): bool {
		if(!in_array($consumerType, [Application::CONSUMER_TYPE_INSTANCE, Application::CONSUMER_TYPE_USER])) {
			throw new InvalidArgumentException('Invalid consumer type');
		}

		$qb = $this->dbc->getQueryBuilder();
		$qb->delete('flow_webhooks_endpoints')
			->where($qb->expr()->eq('consumer_type', $qb->createNamedParameter($consumerType)));

		if ($consumerId === null) {
			$qb->andWhere($qb->expr()->isNull('consumer_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('consumer_id', $qb->createNamedParameter($consumerId)));
		}

		return (bool)$qb->execute();
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function getEndpointUrl(string $consumerType, ?string $consumerId): string {
		$urlId = $this->getEndpointId($consumerType, $consumerId);
		$routeName = Application::APP_ID . '.Trigger.receive';

		return $this->urlGenerator->linkToOCSRouteAbsolute($routeName, ['urlId' => $urlId]);
	}

	protected function getEndpointFromDb(string $consumerType, ?string $consumerId): string {
		$qb = $this->dbc->getQueryBuilder();
		$qb->select(['endpoint'])
			->from('flow_webhooks_endpoints')
			->where($qb->expr()->eq('consumer_type', $qb->createNamedParameter($consumerType)))
			->setMaxResults(1);

		if ($consumerId === null) {
			$qb->andWhere($qb->expr()->isNull('consumer_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('consumer_id', $qb->createNamedParameter($consumerId)));
		}

		$stmt = $qb->execute();
		$endpoint = $stmt->fetchColumn();
		$stmt->closeCursor();
		if($endpoint && is_string($endpoint) && strlen($endpoint) === 10) {
			return $endpoint;
		}

		throw new DomainException();
	}

	public function endpointExists(string $endpoint): bool {
		$qb = $this->dbc->getQueryBuilder();
		$qb->select(['id'])
			->from('flow_webhooks_endpoints')
			->where($qb->expr()->eq('endpoint', $qb->createNamedParameter($endpoint)))
			->setMaxResults(1);
		$stmt = $qb->execute();
		$r = $stmt->fetchColumn() !== false;
		$stmt->closeCursor();
		return $r;
	}

	public function getEndpointOwner(string $endpoint): array {
		$qb = $this->dbc->getQueryBuilder();
		$qb->select(['consumer_type', 'consumer_id'])
			->from('flow_webhooks_endpoints')
			->where($qb->expr()->eq('endpoint', $qb->createNamedParameter($endpoint)))
			->setMaxResults(1);
		$stmt = $qb->execute();
		$row = $stmt->fetch();
		$stmt->closeCursor();
		return [
			'type' => $row['consumer_type'] ?? null,
			'id' => $row['consumer_id'] ?? null,
		];
	}

	protected function setEndpoint(string $endpoint, string $consumerType, ?string $consumerId): bool {
		$qb = $this->dbc->getQueryBuilder();
		$qb->insert('flow_webhooks_endpoints')
			->values([
				'endpoint' => $qb->createNamedParameter($endpoint),
				'consumer_type' => $qb->createNamedParameter($consumerType),
				'consumer_id' => $qb->createNamedParameter($consumerId)
			]);
		try {
			return (bool)$qb->execute();
		} catch (DBALException $e) {
			$this->logger->debug(
				'Could not insert new Webhook endpoint into DB',
				[
					'app' => 'flow_webhooks',
					'exception' => $e,
				]
			);
			return false;
		}
	}
}
