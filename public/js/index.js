
var offset = 4; // initial offset of the number of preloaded tricks
var numberOfTricks = 4;
var isArrow2displayed = false;

function loadMoreTricks(numberOfTricks, offset) {
  $.get( "/charger_plus/"+ numberOfTricks +"/"+ offset, function(data) {
    $( "div#content-index-tricks" ).append(data);
    var nbrTricks = $("div#content-index-tricks").children().length;
    /* the arrow appearance if more than 15 tricks loaded is too much,
       after 8 is better to preview with 10 initial tricks */
    if(nbrTricks > 8 && isArrow2displayed === false){
      $("#arrow2").show();
      isArrow2displayed = true;
    }
  });
}

jQuery(document).ready(function() {

  $('#action-load-more').on('click', function(e) {
      loadMoreTricks(numberOfTricks, offset);
      offset += numberOfTricks;
  });


  // Add scrollspy to <body>
  $('body').scrollspy({target: ".navbar", offset: 50});

  // Add smooth scrolling on links
  $("#navbarSupportedContent #slideToTricksLink, #arrow a, #arrow2 a").on('click', function(event) {

    // Make sure this.hash has a value before overriding default behavior
    if (this.hash !== "") {
      event.preventDefault(); // Prevent default anchor click behavior
      var hash = this.hash; // Store hash

      // smooth page autoscroll taking specified milliseconds to scroll to the specified area
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){
        window.location.hash = hash; // Add hash (#) to URL when done scrolling (default click behavior)
      });
    }
  });


  // Slogan fade manage
  $(window).scroll(function () {
      var sloganElem = $('#slogan');
      var sloganScrollTop = sloganElem.position().top; /* offset from top of the parent being on top of the page */
      var mainElem = $('#main');
      var mainScrollTop = mainElem.offset().top; /* offset from top of the page */

      var initialDiff = mainScrollTop - (sloganScrollTop + sloganElem.outerHeight() );
      var diff = initialDiff - $(this).scrollTop();

      if ( diff > 0 ) {
        var diffValueOn1 = ( diff * 1 ) / initialDiff;
        sloganElem.css('opacity', diffValueOn1);
      } else {
        sloganElem.css('opacity', 0);
      }
  });


});