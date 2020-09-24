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

namespace OCA\FlowWebhooks\Settings;

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Service\Endpoint;
use OCA\FlowWebhooks\Service\ProfileManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	/** @var IInitialStateService */
	private $stateService;
	/** @var Endpoint */
	private $endpoint;
	/** @var ProfileManager */
	private $profileManager;

	public function __construct(IInitialStateService $stateService, Endpoint $endpoint, ProfileManager $profileManager) {
		$this->stateService = $stateService;
		$this->endpoint = $endpoint;
		$this->profileManager = $profileManager;
	}

	public function getForm() {
		$endpoint = $this->endpoint->getEndpointUrl(Application::CONSUMER_TYPE_INSTANCE, null);
		$this->stateService->provideInitialState(Application::APP_ID, 'webhookEndpoint', $endpoint);
		$profiles = $this->profileManager->getOwnerProfiles(Application::CONSUMER_TYPE_INSTANCE, null);
		$this->stateService->provideInitialState(Application::APP_ID, 'profiles', $profiles);
		$this->stateService->provideInitialState(Application::APP_ID, 'consumer', Application::CONSUMER_TYPE_INSTANCE);
		return new TemplateResponse(Application::APP_ID, 'settings');
	}

	public function getSection() {
		return 'webhooks';
	}

	public function getPriority() {
		return 5;
	}
}
