import Vue from 'vue'
import VueClipboard from 'vue-clipboard2'
import PersonalSettings from './Settings/PersonalSettings'
import { Tooltip } from '@nextcloud/vue'
import store from './store'
import Vuex from 'vuex'

Vue.directive('tooltip', Tooltip)
Vue.use(VueClipboard)
Vue.use(Vuex)

Vue.prototype.t = t
Vue.prototype.n = n
Vue.prototype.OC = OC
Vue.prototype.OCA = OCA
Vue.prototype.OCP = OCP

Vue.prototype.t = t

const View = Vue.extend(PersonalSettings)
const flowWebhooks = new View({
	store,
})
flowWebhooks.$mount('#flow_webhooks')