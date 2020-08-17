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

namespace OCA\FlowWebhooks\Flow;

use OCA\FlowWebhooks\Service\EIncomingRequest;
use OCP\EventDispatcher\Event;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\WorkflowEngine\EntityContext\IDisplayText;
use OCP\WorkflowEngine\IEntity;
use OCP\WorkflowEngine\IRuleMatcher;

class RequestEntity implements IEntity, IDisplayText {
	/**
	 * @var IL10N
	 */
	private $l;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	/**
	 * @var IRequest
	 */
	private $request;

	public function __construct(IL10N $l, IURLGenerator $urlGenerator, IRequest $request) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
		$this->request = $request;
	}

	public function getName(): string {
		return $this->l->t('Web Request');
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('core', 'actions/external.svg');
	}

	public function getEvents(): array {
		return [new RequestEntityEvent($this->l)];
	}

	public function prepareRuleMatcher(IRuleMatcher $ruleMatcher, string $eventName, Event $event): void {
		if(!$event instanceof EIncomingRequest) {
			return;
		}
		$ruleMatcher->setEntitySubject($this, $event->getSubject());
	}

	public function isLegitimatedForUserId(string $userId): bool {
		// FIXME
		return true;
	}

	public function getDisplayText(int $verbosity = 0): string {
		$params = $this->request->getParams();
		$paramString = '';
		foreach ($params as $name => $val) {
			$paramString .= $name . ': ' . $val . PHP_EOL;
		}
		return $paramString;
	}
}
