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
import { generateOcsUrl } from '@nextcloud/router'

Vue.use(Vuex)

const getApiUrl = (consumer, profileId) => {
	return generateOcsUrl('apps/flow_webhooks', 2) + 'api/v1/profile/' + consumer + (profileId ? '/' + profileId : '')
}

const constraintObjectToArray = (constraints) => {
	let headers = []
	for (const key in constraints) {
		headers = [...headers, ...constraints[key].map((rule) => ({
			key,
			rule,
		}))]
	}
	return headers
}

const constraintsArrayToObject = (constraints) => {
	const headers = {}
	for (const index in constraints) {
		if (!headers[constraints[index].key]) {
			headers[constraints[index].key] = []
		}
		headers[constraints[index].key].push(constraints[index].rule)
	}
	return headers
}

let profiles = loadState('flow_webhooks', 'profiles')
if (Array.isArray(profiles)) {
	profiles = {}
}
Object.keys(profiles).map(function(key, index) {
	profiles[key] = {
		...profiles[key],
		headerConstraints: constraintObjectToArray(profiles[key].headerConstraints),
		parameterConstraints: constraintObjectToArray(profiles[key].parameterConstraints),
	}
})

const store = new Vuex.Store({
	state: {
		profiles,
		consumer: loadState('flow_webhooks', 'consumer'),
	},
	mutations: {
		ADD_PROFILE(state, profile) {
			Vue.set(state.profiles, profile.id, profile)
		},
		SET_PROFILE(state, profile) {
			const newProfile = Object.assign({}, profile)
			Vue.set(state.profiles, profile.id, newProfile)
		},
		REMOVE_PROFILE(state, profile) {
			Vue.delete(state.profiles, profile.id)
		},
	},
	actions: {
		async createProfile({ commit }, name) {
			const newProfile = {
				id: 0,
				name,
			}
			const result = await axios.post(getApiUrl(this.state.consumer), newProfile)
			commit('ADD_PROFILE', result.data)
		},
		updateProfile({ commit }, profile) {
			commit('SET_PROFILE', profile)
		},
		async deleteProfile({ commit }, profile) {
			await axios.delete(getApiUrl(this.state.consumer, profile.id))
			commit('REMOVE_PROFILE', profile)
		},
		async pushUpdateProfile(context, profile) {
			context.commit('SET_PROFILE', profile)
			if (context.state.scope === 0) {
				await confirmPassword()
			}
			await axios.put(getApiUrl(this.state.consumer, profile.id), {
				...profile,
				headerConstraints: constraintsArrayToObject(profile.headerConstraints),
				parameterConstraints: constraintsArrayToObject(profile.parameterConstraints),
			})
			context.commit('SET_PROFILE', profile)
		},
	},
	getters: {
		getProfile(state) {
			return (id) => state.profiles[id]
		},
	},
})

export default store
