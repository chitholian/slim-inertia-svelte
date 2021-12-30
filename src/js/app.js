window._ = require('lodash')
window.axios = require('axios')
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

window.axios.defaults.baseURL = function () {
    let url = document.getElementById("base_url")?.href
    return url || '/'
}()

import {createInertiaApp} from '@inertiajs/inertia-svelte'
import {InertiaProgress} from '@inertiajs/progress'

createInertiaApp({
    resolve: name => require(`./Pages/${name}.svelte`),
    setup({el, App, props}) {
        new App({target: el, props})
    },
})

InertiaProgress.init()
