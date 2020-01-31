
var offset = 8;
var isArrow2displayed = false;

function loadMoreTricks(offset) {
    $.get( "/charger_plus/"+ offset, function(data) {
      $( "div#content" ).append(data);
      var nbrTricks = $("div#content").children().length;
      if(nbrTricks > 15 && isArrow2displayed == false){
        $("#arrow2").show();
        isArrow2displayed = true;
      }
    });
}

jQuery(document).ready(function() {
    $('#action-load-more').on('click', function(e) {
        loadMoreTricks(offset);
        offset += 8;
    });
});