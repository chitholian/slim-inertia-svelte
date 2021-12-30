<script>
    import {createEventDispatcher} from "svelte";

    export let dpp = 50, total = 400, page = 1, query = {};
    let pageCount = 1
    let showPages = []
    let dppValues = [10, 15, 20, 25, 50, 75, 100, 300, 500, 700, 1000]
    $: {
        createPaging(dpp, total, page, query)
    }

    let dispatch = createEventDispatcher()

    function createPaging(dpp, total, page) {
        let nPages = Math.max(1, Math.ceil(Number(total) / Number(dpp)));
        let pages = [1]
        if (nPages > 1) pages.push(2)
        for (let i = page - 2; i <= 2 + page; i++) {
            if (i > 2 && i < nPages - 1) pages.push(i)
        }
        if (nPages > 3) {
            pages.push(nPages - 1)
            pages.push(nPages)
        } else if (nPages > 2) {
            pages.push(nPages)
        }
        pageCount = nPages
        showPages = pages
        if (!dppValues.includes(dpp)) {
            dppValues = [...dppValues, parseInt(dpp)]
        }
    }

    function dppChanged(e) {
        let item = e.target.value
        dispatch('dpp', item)
    }

    function pageShown(p) {
        return p < 3 || p > pageCount - 2 || (p < page + 3 && p > page - 3)
    }

    function showDots(p) {
        return page > 5 && p === 2 || page < pageCount - 4 && p === pageCount - 1
    }

    function urlFor(p) {
        let q = {...query, page: p, dpp}
        let url = Object.keys(q).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(q[k])
        }).join('&')
        return window.location.pathname + '?' + url
    }
</script>
<ul class="pagination m-1">
    <li class="page-item d-flex align-items-center"><span>Per Page</span>
        <select class="form-control ml-1 mr-1 w-auto h-100" on:change={dppChanged} bind:value={dpp}>
            {#each dppValues as dpp, i (dpp)}
                <option value={dpp}>{dpp}</option>
            {/each}
        </select>
    </li>
    <li class="page-item" class:disabled={page <= 1}>
        <a href={urlFor(Math.min(page - 1, pageCount))} class="page-link">&laquo; Prev</a>
    </li>
    {#each showPages as p (p)}
        <li class="page-item" class:active={p === page} class:disabled={showDots(p)}>
            {#if showDots(p)}
                <span class="page-link">&hellip;</span>
            {:else}
                <a href={urlFor(p)} class="page-link">{p}</a>
            {/if}
        </li>
    {/each}
    <li class="page-item" class:disabled={page >= pageCount}>
        <a href={urlFor(Math.max(page + 1, 1))} class="page-link">Next &raquo;</a>
    </li>
</ul>
