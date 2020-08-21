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

namespace OCA\FlowWebhooks\Controller;

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Service\Dispatcher;
use OCA\FlowWebhooks\Service\Endpoint;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class TriggerController extends OCSController {
	/** @var IRequest */
	protected $request;
	/** @var Dispatcher */
	private $dispatcher;
	/** @var Endpoint */
	private $endpoint;

	public function __construct(IRequest $request, Dispatcher $dispatcher, Endpoint $endpoint) {
		parent::__construct(Application::APP_ID, $request);
		$this->request = $request;
		$this->dispatcher = $dispatcher;
		$this->endpoint = $endpoint;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function receive(string $urlId): Response {
		$status = 400;
		// "uninvited" requests are discarded
		if($this->endpoint->endpointExists($urlId)) {
			$this->dispatcher->dispatch($this->request, $urlId);
			$status = 200;
		}
		$r = new Response();
		$r->setStatus($status);
		return $r;
	}

}
