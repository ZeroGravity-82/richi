document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.js-operation-delete')) {
            const deleteUrl   = target.dataset.url;
            // const operationName = target.dataset.name;

            if (confirm(`Do you really want to delete operation?`)) {
                deleteOperation(deleteUrl)
                    .then(() => location.reload())
                    .catch(error => console.error(error));
            }
        }
    });
});

function deleteOperation(url = '') {
    return fetch(url, {
        method: 'DELETE',
    }).then(response => response.json());
}
