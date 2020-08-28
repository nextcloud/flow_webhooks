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

namespace OCA\FlowWebhooks\Traits;

use OCP\IRequest;

trait RequestParameterHandling {
	protected function getParameterValue(IRequest $request, string $parameterName, ?string $default = null): ?string {
		$parameterValue = $request->getParam($parameterName, '');
		if ($parameterValue === '' && strpos($parameterName, '.')) {
			$keyStructure = explode('.', $parameterName);
			$top = array_shift($keyStructure);
			$sub = $request->getParam($top);
			if (is_array($sub)) {
				foreach ($keyStructure as $key) {
					if (is_array($sub) && isset($sub[$key])) {
						$sub = $sub[$key];
						continue;
					}
					break;
				}
				if(!is_array($sub)) {
					$parameterValue = (string)$sub;
				}
			}
		}
		if($parameterValue === '') {
			return $default;
		}
		return $parameterValue;
	}
}
