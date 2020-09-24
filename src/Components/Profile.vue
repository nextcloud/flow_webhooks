<template>
	<div v-click-outside="hide" class="profile-box" @click="show">
		<h4 class="profile--header">
			<input v-model="profile.name"
				type="text"
				:disabled="loading"
				:placeholder="t('flow_webhooks', 'Unnamed profile') + ' ' + profile.id"
				:class="{ 'icon-loading-small': loading }">
			<input
				:id="'save-' + id"
				:value="t('flow_webhooks', 'Save')"
				:disabled="loading || !dirty"
				type="button"
				@click.stop="saveProfile">
			<input
				:id="'delete-' + id"
				:value="t('flow_webhooks', 'Delete')"
				:disabled="loading"
				type="button"
				@click.stop="deleteProfile">
		</h4>
		<div v-if="visible" class="profile--details">
			<h5>
				{{ t('flow_webhooks', 'Header constraints') }}
				<Actions :disabled="loading">
					<ActionButton icon="icon-add" @click.stop="addHeaderConstraint">
						{{ t('flow_webhooks', 'Add header constraint') }}
					</ActionButton>
				</Actions>
			</h5>
			<div v-for="(headerConstraint, index) in profile.headerConstraints" :key="'headers' + index">
				<input
					:id="'header-name-' + index"
					v-model="headerConstraint.key"
					placeholder="Header name"
					:disabled="loading"
					type="text">
				<input
					:id="'header-pattern-' + index"
					v-model="headerConstraint.rule"
					placeholder="/^(regex)pattern.*/"
					:disabled="loading"
					type="text">
				<Actions>
					<ActionButton icon="icon-delete" @click.stop="removeHeaderConstraint(index)">
						{{ t('flow_webhooks', 'Remove header constraint') }}
					</ActionButton>
				</Actions>
			</div>
			<h5>
				Parameter constraints
				<Actions>
					<ActionButton icon="icon-add" :disabled="loading" @click.stop="addParameterConstraint">
						{{ t('flow_webhooks', 'Add parameter constraint') }}
					</ActionButton>
				</Actions>
			</h5>
			<div v-for="(parameterConstraint, index) in profile.parameterConstraints" :key="'parameters' + index">
				<input
					:id="'param-name-' + index"
					v-model="parameterConstraint.key"
					class="col-dual"
					placeholder="Parameter name"
					type="text">
				<input
					:id="'param-pattern-' + index"
					v-model="parameterConstraint.rule"
					class="col-dual"
					placeholder="/^(regex)pattern.*/"
					:disabled="loading"
					type="text">
				<Actions>
					<ActionButton icon="icon-delete" :disabled="loading" @click.stop="removeParameterConstraint(index)">
						{{ t('flow_webhooks', 'Remove parameter constraint') }}
					</ActionButton>
				</Actions>
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
					v-model="profile.displayTextTemplates[verbosityLevel - 1]"
					class="col-wide"
					:disabled="loading"
					:placeholder="templateTemplate"
					type="text">
			</div>
			<h5>Link template</h5>
			<input
				:id="'url-template-' + id"
				v-model="profile.urlTemplate"
				class="col-full"
				:disabled="loading"
				:placeholder="templateTemplate"
				type="text">
			<h5>Icon-URL template</h5>
			<input
				:id="'icon-url-template-' + id"
				v-model="profile.iconUrlTemplate"
				class="col-full"
				:disabled="loading"
				:placeholder="templateTemplate"
				type="text">
		</div>
	</div>
</template>

<script>
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'

export default {
	name: 'Profile',
	components: {
		Actions, ActionButton,
	},
	props: {
		id: {
			type: Number,
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
			dirty: false,
			loading: false,
		}
	},
	computed: {
		templateTemplate() {
			return 'a {{ parameter.value }} template'
		},
	},
	watch: {
		profile: {
			handler() {
				this.dirty = true
			},
			deep: true,
		},
	},
	methods: {
		toggleVisibility() {
			this.visible = !this.visible
		},
		addHeaderConstraint() {
			this.profile.headerConstraints.push({ key: '', rule: '' })
		},
		addParameterConstraint() {
			this.profile.parameterConstraints.push({ key: '', rule: '' })
		},
		async saveProfile() {
			try {
				this.loading = true
				await this.$store.dispatch('pushUpdateProfile', this.profile)
				this.dirty = false
				this.error = null
			} catch (e) {
				console.error('Failed to save operation', e)
				this.error = e.response.data.ocs.meta.message
			}
			this.loading = false
		},
		async deleteProfile() {
			try {
				this.loading = true
				await this.$store.dispatch('deleteProfile', this.profile)
			} catch (e) {
				console.error('Failed to remove profile', e)
			}
			this.loading = false
		},
		removeHeaderConstraint(index) {
			this.profile.headerConstraints.splice(index, 1)
		},
		removeParameterConstraint(index) {
			this.profile.parameterConstraints.splice(index, 1)
		},
		hide() {
			if (!this.dirty) {
				this.visible = false
			}
		},
		show() {
			this.visible = true
		},
	},
}

</script>

<style lang="scss">
@import './../styles/profiles.scss';
</style>
