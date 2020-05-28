function delete_ajax(url, id, name) {
    const modal = '<div class="modal fade" id="deleteModal'+ id +'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="exampleModalLabel">'+ name +' löschen?</h5><button type="button" class="close" data-dismiss="modal" aria-label="Abbrechen"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">Bist du dir sicher, dass du den Eintrag <b>'+ name +'</b> löschen möchtest?</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button><a href="javascript:definitively_delete_ajax(\''+ url +'\', \''+ id +'\')" class="btn btn-danger">Löschen</a></div></div></div></div>';
    document.body.innerHTML += modal;
    $('#deleteModal'+ id +'').modal('show');
}

function definitively_delete_ajax(url, id) {
    fetch(url + id, {
        method: 'DELETE',
    }).then(res => window.location.reload());
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