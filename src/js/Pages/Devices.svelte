<script>
    import BasePage from "./BasePage.svelte";
    import DataTable from "../Components/DataTable.svelte";
    import {Inertia} from "@inertiajs/inertia";
    import {confirmDangerous, PREFIX, titleCase} from "../extra";

    export let data = {}
    export let query = {}
    let selected = new Set()
    let paging
    $: paging = createPaging(data, query)
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
            label: 'Last Seen',
            field: 'last_active',
            custom: 'ls',
        },
        {
            label: 'Actions',
            field: 'trusted',
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
        if (!confirmDangerous(titleCase(action) + " " + ids.length + ' device(s)')) return
        Inertia.patch(`${PREFIX}devices`, {
            // ...query,
            ids: ids,
            action: action,
        })
    }

    function cleanAll() {
        if (!confirmDangerous('Cleaning up all devices')) return
        Inertia.patch(`${PREFIX}devices`, {
            // ...query,
            ids: [0],
            action: 'clean',
        })
    }

</script>

<svelte:head>
    <title>Devices</title>
</svelte:head>

<BasePage>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header p-1">
                <div class="card-title m-0">
                    <strong>List of Devices</strong>
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
                            {#if item.trusted === 'y'}
                                <i class="fas fa-user-shield c-pointer text-success" title="Distrust this device"
                                   on:click={() => takeAction([item.id], 'distrust')}></i>
                            {:else}
                                <i class="fas fa-user-shield c-pointer text-danger" title="Trust this device"
                                   on:click={() => takeAction([item.id], 'trust')}></i>
                            {/if}
                            <i class="fas fa-trash c-pointer ml-2 text-danger" title="Delete"
                               on:click={() => takeAction([item.id], 'delete')}></i>
                        {:else if column.custom === 'ls'}
                            {#if item.this_device === 'y'}
                                <span class="badge badge-success">This Device</span>
                            {:else}
                                {item.last_seen}
                            {/if}
                        {/if}
                    </svelte:fragment>
                </DataTable>
            </div>
            <div class="card-footer p-1">
                <div class="btn-group" role="group">
                    <button class="btn btn-danger" disabled={!selected.size}
                            on:click={()=>takeAction(Array.from(selected), 'delete')}> Delete
                    </button>
                    <button class="btn btn-warning" disabled={!selected.size}
                            on:click={()=>takeAction(Array.from(selected), 'distrust')}> Distrust
                    </button>
                    <button class="btn btn-success" disabled={!selected.size}
                            on:click={()=>takeAction(Array.from(selected), 'trust')}> Trust
                    </button>
                </div>
            </div>
        </div>
    </div>
</BasePage>
