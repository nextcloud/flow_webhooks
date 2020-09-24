<template>
	<div class="profile-box">
		<h4 class="profile--header" @click="toggleVisibility">
			<input v-model="profile.name" type="text" :placeholder="t('flow_webhooks', 'Unnamed profile') + ' ' + profile.id">
			<input
				:id="'save-' + id"
				:value="t('flow_webhooks', 'Save')"
				type="button"
				@click.stop="saveProfile">
			<input
				:id="'delete-' + id"
				:value="t('flow_webhooks', 'Delete')"
				type="button"
				@click.stop="deleteProfile">
		</h4>
		<div v-if="visible" class="profile--details">
			<h5>Header constraints <button v-tooltip="t('flow_webhooks', 'Add header constraint')" class="icon-add" /></h5>
			<div v-for="(headerPatterns, headerName) in profile.headerConstraints" :key="headerName">
				<div v-for="(pattern) in headerPatterns" :key="pattern" style="display: inline-block">
					<input
						:id="'header-name-' + id"
						placeholder="Header name"
						type="text"
						:value="headerName">
					<input
						:id="'header-pattern-' + id"
						placeholder="/^(regex)pattern.*/"
						:value="pattern"
						type="text">
				</div>
			</div>
			<h5>Parameter constraints <button v-tooltip="t('flow_webhooks', 'Add parameter constraint')" class="icon-add" /></h5>
			<div v-for="(parameterPatterns, parameterName) in profile.parameterConstraints" :key="parameterName">
				<div v-for="(pattern) in parameterPatterns" :key="pattern" style="display: inline-block">
					<input
						:id="'param-name-' + id"
						class="col-dual"
						placeholder="Parameter name"
						type="text"
						:value="parameterName">
					<input
						:id="'param-pattern-' + id"
						class="col-dual"
						placeholder="/^(regex)pattern.*/"
						:value="pattern"
						type="text">
				</div>
			</div>
			<h5>Display text templates</h5>
			<div>
				<span v-tooltip="t('flow_webhooks', 'Verbosity level')" class="col-slim">Vrb.</span>
				<span class="col-wide">Template</span>
			</div>
			<div v-for="verbosityLevel in 4" :key="verbosityLevel" style="display: inline-block">
				<label
					:id="'verbosity-level-lbl-' + verbosityLevel + '-' + id"
					class="col-slim"
					:for="'verbosity-level-' + verbosityLevel + '-' + id"
					type="text">{{ verbosityLevel - 1 }}</label>
				<input
					:id="'verbosity-level-' + verbosityLevel + '-' + id"
					class="col-wide"
					:placeholder="templateTemplate"
					:value="profile.displayTextTemplates[verbosityLevel - 1] || ''"
					type="text">
			</div>
			<h5>Link template</h5>
			<input
				:id="'url-template-' + id"
				class="col-full"
				:placeholder="templateTemplate"
				:value="profile.urlTemplate"
				type="text">
			<h5>Icon-URL template</h5>
			<input
				:id="'icon-url-template-' + id"
				class="col-full"
				:placeholder="templateTemplate"
				:value="profile.iconUrlTemplate"
				type="text">
		</div>
	</div>
</template>

<script>
export default {
	name: 'Profile',
	props: {
		id: {
			type: String,
			required: true,
		},
		profile: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			visible: false,
		}
	},
	computed: {
		templateTemplate() {
			return 'a {{ parameter.value }} template'
		},
	},
	methods: {
		toggleVisibility() {
			this.visible = !this.visible
		},
		updateProfile() {
			if (!this.dirty) {
				this.dirty = true
			}

			this.error = null
			this.$store.dispatch('updateProfile', this.profile)
		},
		async saveProfile() {
			try {
				await this.$store.dispatch('pushUpdateProfile', this.profile)
				this.dirty = false
				this.error = null
				this.originalProfile = JSON.parse(JSON.stringify(this.profile))
			} catch (e) {
				console.error('Failed to save operation', e)
				this.error = e.response.data.ocs.meta.message
			}
		},
		async deleteProfile() {
			try {
				await this.$store.dispatch('deleteProfile', this.profile)
			} catch (e) {
				console.error('Failed to remove profile', e)
			}
		},
	},
}

</script>

<style lang="scss">
@import './../styles/profiles.scss';
</style>
