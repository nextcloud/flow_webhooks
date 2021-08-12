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
use OCA\FlowWebhooks\Exception\ProfileNotFound;
use OCA\FlowWebhooks\Model\Profile;
use OCA\FlowWebhooks\Service\Endpoint;
use OCA\FlowWebhooks\Service\ProfileManager;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IDBConnection;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * Class ProfileManagerTest
 *
 * @group DB
 *
 * @package OCA\FlowWebhooks\Tests\Service
 */
class ProfileManagerTest extends TestCase {
	/** @var ProfileManager */
	protected $profileManager;
	/** @var IEventDispatcher|MockObject */
	protected $dispatcher;
	/** @var Endpoint|MockObject */
	protected $endpoint;

	public function setUp(): void {
		$this->dispatcher = $this->createMock(IEventDispatcher::class);
		$dbc = \OC::$server->get(IDBConnection::class);
		$this->endpoint  = $this->createMock(Endpoint::class);

		$this->profileManager = new ProfileManager($this->dispatcher, $dbc, $this->endpoint);
	}

	public function tearDown(): void {
		/** @var IDBConnection $dbc */
		$dbc = \OC::$server->get(IDBConnection::class);
		$qb = $dbc->getQueryBuilder();
		$qb->delete('flow_webhooks_profiles')->execute();

		parent::tearDown();
	}

	public function requestProvider() {
		$r1 = $this->createMock(IRequest::class);
		$r1->expects($this->any())
			->method('getHeader')
			->willReturnCallback(function(string $name) {
				switch($name) {
					case 'X-Awesome':
						return 'foobar-foobar';
					default:
						return '';
				}
			});
		$r1->expects($this->any())
			->method('getParam')
			->willReturnCallback(function(string $name, $default = null) {
				switch($name) {
					case 'awe':
						return ['some' => 'barfoo_barfoo'];
					default:
						return $default;
				}
			});

		$r2 = $this->createMock(IRequest::class);
		$r2->expects($this->any())
			->method('getHeader')
			->willReturnCallback(function(string $name) {
				switch($name) {
					case 'X-Awesome':
						return 'foobar-foobar';
					case 'X-Terrific':
						return 'foo-foo';
					default:
						return '';
				}
			});
		$r2->expects($this->any())
			->method('getParam')
			->willReturnCallback(function(string $name, $default = null) {
				switch($name) {
					case 'te':
						return ['riff' => [ 'ic' => 'rabbarba' ]];
					default:
						return $default;
				}
			});

		$r3 = $this->createMock(IRequest::class);
		$r3->expects($this->any())
			->method('getHeader')
			->willReturnCallback(function(string $name) {
				switch($name) {
					case 'X-Awesome':
						return 'not-what-are-looking-for';
					case 'X-Terrific':
						return 'also-something-else';
					default:
						return '';
				}
			});
		$r3->expects($this->any())
			->method('getParam')
			->willReturnCallback(function(string $name, $default = null) {
				switch($name) {
					case 'awe':
						return 'ful';
					case 'te':
						return ['riff' => 'biff'];
					default:
						return $default;
				}
			});

		$user = [ 'type' => Application::CONSUMER_TYPE_USER, 'id' => 'alice'];
		$instance = [ 'type' => Application::CONSUMER_TYPE_INSTANCE, 'id' => null];
		return [
			[$r1, 'ProfileA', $user],
			[$r1, 'ProfileA', $instance],
			[$r2, 'ProfileB', $user],
			[$r2, 'ProfileB', $instance],
			[$r3, null, $user],
			[$r3, null, $instance],
		];
	}

	/**
	 * @dataProvider requestProvider
	 */
	public function testGetMatchingProfile(IRequest $request, ?string $expectedName) {
		$profileA = $this->createMock(Profile::class);
		$profileA->expects($this->any())
			->method('getName')
			->willReturn('ProfileA');
		$profileA->expects($this->any())
			->method('getHeaderConstraints')
			->willReturn([
				'X-Awesome' => ['/^foobar/']
			]);
		$profileA->expects($this->any())
			->method('getParameterConstraints')
			->willReturn([
				'awe.some' => ['/barfoo$/']
			]);

		$profileB = $this->createMock(Profile::class);
		$profileB->expects($this->any())
			->method('getName')
			->willReturn('ProfileB');
		$profileB->expects($this->any())
			->method('getHeaderConstraints')
			->willReturn([
				'X-Terrific' => ['/foo.*/']
			]);
		$profileB->expects($this->any())
			->method('getParameterConstraints')
			->willReturn([
				'te.riff.ic' => ['/.*bar.*/']
			]);

		$this->profileManager->addProfile($profileA);
		$this->profileManager->addProfile($profileB);

		$profile = $this->profileManager->getMatchingProfile($request, 'abcdefghij');

		if($expectedName !== null) {
			$this->assertInstanceOf(Profile::class, $profile);
			$this->assertSame($expectedName, $profile->getName());
		} else {
			$this->assertNull($profile);
		}
	}

	/**
	 * @dataProvider requestProvider
	 */
	public function testGetMatchingProfileWithDB(IRequest $request, ?string $expectedName, array $subject) {
		$profileA = new Profile();
		$profileA
			->setName('ProfileA')
			->setHeaderConstraint('X-Awesome', '/^foobar/')
			->setParameterConstraint('awe.some', '/barfoo$/');

		$profileB = new Profile();
		$profileB
			->setName('ProfileB')
			->setHeaderConstraint('X-Terrific', '/foo.*/')
			->setParameterConstraint('te.riff.ic', '/.*bar.*/');

		$this->profileManager->insertProfile($profileA, $subject['type'], $subject['id']);
		$this->profileManager->insertProfile($profileB, $subject['type'], $subject['id']);

		$this->endpoint->expects($this->any())
			->method('getEndpointOwner')
			->with('abcdefghij')
			->willReturn($subject);
		$profile = $this->profileManager->getMatchingProfile($request, 'abcdefghij');

		if($expectedName !== null) {
			$this->assertInstanceOf(Profile::class, $profile);
			$this->assertSame($expectedName, $profile->getName());
		} else {
			$this->assertNull($profile);
		}
	}

	public function testProfileCRUD() {
		$p = new Profile();
		$p
			->setName('Favorite Foodplace Feed')
			->setUrlTemplate('{{menu_url}}')
			->setIconUrlTemplate('{{logo_url}}')
			->setDisplayTextTemplate(0, 'Menu of the day: {{menu_items}}')
			->setParameterConstraint('menu_items', '/.+/');

		$id = $this->profileManager->insertProfile($p, Application::CONSUMER_TYPE_INSTANCE, null);
		$this->assertTrue($id > -1);
		$this->assertInstanceOf(Profile::class, $this->profileManager->readProfile($id, Application::CONSUMER_TYPE_INSTANCE, null));

		$p->setIconUrlTemplate('{{dish_of_the_day_picture_url}}');
		$this->assertTrue($this->profileManager->updateProfile($id,  $p));
		$p2 = $this->profileManager->readProfile($id, Application::CONSUMER_TYPE_INSTANCE, null);
		$this->assertSame('{{dish_of_the_day_picture_url}}', $p2->getIconUrlTemplate());

		$this->assertTrue($this->profileManager->deleteProfile($id));
		$caught = null;
		try {
			$this->profileManager->readProfile($id, Application::CONSUMER_TYPE_INSTANCE, null);
		} catch (ProfileNotFound $e) {
			$caught = $e;
		}
		$this->assertInstanceOf(ProfileNotFound::class, $caught);
	}

}
