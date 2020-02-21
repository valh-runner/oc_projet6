
function changeCommentsPage(trickId, askedCommentPageNumber) {

    var offset = (askedCommentPageNumber - 1) * 10; //

    $.get( "/charger_page_commentaires/"+ trickId +"/"+ offset, function(data) {
      $( "div#comments-content" ).html(data);

      $( "a.btn-primary").removeClass('btn-primary').addClass('btn-secondary');
      $( "a#action-paging-"+ askedCommentPageNumber).removeClass('btn-secondary').addClass('btn-primary');
    });
}

jQuery(document).ready(function() {

    var trickId = $("div#comments-paging-actions").attr('data-trick-id'); // trick id getting

    $.get( "/charger_nombre_commentaires/" + trickId, function(data) {
        var totalCommentsCount = data.CommentsCount;
        totalPageCount = Math.ceil(totalCommentsCount / 10); // number of 10 comments pages

        // buttons adding after the first pre-existent one
        for (var i = 2; i <= totalPageCount; i++) {
            buttonStr = '<a id="action-paging-'+ i +'" class="btn btn-secondary action-paging" onclick="changeCommentsPage('+ trickId +','+ i +')">'+ i +'</a>';
            $("div#comments-paging-actions").append(buttonStr);
        }
    });
});
