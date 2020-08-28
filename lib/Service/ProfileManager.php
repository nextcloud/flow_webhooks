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

namespace OCA\FlowWebhooks\Service;

use OCA\FlowWebhooks\Events\RegisterProfile;
use OCA\FlowWebhooks\Model\Profile;
use OCA\FlowWebhooks\Traits\RequestParameterHandling;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;

class ProfileManager {
	use RequestParameterHandling;

	/** @var IEventDispatcher */
	private $dispatcher;

	public function __construct(IEventDispatcher $dispatcher) {
		$this->dispatcher = $dispatcher;
	}

	/** @var Profile[]  */
	protected $profiles = [];

	public function getMatchingProfile(IRequest $request): ?Profile {
		$this->ensureProfiles();
		foreach ($this->profiles as $profile) {
			if (!$this->matchesHeaderConstraints($request, $profile)) {
				continue;
			}

			if (!$this->matchesParameterConstraints($request, $profile)) {
				continue;
			}

			return $profile;
		}
		return null;
	}

	protected function matchesHeaderConstraints(IRequest $request, Profile $profile): bool {
		foreach ($profile->getHeaderConstraints() as $headerName => $constraints) {
			$headerValue = $request->getHeader($headerName);
			if($headerValue === '') {
				return false;
			}
			foreach ($constraints as $constraintPattern) {
				if (preg_match($constraintPattern, $headerValue) !== 1) {
					return false;
				}
			}
		}
		return true;
	}

	protected function matchesParameterConstraints(IRequest $request, Profile $profile): bool {
		foreach ($profile->getParameterConstraints() as $parameterName => $constraints) {
			$parameterValue = $this->getParameterValue($request, $parameterName, null);
			if($parameterValue === null) {
				return false;
			}

			foreach ($constraints as $constraintPattern) {
				if (preg_match($constraintPattern, $parameterValue) !== 1) {
					return false;
				}
			}
		}
		return true;
	}

	public function addProfile(Profile $profile) {
		$this->profiles[] = $profile;
	}

	protected function ensureProfiles() {
		$this->dispatcher->dispatchTyped(new RegisterProfile($this));
	}
}
