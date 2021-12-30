<script>
    import {createEventDispatcher, onDestroy} from "svelte";

    let dispatch = createEventDispatcher()
    export let data = {}
    let timer = null
    let msg
    $: {
        buildMessage(data, $$slots)
    }

    function buildMessage() {
        clearTimeout(timer)
        msg = {
            showing: true,
            dismissible: true,
            v: 'danger',
            m: '',
            timeout: 3000,
            ...data,
        }
        if (msg.timeout) {
            timer = setTimeout(dismiss, msg.timeout)
        }
    }

    function dismiss() {
        msg = {...msg, showing: false}
        dispatch('dismiss')
        clearTimeout(timer)
    }

    onDestroy(() => clearTimeout(timer))
</script>
{#if msg.showing}
    <div class="alert alert-{msg.v} fade show {msg.dismissible ? 'alert-dismissible' : ''} p-3 pr-5 m-1" role="alert">
        <slot>{msg.m}</slot>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" on:click={dismiss}>
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
{/if}
