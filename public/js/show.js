
function changeCommentsPage(trickId, askedCommentPageNumber) {

    var offset = (askedCommentPageNumber - 1) * 10;

    $.get( "/charger_page_commentaires/"+ trickId +"/"+ offset, function(data) {
      $( "div#comments-content" ).html(data); // load comments page
      // update paging buttons
      var elemUL = $("ul#comments-paging");
      elemUL.find("li.active").removeClass('active');
      elemUL.find("li#item-paging-"+ askedCommentPageNumber).addClass('active');
    });
}

jQuery(document).ready(function() {

    var elemUL = $("ul#comments-paging");
    var trickId = elemUL.attr('data-trick-id'); // trick id getting

    $.get( "/charger_nombre_commentaires/" + trickId, function(data) {
        var totalCommentsCount = data.CommentsCount;
        var totalPageCount = Math.ceil(totalCommentsCount / 10); // number of pages of 10 comments

        // buttons adding after the first pre-existent one
        for (var i = 2; i <= totalPageCount; i++) {
            var itemStr = '<li id="item-paging-'+ i +'" class="page-item"><a class="page-link" onclick="changeCommentsPage('+ trickId +','+ i +')">'+ i +'</a></li>';
            elemUL.append(itemStr);
        }
    });
});
