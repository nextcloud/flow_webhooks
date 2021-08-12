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

return [
	'ocs' => [
		['name' => 'Trigger#receive', 'url' => '/api/v1/hook/{urlId}', 'verb' => 'POST'],

		[
			'name' => 'Profile#addProfile',
			'url' => '/api/v1/profile/{consumerType}',
			'verb' => 'POST',
			'requirements' => [
				'consumerType' => '(user|instance)'
			]
		],
		[
			'name' => 'Profile#removeProfile',
			'url' => '/api/v1/profile/{consumerType}/{profileId}',
			'verb' => 'DELETE',
			'requirements' => [
				'consumerType' => '(user|instance)'
			]
		],
		[
			'name' => 'Profile#editProfile',
			'url' => '/api/v1/profile/{consumerType}/{profileId}',
			'verb' => 'PUT',
			'requirements' => [
				'consumerType' => '(user|instance)'
			]
		],
	],
];
