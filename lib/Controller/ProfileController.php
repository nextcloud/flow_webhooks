<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
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
use OCA\FlowWebhooks\Exception\ProfileNotFound;
use OCA\FlowWebhooks\Model\Profile;
use OCA\FlowWebhooks\Service\ProfileManager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\IRequest;
use OCP\IUserSession;

class ProfileController extends OCSController {

	/** @var ProfileManager */
	private $manager;

	/** @var IUserSession */
	private $userSession;

	public function __construct(IRequest $request, ProfileManager $manager, IUserSession $userSession) {
		parent::__construct(Application::APP_ID, $request);
		$this->manager = $manager;
		$this->userSession = $userSession;
	}

	/**
	 * @NoAdminRequired
	 */
	public function addProfile(string $name): JSONResponse {
		try {
			$profile = new Profile();
			$profile->setName($name);

			$uid = $this->userSession->getUser()->getUID();

			$profileId = $this->manager->insertProfile($profile, Application::CONSUMER_TYPE_USER, $uid);
			$profile = $this->manager->readProfile($profileId, Application::CONSUMER_TYPE_USER, $uid);

			return new JSONResponse($profile);
		} catch (\Exception $e) {
			return new JSONResponse([
				'message' => $e->getMessage()
			], Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * @NoAdminRequired
	 */
	public function removeProfile(int $id): JSONResponse {
		$uid = $this->userSession->getUser()->getUID();

		try {
			$profile = $this->manager->readProfile($id, Application::CONSUMER_TYPE_USER, $uid);
			$this->manager->deleteProfile($id);
		} catch (ProfileNotFound $e) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		return new JSONResponse([]);
	}

	/**
	 * @NoAdminRequired
	 */
	public function editProfile(
		int $id,
		string $name,
		array $headerConstraints,
		array $parameterConstraints,
		array $displayTextTemplates,
		string $urlTemplate,
		string $iconUrlTemplate
	): JSONResponse {
		$uid = $this->userSession->getUser()->getUID();

		try {
			$profile = $this->manager->readProfile($id, Application::CONSUMER_TYPE_USER, $uid);
		} catch (ProfileNotFound $e) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		$profile->setName($name);

		//TODO: Verify all paramters

		$profile->clearHeaderConstraints();
		foreach ($headerConstraints as $headerName => $constraints) {
			foreach ($constraints as $constraint) {
				$profile->setHeaderConstraint($headerName, $constraint);
			}
		}

		$profile->clearParameterConstraints();
		foreach ($parameterConstraints as $paramaterName => $constraints) {
			foreach ($constraints as $constraint) {
				$profile->setParameterConstraint($paramaterName, $constraint);
			}
		}

		$profile->clearDisplayTextTemplate();
		foreach ($displayTextTemplates as $displayTextName => $template) {
			$profile->setDisplayTextTemplate($displayTextName, $template);
		}

		$profile->setUrlTemplate($urlTemplate);
		$profile->setIconUrlTemplate($iconUrlTemplate);

		return new JSONResponse([]);
	}
}
