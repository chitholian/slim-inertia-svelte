<script>
    import {useForm} from '@inertiajs/inertia-svelte'
    import {page} from '@inertiajs/inertia-svelte';
    import {PREFIX} from "../../extra";
    import Alert from "../../Components/Alert.svelte";

    let msg
    $: msg = $page.props.message

    let form = useForm({
        username: null,
        password: null,
        captcha: null,
    })

    function submitForm() {
        $form.post(PREFIX + 'login', {
            onFinish: () => {
                reloadCap()
                $form.setStore('captcha', null)
            }
        })
    }

    let captchaTime
    $: {
        reloadCap(page)
    }

    function reloadCap() {
        captchaTime = (new Date()).getTime()
    }
</script>

<svelte:head>
    <title>Login Page</title>
</svelte:head>

<main class="abs-middle col-md-4">
    <form class="card card-primary mt-3" method="post" on:submit|preventDefault={submitForm}>
        <div class="card-header p-0 bg-primary">
            <div class="round-logo">
                <img src="{PREFIX}assets/logo.png" alt="Site Logo" class="img-fluid"/>
            </div>
        </div>
        <div class="card-body">
            {#if msg}
                <Alert data={msg} on:dismiss={() => msg = null}>{msg.m}</Alert>
            {/if}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Username</span>
                </div>
                <input type="text" class="form-control" maxlength="20" bind:value={$form.username} name="username"
                       required>
            </div>
            <small class="text-danger">{$form.errors.username || ''}</small>

            <div class="input-group mt-1">
                <div class="input-group-prepend">
                    <span class="input-group-text">Password</span>
                </div>
                <input type="password" class="form-control" bind:value={$form.password} name="password" required>
            </div>
            <small class="text-danger">{$form.errors.password || ''}</small>

            <div class="d-flex align-items-center justify-content-center mt-1 mb-1">
                <img src="{PREFIX}captcha?time={captchaTime}" alt="Captcha" class="mr-2 captcha">
                <i class="fas fa-sync c-pointer" title="Refresh" style="font-size: 1.5rem" on:click={reloadCap}></i>
            </div>

            <div class="input-group mt-1">
                <div class="input-group-prepend">
                    <span class="input-group-text">Captcha</span>
                </div>
                <input type="text" class="form-control" bind:value={$form.captcha} autocomplete="off" required>
            </div>
            <small class="text-danger">{$form.errors.captcha || ''}</small>
        </div>

        <div class="card-footer p-1">
            <button type="submit" class="btn btn-primary float-right">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </div>
    </form>
</main>
<footer>
    <div class="version-info">
        <strong class="text-monospace">v{$page.props.server_conf.version}</strong> - Developed by
        <a href="https://github.com/chitholian" target="_blank">
            <i class="fab fa-github"></i> Atikur Rahman Chitholian
        </a>
    </div>
</footer>

<style>
    .card-header {
        height: 128px;
    }

    .card-body {
        margin-top: 80px;
    }

    .img-fluid {
        padding: 8px;
        margin: 15px 0 0 4px;
    }
</style>
