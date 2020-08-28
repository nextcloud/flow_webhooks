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

class ResurrectedRequest implements \OCP\IRequest {

	/** @var array */
	private $headers;
	/** @var array */
	private $parameters;
	/** @var string */
	private $id;

	public function __construct(string $id, array $headers, array $parameters) {
		$this->id = $id;
		$this->headers = $headers;
		$this->parameters = $parameters;
	}

	/**
	 * @inheritDoc
	 */
	public function getHeader(string $name): string {
		return $this->headers[$name] ?: '';
	}

	/**
	 * @inheritDoc
	 */
	public function getParam(string $key, $default = null) {
		return $this->parameters[$key] ?: $default;
	}

	/**
	 * @inheritDoc
	 */
	public function getParams(): array {
		return $this->parameters;
	}

	/**
	 * @inheritDoc
	 */
	public function getMethod(): string {
		return 'POST';
	}

	/**
	 * @inheritDoc
	 */
	public function getUploadedFile(string $key) {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getEnv(string $key) {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getCookie(string $key) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function passesCSRFCheck(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function passesStrictCookieCheck(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function passesLaxCookieCheck(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteAddress(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getServerProtocol(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getHttpProtocol(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestUri(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getRawPathInfo(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getPathInfo() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getScriptName(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function isUserAgent(array $agent): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getInsecureServerHost(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getServerHost(): string {
		return '';
	}
}
