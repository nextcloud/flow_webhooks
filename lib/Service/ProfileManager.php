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

use OCA\FlowWebhooks\AppInfo\Application;
use OCA\FlowWebhooks\Events\RegisterProfile;
use OCA\FlowWebhooks\Exception\ProfileNotFound;
use OCA\FlowWebhooks\Model\Profile;
use OCA\FlowWebhooks\Traits\RequestParameterHandling;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;

class ProfileManager {
	use RequestParameterHandling;

	protected const TABLE_PROFILES = 'flow_webhooks_profiles';

	/** @var IEventDispatcher */
	private $dispatcher;
	/** @var IDBConnection */
	private $dbc;
	/** @var Endpoint */
	private $endpoint;
	/** @var IGroupManager */
	private $groupManager;

	public function __construct(
		IEventDispatcher $dispatcher,
		IDBConnection $dbc,
		Endpoint $endpoint,
		IGroupManager $groupManager
	) {
		$this->dispatcher = $dispatcher;
		$this->dbc = $dbc;
		$this->endpoint = $endpoint;
		$this->groupManager = $groupManager;
	}

	/** @var Profile[]  */
	protected $profiles = [];

	public function getMatchingProfile(IRequest $request, string $endpointId): ?Profile {
		$this->ensureProfiles($endpointId);
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

	/**
	 * @return Profile[]
	 */
	public function getOwnerProfiles(string $consumerType, ?string $consumerId): array {
		$qb = $this->dbc->getQueryBuilder();
		$qb->select(['*'])
			->from(self::TABLE_PROFILES)
			->where($qb->expr()->eq('consumer_type', $qb->createNamedParameter($consumerType)));

		if($consumerId === null) {
			$qb->andWhere($qb->expr()->isNull('consumer_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('consumer_id', $qb->createNamedParameter($consumerId)));
		}

		$stmt = $qb->execute();

		$results = [];
		while($profileData = $stmt->fetch()) {
			$p = new Profile();
			$this->readHeaderConstraints($p, $profileData['header_constraints']);
			$this->readParameterConstraints($p, $profileData['param_constraints']);
			$this->readDisplayTextTemplates($p, $profileData['display_text_templates']);
			$p
				->setId((int)$profileData['id'])
				->setName($profileData['name'])
				->setUrlTemplate($profileData['url_template'])
				->setIconUrlTemplate($profileData['icon_url_template']);

			$results[$profileData['id']] = $p;
		}
		$stmt->closeCursor();

		return $results;
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

	protected function ensureProfiles(string $endpointId) {
		if (!empty($this->profiles)) {
			return;
		}

		$this->dispatcher->dispatchTyped(new RegisterProfile($this));
		$this->loadConfiguredProfiles($endpointId);

		$githubComment = new Profile();
		$githubComment
			->setParameterConstraint('comment.body', '/.+/')
			->setParameterConstraint('comment.user.id', '/[0-9]+/')
			->setDisplayTextTemplate(0, '{{comment.user.login}} says {{ comment.body }}')
			->setUrlTemplate('{{comment.html_url}}')
			->setIconUrlTemplate('{{comment.user.avatar_url}}');

		$this->addProfile($githubComment);
	}

	protected function loadConfiguredProfiles(string $endpointId) {
		$qb = $this->dbc->getQueryBuilder();

		$endpointOwner = $this->endpoint->getEndpointOwner($endpointId);
		if ($endpointOwner['type'] === Application::CONSUMER_TYPE_USER) {
			$userClause = $qb->expr()->andX(
				$qb->expr()->eq('consumer_type', $qb->createNamedParameter(Application::CONSUMER_TYPE_USER)),
				$qb->expr()->eq('consumer_id', $qb->createNamedParameter($endpointOwner['id']))
			);
		}
		$instanceClause = $qb->expr()->andX(
			$qb->expr()->eq('consumer_type', $qb->createNamedParameter(Application::CONSUMER_TYPE_INSTANCE)),
			$qb->expr()->isNull('consumer_id')
		);

		if (isset($userClause)) {
			$where = $qb->expr()->orX($userClause, $instanceClause);
		} else {
			$where = $instanceClause;
		}

		$stmt = $qb->select(['*'])
			->from(self::TABLE_PROFILES)
			->where($where)
			->execute();
		$profiles = $stmt->fetchAll();
		$stmt->closeCursor();
		usort($profiles, function (array $a, array $b) {
			$ai = (int)($a['consumer_type'] === Application::CONSUMER_TYPE_INSTANCE);
			$bi = (int)($b['consumer_type'] === Application::CONSUMER_TYPE_INSTANCE);

			return $ai <=> $bi;
		});

		foreach ($profiles as $profileData) {
			$p = new Profile();
			$this->readHeaderConstraints($p, $profileData['header_constraints']);
			$this->readParameterConstraints($p, $profileData['param_constraints']);
			$this->readDisplayTextTemplates($p, $profileData['display_text_templates']);
			$p
				->setName($profileData['name'])
				->setUrlTemplate($profileData['url_template'])
				->setIconUrlTemplate($profileData['icon_url_template']);

			$this->addProfile($p);
		}
	}

	protected function readDisplayTextTemplates(Profile $profile, string $field): void {
		$templates = \json_decode($field, true);
		$isDefaultSet = false;
		foreach ($templates as $verbosity => $template) {
			$profile->setDisplayTextTemplate((int)$verbosity, $template);
			$isDefaultSet |= (int)$verbosity === 0;
		}
		if(!$isDefaultSet && !empty($templates)) {
			$profile->setDisplayTextTemplate(0, array_shift($templates));
		}
	}

	protected function readHeaderConstraints(Profile $profile, string $field): void {
		$this->readConstraints($field, function($name, $pattern) use ($profile) {
			$profile->setHeaderConstraint($name, $pattern);
		});
	}

	protected function readParameterConstraints(Profile $profile, string $field): void {
		$this->readConstraints($field, function($name, $pattern) use ($profile) {
			$profile->setParameterConstraint($name, $pattern);
		});
	}

	protected function readConstraints( string $field, \Closure $setter) {
		$constraints = \json_decode($field, true);
		if (!is_array($constraints)) {
			return;
		}
		foreach ($constraints as $headerName => $constraintPatterns) {
			foreach ($constraintPatterns as $pattern) {
				$setter($headerName, $pattern);
			}
		}
	}

	public function insertProfile(Profile $profile, string $consumerType, ?string $consumerId): int {
		if($consumerType === Application::CONSUMER_TYPE_INSTANCE && $consumerId !== null) {
			$consumerId = null;
		}

		$qb = $this->dbc->getQueryBuilder();
		$affected = $qb->insert(self::TABLE_PROFILES)
			->values([
				'name' => $qb->createNamedParameter($profile->getName()),
				'consumer_type' => $qb->createNamedParameter($consumerType),
				'consumer_id' => $qb->createNamedParameter($consumerId),
				'header_constraints' => $qb->createNamedParameter(\json_encode($profile->getHeaderConstraints())),
				'param_constraints' => $qb->createNamedParameter(\json_encode($profile->getParameterConstraints())),
				'display_text_templates' => $qb->createNamedParameter(\json_encode($profile->getAllDisplayTextTemplates())),
				'url_template' => $qb->createNamedParameter($profile->getUrlTemplate()),
				'icon_url_template' => $qb->createNamedParameter($profile->getIconUrlTemplate())
			])
			->execute();
		if ($affected === 1) {
			return $qb->getLastInsertId();
		}
		return -1;
	}

	/**
	 * @throws ProfileNotFound
	 */
	public function readProfile(int $id, string $consumerType, ?string $consumerId) {
		if($consumerType === Application::CONSUMER_TYPE_INSTANCE && $consumerId !== null) {
			$consumerId = null;
		}

		$qb = $this->dbc->getQueryBuilder();
		$qb->select(['*'])
			->from(self::TABLE_PROFILES)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->andWhere($qb->expr()->eq('consumer_type', $qb->createNamedParameter($consumerType)))
			->setMaxResults(1);

		if($consumerId === null) {
			$qb->andWhere($qb->expr()->isNull('consumer_id'));
		} else {
			$qb->andWhere($qb->expr()->eq('consumer_id', $qb->createNamedParameter($consumerId)));
		}

		$stmt = $qb->execute();
		$profileData = $stmt->fetch();
		$stmt->closeCursor();

		if ($profileData === false) {
			throw new ProfileNotFound();
		}

		$p = new Profile();
		$this->readHeaderConstraints($p, $profileData['header_constraints']);
		$this->readParameterConstraints($p, $profileData['param_constraints']);
		$this->readDisplayTextTemplates($p, $profileData['display_text_templates']);
		$p
			->setId((int)$profileData['id'])
			->setName($profileData['name'])
			->setUrlTemplate($profileData['url_template'])
			->setIconUrlTemplate($profileData['icon_url_template']);

		return $p;
	}

	public function updateProfile(int $id, Profile $profile): bool {
		$qb = $this->dbc->getQueryBuilder();
		return $qb->update(self::TABLE_PROFILES)
				->set('name', $qb->createNamedParameter($profile->getName()))
				->set('header_constraints', $qb->createNamedParameter(\json_encode($profile->getHeaderConstraints())))
				->set('param_constraints', $qb->createNamedParameter(\json_encode($profile->getParameterConstraints())))
				->set('display_text_templates', $qb->createNamedParameter(\json_encode($profile->getAllDisplayTextTemplates())))
				->set('url_template', $qb->createNamedParameter($profile->getUrlTemplate()))
				->set('icon_url_template', $qb->createNamedParameter($profile->getIconUrlTemplate()))
				->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
				->execute() > 0;
	}

	public function deleteProfile(int $id): bool {
		$qb = $this->dbc->getQueryBuilder();
		return $qb->delete(self::TABLE_PROFILES)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id)))
			->execute() > 0;
	}

	/**
	 * @throw \InvalidArgumentException
	 */
	public function canEditProfile(IUser $user, string $consumerType, ?int $endpointId): bool {
		if($consumerType === Application::CONSUMER_TYPE_INSTANCE) {
			// currently, only admins can manipulate instance profiles
			return $this->groupManager->isAdmin($user->getUID());
		} else if($consumerType === Application::CONSUMER_TYPE_USER) {
			try {
				if($endpointId === null) {
					// any user can create manage their flows
					return true;
				}
				$this->readProfile($endpointId, $consumerType, $user->getUID());
				return true;
			} catch (ProfileNotFound $e) {
				return false;
			}
		}
		throw  new \InvalidArgumentException('Invalid consumer type');
	}
}
