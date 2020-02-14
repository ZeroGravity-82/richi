document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.js-fund-delete')) {
            const deleteUrl = target.dataset.url;
            const fundName  = target.dataset.name;

            if (confirm(`Do you really want to delete fund "${fundName}"?`)) {
                deleteFund(deleteUrl)
                    .then(() => location.reload())
                    .catch(error => console.error(error));
            }
        }
    });
});

function deleteFund(url = '') {
    return fetch(url, {
        method: 'DELETE',
    }).then(response => response.json());
}
