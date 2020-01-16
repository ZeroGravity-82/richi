document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.js-account-delete')) {
            const deleteUrl   = target.dataset.url;
            const accountName = target.dataset.name;

            if (confirm(`Do you really want to delete account "${accountName}"?`)) {
                deleteAccount(deleteUrl)
                    .then(() => location.reload())
                    .catch(error => console.error(error));
            }
        }
    });
});

function deleteAccount(url = '') {
    return fetch(url, {
        method: 'DELETE',
    }).then(response => response.json());
}
