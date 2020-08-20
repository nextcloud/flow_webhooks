import Vue from 'vue'
import VueClipboard from 'vue-clipboard2'
import PersonalSettings from './Settings/PersonalSettings'
import { Tooltip } from '@nextcloud/vue'

Vue.directive('tooltip', Tooltip)
Vue.use(VueClipboard)

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA
Vue.prototype.OCP = OCP

export default new Vue({
	el: '#flow_webhooks',
	render: h => h(PersonalSettings),
})
