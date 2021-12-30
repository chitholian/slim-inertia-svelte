export const PREFIX = window.axios.defaults.baseURL

export function confirmDangerous(msg) {
    let a = ((Math.random() * 10000).toFixed(0) % 10) + 1
    let b = ((Math.random() * 10000).toFixed(0) % 10) + 1
    let s = a + b;
    let res = prompt(`${msg}. What is ${a} + ${b} = ?`)
    if (res === null) return false
    if (parseInt(res) !== s) {
        alert("Incorrect response.")
        return false
    }
    return true
}

export function titleCase(str) {
    return str.replace(/\b\S/g, t => t.toUpperCase());
}

export function calcASR(info, total = null) {
    total = total == null ? Number(info.total) : Number(total)
    let success = Number(info.success)
    return ((total === 0 ? 0 : 100 * success / total) || 0).toFixed(0)
}

export function calcSum(items, fieldName) {
    return items.reduce((a, c) => a + Number(c[fieldName]), 0)
}
