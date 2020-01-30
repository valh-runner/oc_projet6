jQuery(document).ready(function() {
    $newInputFile = $('input[id="account_accountPicture"]');
    //On this file input change
    $newInputFile.on('change', function(event) {

        //Make fileType field, styled by bootstrap, look usable
        var inputFile = event.currentTarget;
        $(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);

        previewImageForInputFile(inputFile);
    });

    $('#action-delete-accountPicture').on('click', function(e) {
        //if featuredPicture file input hold a file

            // file input reinitialization
            $inputFile = $('input[id="account_accountPicture"]');
            $inputFile.val('');
            //$InputFile.attr('placeholder', 'Sélectionner un fichier image');
            $inputFile.parent().find('.custom-file-label').text('Sélectionner un fichier image');

            //deletion of preview image
            var imgPreviewHolder = $inputFile.parent().parent().parent().find('.img-preview-holder');
            imgPreviewHolder.empty();

            $('input[id="account_pictureDeletionState"]').attr('value', 'true'); //to know to apply the main picture deletion
    });

    imgPreviewSearch = $('input[id="trick_featuredPicture"]').parent().parent().parent().find('.img-preview');
    // if featuredPicture is preset
    if( imgPreviewSearch.length == 1 ){
        funcPreviewImageInTitleBanner(imgPreviewSearch.attr('src')); //set same picture in banner title
    }
});
