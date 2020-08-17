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

namespace OCA\FlowWebhooks\Controller;

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Service\Dispatcher;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;

class TriggerController extends OCSController {
	/** @var IRequest */
	protected $request;
	/** @var Dispatcher */
	private $dispatcher;

	public function __construct(IRequest $request, Dispatcher $dispatcher) {
		parent::__construct(Application::APP_ID, $request);
		$this->request = $request;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function onGet($urlId): DataResponse {
		$this->dispatcher->dispatch($this->request, $urlId);
		return new DataResponse();
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 */
	public function onPost($urlId): DataResponse {
		$this->dispatcher->dispatch($this->request, $urlId);
		return new DataResponse();
	}

}
