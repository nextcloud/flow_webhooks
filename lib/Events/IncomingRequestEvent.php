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

namespace OCA\FlowWebhooks\Events;

use OCP\EventDispatcher\Event;
use OCP\IRequest;

class IncomingRequestEvent extends Event {
	/** @var IRequest */
	private $request;
	/** @var string */
	private $urlId;

	public function __construct(IRequest $request, string $urlId) {
		$this->request = $request;
		$this->urlId = $urlId;
	}

	public function getRequest(): IRequest {
		return $this->request;
	}

	public function getUrlId(): string {
		return $this->urlId;
	}
}
