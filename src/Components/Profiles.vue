<template>
	<div id="webhooks_profiles">
		<div class="section">
			<h3 class="configured-profiles">
				{{ t('flow_webhooks', 'Configured profiles') }}
			</h3>
			<div class="profile-wrapper">
				<transition-group v-if="Object.keys(profiles).length > 0" name="slide">
					<Profile v-for="(profile, profile_id) in profiles"
						:id="profile_id"
						:key="profile_id"
						:profile="profile" />
				</transition-group>
				<p v-else>
					No profiles configured yet
				</p>
			</div>
		</div>
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
}
</script>

<style>
	@import './../styles/profiles.scss';
</style>
