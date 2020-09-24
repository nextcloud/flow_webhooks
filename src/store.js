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

import Vue from 'vue'
import Vuex from 'vuex'
import { loadState } from '@nextcloud/initial-state'
import confirmPassword from '@nextcloud/password-confirmation'
import axios from '@nextcloud/axios'

Vue.use(Vuex)

const store = new Vuex.Store({
	state: {
		profiles: loadState('flow_webhooks', 'profiles'),
		consumer: loadState('flow_webhooks', 'consumer'),
	},
	computed: {
		getApiUrl(profileId) {
			return 'FIXME'
		},
	},
	mutations: {
		// TODO: test whether this is working at all
		updateProfile(state, profile, profileId) {
			const index = state.profiles.findIndex((item) => profileId === item.id)
			const newProfile = Object.assign({}, profile)
			Vue.set(state.profiles, index, newProfile)
		},
	},
	actions: {
		updateProfile(context, profile, profileId) {
			context.commit('updateProfile', {
				...profile,
				profileId,
			})
		},
		async pushUpdateProfile(context, profile, profileId) {
			if (context.state.scope === 0) {
				await confirmPassword()
			}
			let result
			if (profileId < 0) {
				result = await axios.post(this.getApiUrl(''), profile)
			} else {
				result = await axios.put(this.getApiUrl(`/${profileId}`), profile)
			}
			Vue.set(profile, 'id', result.data.ocs.data.id)
			context.commit('updateProfile', profile, profileId)
		},
	},
})

export default store
