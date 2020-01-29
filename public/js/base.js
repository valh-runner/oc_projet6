
function confirmTrickDeleteModal(id){
    $('#delete_warn_modal').modal();
    $('#deleteButton').attr('onclick', 'trickDelete('+id+')');
}
function trickDelete(id){
    document.location.href="/suppression_trick/"+id
}

$('#notices_modal').modal();