
function previewImageForInputFile(inputFile, callback = false) {
            
    //Display of the image preview before form submission
    if (typeof(FileReader) === "undefined") {
        alert("Votre navigateur n'est pas capable d'afficher l'aperçu de l'image avant soumission");
    }else{
        var imgPreviewHolder = $(inputFile).parent().parent().parent().find(".img-preview-holder");
        var imgPath = $(inputFile)[0].value;
        var filename = imgPath.substring(imgPath.lastIndexOf("\\") + 1);

        var reader = new FileReader();
        reader.onload = function(e) {

            imgPreviewHolder.empty(); // clear eventual previous preview image
            // add new preview image
            $("<img />", {
                "class": "img-preview",
                "src": e.target.result,
                "alt" : filename
            }).appendTo(imgPreviewHolder); 

            if(callback !== false){
                callback(e.target.result);
            }
        };
        reader.readAsDataURL($(inputFile)[0].files[0]);
    }
}

function confirmTrickDeleteModal(id){
    $("#delete_warn_modal").modal();
    $("#deleteButton").attr("onclick", 'trickDelete("+id+")');
}

function trickDelete(id){
    document.location.href="/suppression_trick/"+id;
}

function setActiveCurrentNavItem() {
    $(".navbar").find(".active").removeClass("active");

    $.each($(".navbar").find("li"), function() {
        $(this).toggleClass("active", 
            window.location.pathname === $(this).find("a").attr("href")
        );
    });
    
}

function displayPictureViewModal(pictureSrc, calledElement) {
    var currentImgElem = $(calledElement);
    var viewerModalElem = $("#picture_view_modal");
    viewerModalElem.find("img").attr("src", pictureSrc);
    viewerModalElem.find(".modal-title").text( currentImgElem.attr("alt") );
    viewerModalElem.modal();
}

jQuery(document).ready(function() {
    $("#notices_modal").modal();

    $("#action-see-medias").on("click", function(e) {
      $("div#action-medias").removeClass("d-block d-sm-none").addClass("d-none"); // unset display on mobile rule and set hide for all devices
      $("div#medias").removeClass("d-none d-sm-block"); // unset hide on mobile rule
    });

    setActiveCurrentNavItem();
});
