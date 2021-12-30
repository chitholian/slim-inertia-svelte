<script>

    import BasePage from "../BasePage.svelte";
    import {PREFIX} from "../../extra";
    import Alert from "../../Components/Alert.svelte";
    import {useForm} from "@inertiajs/inertia-svelte";

    export let userInfo = {}

    let form = useForm({
        username: null,
        password_old: null,
        password: null,
        password2: null,
        logout_all: true,
        ...userInfo,
    })

    let passwordsMismatch = false

    function submitForm() {
        $form.patch(PREFIX + 'profile/change-password')
    }

    function checkPasswords() {
        passwordsMismatch = $form.password && $form.password2 && ($form.password !== $form.password2)
    }

</script>

<svelte:head>
    <title>Change Password</title>
</svelte:head>

<BasePage>
    <div class="abs-middle col-md-4">
        <form class="card card-primary mt-3" method="post" on:submit|preventDefault={submitForm}>
            <div class="card-header">
                <div class="card-title m-0">
                    Change User Credentials
                </div>
            </div>
            <div class="card-body p-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Username</span>
                    </div>
                    <input type="text" class="form-control" maxlength="20" bind:value={$form.username} required>
                </div>
                <small class="text-danger">{$form.errors.username || ''}</small>

                <div class="input-group mt-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Current Password</span>
                    </div>
                    <input type="password" class="form-control" bind:value={$form.password_old} required>
                </div>
                <small class="text-danger">{$form.errors.password_old || ''}</small>

                <div class="input-group mt-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">New Password</span>
                    </div>
                    <input type="password" class="form-control" bind:value={$form.password} maxlength="190"
                           on:input={checkPasswords} required>
                </div>
                <small class="text-danger">{$form.errors.password || ''}</small>

                <div class="input-group mt-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Confirm Password</span>
                    </div>
                    <input type="password" class="form-control" bind:value={$form.password2} on:input={checkPasswords}
                           required>
                </div>
                {#if passwordsMismatch}
                    <small class="text-danger">Passwords mismatch</small>
                {:else}
                    <small class="text-danger">{$form.errors.password2 || ''}</small>
                {/if}

                {#if $form.errors.error}
                    <Alert data={{m:$form.errors.error}} on:dismiss={$form.clearErrors('error')}/>
                {/if}

            </div>

            <div class="card-footer p-1">
                <label class="form-check-label">
                    <input type="checkbox" class="align-middle" bind:checked={$form.logout_all}> Logout All Devices
                </label>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </div>
        </form>
    </div>
</BasePage>
