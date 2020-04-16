function delete_ajax(url, id, name) {
    if(confirm('Bist du dir sicher, dass du den Eintrag "' + name + '" löschen möchtest?')) {
        fetch(url + id, {
            method: 'DELETE',
        }).then(res => window.location.reload());
    }
}

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

if(window.innerWidth <= 765) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
        $('.sidebar .collapse').collapse('hide');
    };
}