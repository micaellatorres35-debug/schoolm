function schpFormatCurrency(amount) {
    const value = Number(amount) || 0;
    return '₱' + value.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function schpRenderTransactions(rows) {
    const body = document.getElementById('schpTransactionsBody');
    if (!body) return;

    if (!rows || rows.length === 0) {
        body.innerHTML = '<tr><td colspan="4" class="schp-no-data">No Data Found</td></tr>';
        return;
    }

    body.innerHTML = rows.map(function (row) {
        return '<tr>' +
            '<td>' + (row.applied_at ?? '') + '</td>' +
            '<td>' + (row.scholarship_name ?? '') + '</td>' +
            '<td>' + schpFormatCurrency(row.award_amount) + '</td>' +
            '<td>' + (row.status ?? '') + '</td>' +
            '</tr>';
    }).join('');
}

function schpShowError() {
    const body = document.getElementById('schpTransactionsBody');
    if (body) {
        body.innerHTML = '<tr><td colspan="4" class="schp-no-data">Unable to load data</td></tr>';
    }
}

function schpLoadTransactions() {
    fetch(BASE_URL + '/scholarship/get-scholarship')
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || data.success === false) {
                schpShowError();
                return;
            }
            schpRenderTransactions(data.transactions);
        })
        .catch(function () {
            schpShowError();
        });
}

function schpInit() {
    schpLoadTransactions();
}

document.addEventListener('DOMContentLoaded', schpInit);
document.addEventListener('page:loaded', schpInit);