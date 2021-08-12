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

namespace OCA\FlowWebhooks\Middleware;

use OCA\FlowWebhooks\Exception\NoPermissions;
use OCA\FlowWebhooks\Exception\ProfileNotFound;
use OCA\FlowWebhooks\Service\ProfileManager;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\AppFramework\Utility\IControllerMethodReflector;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

class ProfileAccess extends Middleware {

	/** @var IUserSession */
	private $userSession;
	/** @var IRequest */
	private $request;
	/** @var ProfileManager */
	private $profileManager;
	/** @var IControllerMethodReflector */
	private $reflector;

	public function __construct(
		IUserSession $userSession,
		IRequest $request,
		ProfileManager $profileManager,
		IControllerMethodReflector $reflector
	) {
		\OC::$server->getLogger()->critical('MIDDLEWERE TADA');
		$this->userSession = $userSession;
		$this->request = $request;
		$this->profileManager = $profileManager;
		$this->reflector = $reflector;
	}

	/**
	 * @throws NoPermissions
	 */
	public function beforeController($controller, $methodName) {
		\OC::$server->getLogger()->critical('BEFORE CONTROLLER');
		if (!$this->reflector->hasAnnotation('RequireProfileEditAccess')) {
			return;
		}
		$noPermissionsException = new NoPermissions();

		try {
			$user = $this->userSession->getUser();
			$profileId = $this->request->getParam('profileId', null);
			$profileId = $profileId ? (int)$profileId : null;

			if (!$user instanceof IUser ||
				!$this->profileManager->canEditProfile(
				$user,
				$this->request->getParam('consumerType', ''),
				$profileId
			)) {
				throw $noPermissionsException;
			}
		} catch (\InvalidArgumentException $e) {
			throw $noPermissionsException;
		}
	}

	public function afterException($controller, $methodName, \Exception $exception) {
		if($exception instanceof NoPermissions) {
			$r = new Response();
			$r->setStatus(403);
			return $r;
		} elseif ($exception instanceof ProfileNotFound) {
			return new NotFoundResponse();
		}
		throw $exception;
	}
}
