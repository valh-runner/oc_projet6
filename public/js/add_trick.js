

// Setup "add and remove" functionnality for each CollectionType
//--------------------------------------------------------------

jQuery(document).ready(function() {
	addAndRemoveFunctionnality('videos', false);
	addAndRemoveFunctionnality('pictures', true);
});

function addAndRemoveFunctionnality(ulClassname, isFileTypeEntity){
	var $collectionHolder;

	// setup an "add a video" link
	var $addVideoButton = $('<button type="button" class="btn btn-secondary">Ajouter un champ</button>');
	var $newLinkLi = $('<li></li>').append($addVideoButton);


    // Get the ul that holds the collection of videos
    $collectionHolder = $('ul.'+ ulClassname);

    // add a delete link to all of the existing video form li elements
    $collectionHolder.find('li').each(function() {
        addVideoFormDeleteLink($(this));
    });

    // add the "add a tag" anchor and li to the videos ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    var index = $collectionHolder.find(':input').length;
    $collectionHolder.data('index', index);

    $addVideoButton.on('click', function(e) {
        // add a new video form
        addVideoForm($collectionHolder, $newLinkLi, isFileTypeEntity);
    });

    //if no existant field, create default one
    if(index == 1){
    	addVideoForm($collectionHolder, $newLinkLi, isFileTypeEntity);
    }
}

function addVideoForm($collectionHolder, $newLinkLi, isFileTypeEntity) {
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
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    // add a delete link to the new form
    addVideoFormDeleteLink($newFormLi);

    //If as FileType field
    if(isFileTypeEntity == true){


    	//Make fileType field, styled by bootstrap, look usable
    	//$newInputFile = $newFormLi.find('input#trick_pictures_'+ index +'_filename');
    	$newInputFile = $newFormLi.find('.custom-file-input');

        $newInputFile.on('change', function(event) {
            var inputFile = event.currentTarget;
            $(inputFile).parent()
                .find('.custom-file-label')
                .html(inputFile.files[0].name);
        });
    }
}

function addVideoFormDeleteLink($videoFormLi) {
    //var $removeFormButton = $('<button type="button">Delete this tag</button>');
    //$videoFormLi.append($removeFormButton);

    $removeFormButton = $videoFormLi.find('button');

    $removeFormButton.on('click', function(e) {
        // remove the li for the video form
        $videoFormLi.remove();
    });
}

//Form enhancer
//-------------

$(document).ready(function(){
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

	//Make fileInput field, styled by bootstrap, look usable

    $('.custom-file-input').on('change', function(event) {
        var inputFile = event.currentTarget;
        $(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);
    });
});