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

namespace OCA\FlowWebhooks\Model;

use function json_encode;

class Profile implements \JsonSerializable {
	/** @var int */
	protected $id = null;
	/** @var array */
	protected $headerConstraints = [];
	/** @var array */
	protected $parameterConstraints = [];
	/** @var string[] */
	protected $displayTextTemplates = ['', '', '', ''];
	/** @var string */
	protected $urlTemplate = '';
	/** @var string */
	protected $iconUrlTemplate = '';
	/** @var string */
	protected $name = '';

	public function setId(int $id): Profile {
		$this->id = $id;
		return $this;
	}

	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @throws \LengthException
	 */
	public function setName(string $name): Profile {
		if(\mb_strlen($name, 'UTF-8') > 64) {
			throw new \LengthException('Name must not be longer than 64 bytes');
		}
		$this->name = $name;
		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getHeaderConstraints(): array {
		return $this->headerConstraints;
	}

	public function setHeaderConstraint(string $name, string $pattern): Profile {
		if(!isset($this->headerConstraints[$name])) {
			$this->headerConstraints[$name] = [];
		}
		$this->headerConstraints[$name][] = $pattern;
		return $this;
	}

	public function clearHeaderConstraints(): Profile {
		$this->headerConstraints = [];
		return $this;
	}

	public function getParameterConstraints(): array {
		return $this->parameterConstraints;
	}

	public function setParameterConstraint(string $name, string $pattern): Profile {
		if(!isset($this->parameterConstraints[$name])) {
			$this->parameterConstraints[$name] = [];
		}
		$this->parameterConstraints[$name][] = $pattern;
		return $this;
	}

	public function clearParameterConstraints(): Profile {
		$this->parameterConstraints = [];
		return $this;
	}

	public function getDisplayTextTemplate(int $verbosity): string {
		return $this->displayTextTemplates[$verbosity] ?: $this->displayTextTemplates[0];
	}

	public function getAllDisplayTextTemplates(): array {
		return $this->displayTextTemplates;
	}

	public function setDisplayTextTemplate(int $verbosity, string $template): Profile {
		$this->displayTextTemplates[$verbosity] = $template;
		return $this;
	}

	public function clearDisplayTextTemplate(): Profile {
		$this->displayTextTemplates = [];
		return $this;
	}

	public function getUrlTemplate(): string {
		return $this->urlTemplate;
	}

	public function setUrlTemplate(string $template): Profile {
		$this->urlTemplate = $template;
		return $this;
	}

	public function getIconUrlTemplate(): string {
		return $this->iconUrlTemplate;
	}

	public function setIconUrlTemplate(string $template): Profile {
		$this->iconUrlTemplate = $template;
		return $this;
	}

	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'name' => $this->getName(),
			'headerConstraints' => $this->getHeaderConstraints(),
			'parameterConstraints' => $this->getParameterConstraints(),
			'displayTextTemplates' => $this->getAllDisplayTextTemplates(),
			'urlTemplate' => $this->getUrlTemplate(),
			'iconUrlTemplate' => $this->getIconUrlTemplate()
		];
	}
}
