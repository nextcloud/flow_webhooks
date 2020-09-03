<template>
	<div id="flow_webhooks" class="section">
		<h2>{{ t('flow_webhooks', 'Webhooks') }}</h2>
		<p>
			{{ t('flow_webhooks', 'Your personal webhook endpoint:') }} <strong id="webhookUrl">{{ webhookEndpoint }}</strong>
			<button
				v-clipboard:copy="webhookEndpoint"
				v-clipboard:success="onCopy"
				v-clipboard:error="onCopyFailed"
				type="button"
				class="icon icon-clippy">
				&nbsp;
			</button>
			<span v-if="showConfirmationText" class="confirmation__warning">{{ confirmationText }}</span>
		</p>
		<p>
			<a href="workflow">{{ t('flow_webhooks', 'Configure webhooks in the Flow settings.') }}</a>
		</p>
		<Profiles />
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import Profiles from '../Components/Profiles'

export default {
	name: 'PersonalSettings',
	components: {
		Profiles,
	},

	data() {
		return {
			confirmationText: '',
			showConfirmationText: false,
		}
	},

	beforeMount() {
		this.webhookEndpoint = loadState('flow_webhooks', 'webhookEndpoint')
	},

	mounted() {
	},

	methods: {
		onCopy() {
			this.confirmationText = t('flow_webhooks', 'Link copied to the clipboard!')
			this.showConfirmationText = true
			setTimeout(function() {
				this.showConfirmationText = false
			}, 800)
		},
		onCopyFailed() {
			this.confirmationText = t('flow_webhooks', 'Error')
			this.showConfirmationText = true
			setTimeout(function() {
				this.showConfirmationText = false
			}, 800)
		},
	},
}
</script>

<style lang="scss" scoped>
.icon {
	background-size: 16px 16px;
	display: inline-block;
	position: relative;
	top: 3px;
	margin-left: 5px;
	margin-right: 8px;
}
</style>
