document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.js-person-delete')) {
            const deleteUrl  = target.dataset.url;
            const personName = target.dataset.name;

            if (confirm(`Do you really want to delete person "${personName}"?`)) {
                deletePerson(deleteUrl)
                    .then(() => location.reload())
                    .catch(error => console.error(error));
            }
        }
    });
});

function deletePerson(url = '') {
    return fetch(url, {
        method: 'DELETE',
    }).then(function (response) { console.log(response); response.json()});
}
