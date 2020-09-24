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

const getApiUrl = (profileId) => {
	return generateOcsUrl('apps/flow_webhooks', 2) + 'api/v1/profile' + (profileId ? '/' + profileId : '')
}

const store = new Vuex.Store({
	state: {
		profiles: loadState('flow_webhooks', 'profiles'),
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
			const result = await axios.post(getApiUrl(), newProfile)
			commit('ADD_PROFILE', result.data)
		},
		updateProfile({ commit }, profile) {
			commit('updateProfile', profile)
		},
		async deleteProfile({ commit }, profile) {
			await axios.delete(getApiUrl(profile.id))
			commit('REMOVE_PROFILE', profile)
		},
		async pushUpdateProfile(context, profile) {
			if (context.state.scope === 0) {
				await confirmPassword()
			}
			await axios.put(getApiUrl(profile.id), profile)
			context.commit('SET_PROFILE', profile)
		},
	},
})

export default store
