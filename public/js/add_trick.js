
//Form enhancer
//-------------

function enhanceForm() {
    var $radios = $('input[name="trick[categoryType]"]');
    $radios.change(function() {
        var $checked = $radios.filter(':checked');
        if($checked.val() == 1) {
            $('#trick_newCategory').removeAttr('required');
            $('#trick_existantCategory').removeAttr('disabled');
            $('#trick_newCategory').attr('disabled', 'disabled');
            $('#trick_existantCategory').attr('required', 'required');
        } else if($checked.val() == 2) {
            $('#trick_existantCategory').removeAttr('required');
            $('#trick_newCategory').removeAttr('disabled');
            $('#trick_existantCategory').attr('disabled', 'disabled');
            $('#trick_newCategory').attr('required', 'required');
        } else {
            $('#trick_existantCategory').removeAttr('required');
            $('#trick_existantCategory').removeAttr('disabled');
            $('#trick_newCategory').removeAttr('required');
            $('#trick_newCategory').removeAttr('disabled');
        }
    });
}



// Setup "add and remove" functionnality for each CollectionType
//--------------------------------------------------------------

function addAndRemoveFunctionnality(ulClassname, isFileTypeEntity){
	var $collectionHolder;

	// setup an "add a video" link
	var $addVideoButton = $('<button type="button" class="btn btn-secondary">Ajouter un champ</button>');
	var $newLinkLi = $('<li></li>').append($addVideoButton);


    // Get the ul that holds the collection of videos
    $collectionHolder = $('ul.'+ ulClassname);

    // add a delete link to all of the existing video form li elements
    $collectionHolder.find('li').each(function() {
        addItemFormDeleteLink($(this));
    });

    // add the "add a tag" anchor and li to the videos ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    var index = $collectionHolder.find(':input').length;
    $collectionHolder.data('index', index);

    $addVideoButton.on('click', function(e) {
        // add a new video form
        addItemForm($collectionHolder, $newLinkLi, isFileTypeEntity);
    });

    //if no existant field, create default one
    if(index == 1){
    	addItemForm($collectionHolder, $newLinkLi, isFileTypeEntity);
    }
}

function addItemForm($collectionHolder, $newLinkLi, isFileTypeEntity) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');
    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;
    // You need this only if you didn't set 'label' => false in your tags field in TaskType
    // Replace '__name__label__' in the prototype's HTML to
    // instead be a number based on how many items we have
    // newForm = newForm.replace(/__name__label__/g, index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);
    // Display the form in the page in an li, before the "Add a tag" link li
    var $newForm = $(newForm);
    $newLinkLi.before($newForm);

    // add a delete link to the new form
    addItemFormDeleteLink($newForm);

    //If is FileType field
    if(isFileTypeEntity == true){

    	$newInputFile = $newForm.find('.custom-file-input');

    	//On this file input change
        $newInputFile.on('change', function(event) {

            //Make fileType field, styled by bootstrap, look usable
            var inputFile = event.currentTarget;
            $(inputFile).parent()
                .find('.custom-file-label')
                .html(inputFile.files[0].name);

            previewImageForInputFile(inputFile);
        });
    }
}

function addItemFormDeleteLink($videoFormLi) {
    $removeFormButton = $videoFormLi.find('button');
    $removeFormButton.on('click', function(e) {
        // remove the li for the video form
        $videoFormLi.remove();
    });
}

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

var initialTitleBannerSrc = null;

var funcPreviewImageInTitleBanner = function (filename) {
    initialTitleBannerSrc = $('#trick-head-banner').attr('src');
    $('#trick-head-banner').attr('src', filename);
}


function ReplaceInitialTitleBannerImage () {
    if(initialTitleBannerSrc != null){
        $('#trick-head-banner').attr('src', initialTitleBannerSrc);
    }
}


jQuery(document).ready(function() {
	addAndRemoveFunctionnality('videos', false);
	addAndRemoveFunctionnality('pictures', true);

    $newInputFile = $('input[id="trick_featuredPicture"]');
    //On this file input change
    $newInputFile.on('change', function(event) {

        //Make fileType field, styled by bootstrap, look usable
        var inputFile = event.currentTarget;
        $(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);

        previewImageForInputFile(inputFile, funcPreviewImageInTitleBanner);
    });

    $('#action-delete-featuredPicture').on('click', function(e) {
        //if featuredPicture file input hold a file

            // file input reinitialization
            $inputFile = $('input[id="trick_featuredPicture"]');
            $inputFile.val('');
            //$InputFile.attr('placeholder', 'Sélectionner un fichier image');
            $inputFile.parent().find('.custom-file-label').text('Sélectionner un fichier image');

            //deletion of preview image
            var imgPreviewHolder = $inputFile.parent().parent().parent().find('.img-preview-holder');
            imgPreviewHolder.empty();

            $('input[id="trick_featuredPictureDeletionState"]').attr('value', 'true'); //to know to apply the main picture deletion
            ReplaceInitialTitleBannerImage();
    });

    imgPreviewSearch = $('input[id="trick_featuredPicture"]').parent().parent().parent().find('.img-preview');
    // if featuredPicture is preset
    if( imgPreviewSearch.length == 1 ){
        funcPreviewImageInTitleBanner(imgPreviewSearch.attr('src')); //set same picture in banner title
    }

    enhanceForm();
});
