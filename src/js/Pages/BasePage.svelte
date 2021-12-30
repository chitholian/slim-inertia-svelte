<script>
    import {page} from "@inertiajs/inertia-svelte";
    import Alert from "../Components/Alert.svelte";
    import {PREFIX} from "../extra";

    export let server_conf = $page.props.server_conf || {}

    let msg
    $: msg = $page.props.message

</script>
<header>
    <nav class="navbar navbar-dark bg-primary navbar-expand">
        <a href="{PREFIX}" class="navbar-brand has-logo mr-2">
            <img src="{PREFIX}assets/logo.png" alt="Logo">
        </a>
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="{PREFIX}">Home</a></li>
            <li class="nav-item dropdown"><span class="nav-link">DropDown</span>
                <ul class="dropdown-menu">
                    <li class="dropdown-item"><a class="nav-link" href="{PREFIX}">Item One</a></li>
                    <li class="dropdown-item"><a class="nav-link" href="{PREFIX}">Item Two</a></li>
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="{PREFIX}">Another Menu</a></li>
        </ul>
        {#if server_conf.user}
            <ul class="navbar-nav flex-grow-0 ml-2">
                <li class="nav-item dropdown">
                    <strong class="border-danger border p-1 text-white">
                        <i class="fas fa-user"></i> {server_conf.user.username}
                    </strong>
                    <ul class=" dropdown-menu dropdown-menu-right">
                        <li class="dropdown-item">
                            <a href="{PREFIX}devices" class="nav-link">Devices</a>
                        </li>
                        <li class="dropdown-item">
                            <a href="{PREFIX}reports/logins" class="nav-link">Login Report</a>
                        </li>
                        <li class="dropdown-item">
                            <a href="{PREFIX}profile/change-password" class="nav-link">Change Password</a>
                        </li>
                        <li class="dropdown-item">
                            <a href="{PREFIX}logout" class="nav-link"
                               onclick="return confirm('Are you sure to logout ?')">Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
        {/if}
    </nav>
</header>
<main>
    {#if msg}
        <div style="position: fixed;top: 0; right: 0;z-index: 999999">
            <Alert data={msg} on:dismiss={() => msg = null}>{msg.m}</Alert>
        </div>
    {/if}
    <div class="clearfix"></div>
    <slot></slot>
</main>
<footer>
    <div class="version-info">
        <strong class="text-monospace">v{$page.props.server_conf.version}</strong> - Developed by
        <a href="https://github.com/chitholian" target="_blank">
            <i class="fab fa-github"></i> Atikur Rahman Chitholian
        </a>
    </div>
</footer>
