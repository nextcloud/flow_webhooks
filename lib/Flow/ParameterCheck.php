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

use OCA\FlowWebhooks\Exceptions\ParameterNotFound;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\WorkflowEngine\ICheck;

class ParameterCheck implements ICheck {
	/** @var array[] Nested array: [Pattern => [ActualValue => Regex Result]] */
	protected $matches;

	/** @var IRequest */
	protected $request;

	/** @var IL10N */
	private $l;
	/**
	 * @var ILogger
	 */
	private $logger;

	public function __construct(IL10N $l, IRequest $request, ILogger $logger) {
		$this->l = $l;
		$this->request = $request;
		$this->logger = $logger;
	}

	public function executeCheck($operator, $value) {
		$value = \json_decode($value, true);
		if(!is_array($value)) {
			return false;
		}
		try {
			$actualValue = $this->getActualValue($value[0]);
			return $this->executeStringCheck($operator, $value[1], $actualValue);
		} catch (ParameterNotFound $e) {
			$this->logger->logException($e, ['level' => ILogger::DEBUG]);
			return false;
		}
	}

	public function validateCheck($operator, $value) {
		if (!in_array($operator, ['is', '!is', 'matches', '!matches'])) {
			throw new \UnexpectedValueException($this->l->t('The given operator is invalid'), 1);
		}

		if (in_array($operator, ['matches', '!matches']) &&
			@preg_match($value['value'], null) === false) {
			throw new \UnexpectedValueException($this->l->t('The given regular expression is invalid'), 2);
		}

		if (strlen($value['name']) < 1) {
			throw new \UnexpectedValueException($this->l->t('The given parameter name is invalid'), 1);
		}
	}

	protected function executeStringCheck(string $operator, string $checkValue, string $actualValue): bool {
		if ($operator === 'is') {
			return $checkValue === $actualValue;
		} else if ($operator === '!is') {
			return $checkValue !== $actualValue;
		} else {
			$match = $this->match($checkValue, $actualValue);
			if ($operator === 'matches') {
				return $match === 1;
			} else {
				return $match === 0;
			}
		}
	}

	protected function match($pattern, $subject) {
		$patternHash = md5($pattern);
		$subjectHash = md5($subject);
		if (isset($this->matches[$patternHash][$subjectHash])) {
			return $this->matches[$patternHash][$subjectHash];
		}
		if (!isset($this->matches[$patternHash])) {
			$this->matches[$patternHash] = [];
		}
		$this->matches[$patternHash][$subjectHash] = preg_match($pattern, $subject);
		return $this->matches[$patternHash][$subjectHash];
	}

	public function supportedEntities(): array {
		return [RequestEntity::class];
	}

	public function isAvailableForScope(int $scope): bool {
		return true;
	}

	/**
	 * @throws ParameterNotFound
	 */
	protected function getActualValue($parameterName): string {
		if($this->request instanceof IRequest) {
			return $this->request->getParam($parameterName, '');
		}
		throw new ParameterNotFound(sprintf('Parameter %s not found', [$parameterName]));
	}
}
