document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.js-tag-delete')) {
            const deleteUrl   = target.dataset.url;
            const tagName = target.dataset.name;

            if (confirm(`Do you really want to delete tag "${tagName}"?`)) {
                deleteTag(deleteUrl)
                    .then(() => location.reload())
                    .catch(error => console.error(error));
            }
        }
    });
});

function deleteTag(url = '') {
    return fetch(url, {
        method: 'DELETE',
    }).then(response => response.json());
}
