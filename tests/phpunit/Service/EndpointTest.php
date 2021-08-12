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

namespace OCA\FlowWebhooks\Tests\Service;

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Service\Endpoint;
use OCP\IDBConnection;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class EndpointTest
 *
 * @group DB
 *
 * @package OCA\FlowWebhooks\Tests\Service
 */
class EndpointTest extends TestCase {

	/** @var Endpoint */
	protected $endpoint;
	/** @var ISecureRandom|\PHPUnit\Framework\MockObject\MockObject */
	protected $rnd;

	public function setUp(): void {
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$dbc = \OC::$server->get(IDBConnection::class);
		$this->rnd = $this->createMock(ISecureRandom::class);
		$logger = $this->createMock(LoggerInterface::class);

		$this->endpoint = new Endpoint($urlGenerator, $dbc, $this->rnd, $logger);
	}

	public function tearDown(): void {
		/** @var IDBConnection $dbc */
		$dbc = \OC::$server->get(IDBConnection::class);
		$qb = $dbc->getQueryBuilder();
		$qb->delete('flow_webhooks_endpoints')->execute();

		parent::tearDown();
	}

	public function consumerProvider(): array {
		return [
			[ Application::CONSUMER_TYPE_INSTANCE, null, null ],
			[ Application::CONSUMER_TYPE_USER, 'alice', null ],
			[ 'aliens', 'predator', \InvalidArgumentException::class ],
		];
	}

	/**
	 * @dataProvider consumerProvider
	 */
	public function testGetEndpointId(string $consumerType, ?string $consumerId, ?string $exceptionClass) {
		$this->rnd->expects($this->any())
			->method('generate')
			->willReturn((string)mt_rand(1000000000, 9999999999));

		try {
			$id = $this->endpoint->getEndpointId($consumerType, $consumerId);
		} catch (\Throwable $e) {
			$this->assertInstanceOf($exceptionClass, $e);
			return;
		}
		$id2 = $this->endpoint->getEndpointId($consumerType, $consumerId);
		// ensure that endpoints are stable
		$this->assertSame($id, $id2);
	}
}
