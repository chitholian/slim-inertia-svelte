<script>
    import Pagination from "./Pagination.svelte";

    export let loading = false
    export let opaqueLoading = false
    export let rows = []
    export let keyName = 'id'
    export let numbers = true
    export let selectable = false
    export let columns = []
    export let paging = {
        page: 1,
        dpp: 50,
        total: 0,
        hidden: true,
    }

    let showing
    let sortBy = {}
    export let selected = new Set()

    $: {
        recomputeSelected(rows)
        sortItems(rows)
    }

    function recomputeSelected(r) {
        let newSet = new Set()
        r.forEach(i => {
            if (selected.has(i[keyName])) newSet.add(i[keyName])
        })
        selected = newSet
        return newSet
    }

    function toggleAll() {
        if (selected.size !== showing.length) {
            selected = new Set(showing.map(i => i[keyName]))
        } else {
            selected = new Set()
        }
    }

    function sortClassNames(col) {
        if (sortBy[col.field]) {
            return 'sort sort-' + sortBy[col.field]
        }
        return (col.sort === 'none' || !col.field) ? '' : 'sort sort-both'
    }

    function sortNow(col, e) {
        if (col.field && col.sort !== 'none') {
            let currentKeys = e.shiftKey ? {...sortBy} : {}
            if (sortBy[col.field]) {
                sortBy = {...currentKeys, [col.field]: sortBy[col.field] === 'asc' ? 'desc' : 'asc'}
            } else {
                sortBy = {...currentKeys, [col.field]: 'asc'}
            }
            sortItems();
        }
    }

    function toggleItem(item) {
        if (selected.has(item)) {
            selected.delete(item)
            selected = new Set(selected)
        } else {
            selected = new Set([...selected, item])
        }
    }

    function sortItems() {
        let items = rows
        if (Object.keys(sortBy).length !== 0 && items.length) {
            items = items.sort((a, b) => {
                let diff = 0
                // Iterate keys.
                for (let k of Object.keys(sortBy)) {
                    let x = a[k], y = b[k]
                    let col = columns.find(i => i.field === k)
                    if (!col) continue
                    if (col.sorter) {
                        diff = Math.ceil(col.sorter(a, b))
                    } else if (col.sort === 'number') {
                        x = Number(x)
                        y = Number(y)
                        diff = x - y
                    } else if (col.sort === 'ipv4') {
                        const num1 = Number(x.split(".").map((num) => (`000${num}`).slice(-3)).join(""))
                        const num2 = Number(y.split(".").map((num) => (`000${num}`).slice(-3)).join(""))
                        diff = num1 - num2
                    } else {
                        diff = x === y ? 0 : x == null ? -1 : y == null ? 1 : x < y ? -1 : 1
                    }
                    if (diff === 0) continue
                    if (sortBy[k] === 'desc') diff *= -1
                    break
                }

                return diff
            })
        }
        showing = items
        return items
    }
</script>
<div class="datatable">
    {#if !paging.hidden}
        <Pagination {...paging} on:dpp/>
    {/if}
    <table class="text-center table table-bordered table-striped m-auto {loading && opaqueLoading ? 'op-50' : ''}">
        <thead>
        <tr>
            {#if selectable}
                <th class="fit">
                    <input type="checkbox" on:change|preventDefault={toggleAll}
                           checked={selected.size && selected.size === showing.length}
                           indeterminate={selected.size && selected.size !== showing.length}>
                </th>
            {/if}
            {#if numbers}
                <th class="fit">#</th>
            {/if}
            {#each columns as col, i (i)}
                {#if !col.hidden}
                    <th class="{col.headClass + ' ' + sortClassNames(col, sortBy)}"
                        on:click={(e) => sortNow(col, e)}>
                        {col.label}
                    </th>
                {/if}
            {/each}
        </tr>
        </thead>
        {#if showing.length}
            <tbody>
            {#each showing as item, i (item[keyName])}
                <tr>
                    {#if selectable}
                        <td>
                            <input type="checkbox" on:change|preventDefault={() => toggleItem(item[keyName])}
                                   checked={selected.has(item[keyName])}>
                        </td>
                    {/if}
                    {#if numbers}
                        <td>{(paging.page - 1) * paging.dpp + i + 1}</td>
                    {/if}
                    {#each columns as col, i (i)}
                        {#if !col.hidden}
                            <td class="{col.cellClass || ''}">
                                {#if col.custom}
                                    <slot name="cell" {item} column={col}></slot>
                                {:else if col.callback}
                                    {col.callback(item, i)}
                                {:else if col.field}
                                    {item[col.field] ?? ''}
                                {/if}
                            </td>
                        {/if}
                    {/each}
                </tr>
            {/each}
            </tbody>
            <slot name="footer"></slot>
        {/if}
    </table>
    {#if !showing.length && !loading}
        <div class="alert alert-warning p-2 mt-1 text-center">No Data Available</div>
    {/if}
    {#if loading && !showing.length}
        <div class="text-center p-2"><span class="spinner-border"></span></div>
    {/if}
    {#if selected.size}
        <div class="p-2">{selected.size} Item(s) Selected</div>
    {/if}
</div>
