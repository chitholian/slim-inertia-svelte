<script>
    import BasePage from "./BasePage.svelte";
    import DataTable from "../Components/DataTable.svelte";
    import {Inertia} from "@inertiajs/inertia";
    import {confirmDangerous, PREFIX, titleCase} from "../extra";

    export let data = {}
    export let query = {}
    let selected = new Set()
    let paging
    $: paging = createPaging(data)
    let columns = [
        {
            label: 'Username',
            field: 'username',
        },
        {
            label: 'IP',
            field: 'ip',
            type: 'ipv4'
        },
        {
            label: 'User Agent',
            field: 'user_agent',
        },
        {
            label: 'Login',
            field: 'created_at',
        },
        {
            label: 'Logout',
            field: 'logout_time',
        },
        {
            label: 'Trusted',
            field: 'trusted',
            custom: 't',
        },
        {
            label: 'Actions',
            custom: 'a',
        }
    ];

    function createPaging(d) {
        return {
            page: d.page,
            total: d.total,
            dpp: d.dpp,
            query: {...query},
        }
    }

    function handleDppChange(e) {
        let dpp = e.detail
        Inertia.reload({
            data: {
                ...query,
                dpp,
            }
        })
    }

    function takeAction(ids, action) {
        if (!confirmDangerous(titleCase(action) + " " + ids.length + ' login(s)')) return
        Inertia.patch(`${PREFIX}reports/logins`, {
            // ...query,
            ids: ids,
            action: action,
        })
    }

    function cleanAll() {
        if (!confirmDangerous('Cleaning up all logins')) return
        Inertia.patch(`${PREFIX}reports/logins`, {
            // ...query,
            ids: [0],
            action: 'clean',
        })
    }

</script>

<svelte:head>
    <title>Login Report</title>
</svelte:head>

<BasePage>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header p-1">
                <div class="card-title m-0">
                    <strong>Login Report</strong>
                    <button class="btn btn-danger float-right" on:click={cleanAll}>
                        <i class="fas fa-database"></i> Clean All
                    </button>
                </div>
            </div>
            <div class="card-body p-0 m-0">
                <DataTable rows={data.items} columns={columns} {paging} on:dpp={handleDppChange} selectable={true}
                           bind:selected>
                    <svelte:fragment slot="cell" let:item let:column>
                        {#if column.custom === 'a'}
                            <i class="fas fa-trash c-pointer text-danger" title="Delete"
                               on:click={() => takeAction([item.id], 'delete')}></i>
                        {:else if column.custom === 't'}
                            <span class="badge badge-{item.trusted === 'y' ? 'success' : 'danger'}">
                                {item.trusted === 'y' ? 'Yes' : 'No'}
                            </span>
                        {/if}
                    </svelte:fragment>
                </DataTable>
            </div>
            <div class="card-footer p-1">
                <button class="btn btn-danger" disabled={!selected.size}
                        on:click={()=>takeAction(Array.from(selected), 'delete')}>
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>
    </div>
</BasePage>
