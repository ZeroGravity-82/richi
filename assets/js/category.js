document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.js-category-delete')) {
            const deleteUrl    = target.dataset.url;
            const categoryName = target.dataset.name;

            if (confirm(`Do you really want to delete category "${categoryName}"?`)) {
                deleteCategory(deleteUrl)
                    .then(() => location.reload())
                    .catch(error => console.error(error));
            }
        }
    });
});

function deleteCategory(url = '') {
    return fetch(url, {
        method: 'DELETE',
    }).then(response => response.json());
}
