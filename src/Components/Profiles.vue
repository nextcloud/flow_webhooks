<template>
	<div id="webhooks_profiles">
		<h3 class="configured-profiles">
			{{ t('flow_webhooks', 'Configured profiles') }}
		</h3>
		<button class="profiles--add icon-add" @click="addProfile">
			{{ t('flow_webhooks', 'Add profile') }}
		</button>
		<transition-group v-if="Object.keys(profiles).length > 0"
			class="profile-wrapper"
			tag="div"
			name="slide">
			<Profile v-for="profile in profiles"
				:id="profile.id"
				:key="profile.id"
				:profile="profile" />
		</transition-group>
		<p v-else>
			No profiles configured yet
		</p>
	</div>
</template>

<script>
import Profile from './Profile'
import { mapState } from 'vuex'

export default {
	name: 'Profiles',
	components: {
		Profile,
	},
	computed: {
		...mapState({
			profiles: 'profiles',
		}),
	},
	methods: {
		addProfile() {
			this.$store.dispatch('createProfile', '')
		},
	},
}
</script>

<style>
	@import './../styles/profiles.scss';
</style>
