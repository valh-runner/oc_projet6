
function previewImageForInputFile(inputFile, callback = false) {
            
    //Display of the image preview before form submission
    if (typeof(FileReader) == "undefined") {
        alert("Votre navigateur n'est pas capable d'afficher l'aperÃ§u de l'image avant soumission");
    }else{
        var imgPreviewHolder = $(inputFile).parent().parent().parent().find('.img-preview-holder');
        var imgPath = $(inputFile)[0].value;
        var filename = imgPath.substring(imgPath.lastIndexOf('\\') + 1);

        var reader = new FileReader();
        reader.onload = function(e) {

            imgPreviewHolder.empty(); // clear eventual previous preview image
            // add new preview image
            $("<img />", {
                "class": "img-preview",
                "src": e.target.result,
                "alt" : filename
            }).appendTo(imgPreviewHolder); 

            if(callback != false){
                callback(e.target.result);
            }
        }
        reader.readAsDataURL($(inputFile)[0].files[0]);
    }
}

function confirmTrickDeleteModal(id){
    $('#delete_warn_modal').modal();
    $('#deleteButton').attr('onclick', 'trickDelete('+id+')');
}
function trickDelete(id){
    document.location.href="/suppression_trick/"+id
}


jQuery(document).ready(function() {


    $('#notices_modal').modal();
});
