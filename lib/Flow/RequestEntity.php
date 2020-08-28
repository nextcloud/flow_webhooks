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

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Events\IncomingRequestEvent;
use OCA\FlowWebhooks\Model\Profile;
use OCA\FlowWebhooks\Model\ResurrectedRequest;
use OCA\FlowWebhooks\Service\Endpoint;
use OCA\FlowWebhooks\Service\ProfileManager;
use OCA\FlowWebhooks\Traits\RequestParameterHandling;
use OCP\EventDispatcher\Event;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\WorkflowEngine\EntityContext\IContextPortation;
use OCP\WorkflowEngine\EntityContext\IDisplayText;
use OCP\WorkflowEngine\IEntity;
use OCP\WorkflowEngine\IRuleMatcher;

class RequestEntity implements IEntity, IDisplayText, IContextPortation {
	use RequestParameterHandling;

	/** @var string */
	protected $endpointId;
	/** @var IL10N */
	private $l;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IRequest */
	private $request;
	/** @var Endpoint */
	private $endpoint;
	/** @var ProfileManager */
	private $profileManager;

	public function __construct(IL10N $l, IURLGenerator $urlGenerator, IRequest $request, Endpoint $endpoint, ProfileManager $profileManager) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
		$this->request = $request;
		$this->endpoint = $endpoint;
		$this->profileManager = $profileManager;
	}

	public function getName(): string {
		return $this->l->t('Webhook received');
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('flow_webhooks', 'app-dark.svg');
	}

	public function getEvents(): array {
		return [new RequestEntityEvent($this->l)];
	}

	public function prepareRuleMatcher(IRuleMatcher $ruleMatcher, string $eventName, Event $event): void {
		if(!$event instanceof IncomingRequestEvent) {
			return;
		}
		$ruleMatcher->setEntitySubject($this, $event->getRequest());
		$this->endpointId = $event->getUrlId();
	}

	public function isLegitimatedForUserId(string $userId): bool {
		$personalId = $this->endpoint->getEndpointId(Application::CONSUMER_TYPE_USER, $userId);
		return $personalId === $this->endpointId;
	}

	public function getDisplayText(int $verbosity = 0): string {
		$profile = $this->profileManager->getMatchingProfile($this->request);
		if($profile instanceof Profile) {
			$displayText = $profile->getDisplayTextTemplate($verbosity);
			preg_match_all('/[{]{2} ?[a-zA-Z0-9._-]* ?[}]{2}/', $displayText, $parameterPlaceholders);
			foreach($parameterPlaceholders[0] as $placeholder) {
				$parameterName = trim($placeholder, '{} ');
				$parameterValue = trim($this->getParameterValue($this->request, $parameterName, '(?)'));
				$displayText = str_replace($placeholder, $parameterValue, $displayText);
			}
			return $displayText;
		}

		$params = $this->request->getParams();
		$paramString = '';
		foreach ($params as $name => $val) {
			$paramString .= $name . ': ' . $val . PHP_EOL;
		}
		return $paramString;
	}

	public function exportContextIDs(): array {
		$profile = $this->profileManager->getMatchingProfile($this->request);
		$headers = [];
		if ($profile instanceof Profile) {
			// IRequest does not offer a method to return all headers
			$headerConstraints = $profile->getHeaderConstraints();
			foreach ($headerConstraints as $headerName => $constraints) {
				$headers[$headerName] = $this->request->getHeader($headerName);
			}
		}

		return [
			'requestId' => $this->request->getId(),
			'requestHeaders' => $headers,
			'requestParameters' => $this->request->getParams(),
		];
	}

	public function importContextIDs(array $contextIDs): void {
		$this->request = new ResurrectedRequest(
			$contextIDs['requestId'],
			$contextIDs['requestHeaders'],
			$contextIDs['requestParameters']
		);
	}
}
