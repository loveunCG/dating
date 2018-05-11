  //Select Menu  Color Script
  $(document).ready(function() {
      $(".red, .pink, .purple, .deep-purple, .indigo, .blue, .light-blue, .cyan, .teal, .green, .light-green, .lime, .yellow, .amber, .orange, .deep-orange, .brown, .white, .blue-grey, .black").on("click", function() {
          $(".wsmenu")
              .removeClass()
              .addClass('wsmenu pm_' + $(this).attr('class') );       
      });
  	$(".toggle").on("click", function() {
           $(".pop-up").toggleClass('inactive');     
      });
      $('#wsnavtoggle').click(function(){
                     $('body').css('overflow','hidden'); 
                  });
                  
                  $('#teacher_drop').click(function(){
  						    	if($(this).hasClass('wsmenu-rotate')){
  										$(this).removeClass('wsmenu-rotate');						    	
  						    	}else{
  						    	$(this).addClass('wsmenu-rotate');
  						    	}            
                  });
                 
  });
  //Select Menu  Color Script

  ( function( window ) {

  'use strict';

  // class helper functions from bonzo https://github.com/ded/bonzo

  function classReg( className ) {
    return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
  }

  // classList support for class management
  // altho to be fair, the api sucks because it won't accept multiple classes at once
  var hasClass, addClass, removeClass;

  if ( 'classList' in document.documentElement ) {
    hasClass = function( elem, c ) {
      return elem.classList.contains( c );
    };
    addClass = function( elem, c ) {
      elem.classList.add( c );
    };
    removeClass = function( elem, c ) {
      elem.classList.remove( c );
    };
  }
  else {
    hasClass = function( elem, c ) {
      return classReg( c ).test( elem.className );
    };
    addClass = function( elem, c ) {
      if ( !hasClass( elem, c ) ) {
        elem.className = elem.className + ' ' + c;
      }
    };
    removeClass = function( elem, c ) {
      elem.className = elem.className.replace( classReg( c ), ' ' );
    };
  }

  function toggleClass( elem, c ) {
    var fn = hasClass( elem, c ) ? removeClass : addClass;
    fn( elem, c );
  }

  var classie = {
    // full names
    hasClass: hasClass,
    addClass: addClass,
    removeClass: removeClass,
    toggleClass: toggleClass,
    // short names
    has: hasClass,
    add: addClass,
    remove: removeClass,
    toggle: toggleClass
  };

  // transport
  if ( typeof define === 'function' && define.amd ) {
    // AMD
    define( classie );
  } else {
    // browser global
    window.classie = classie;
  }

  })( window );
      function init() {
          window.addEventListener('scroll', function(e){
              var distanceY = window.pageYOffset || document.documentElement.scrollTop,
                  shrinkOn = 0,
                  header = document.querySelector("header");
              if (distanceY > shrinkOn) {
                  classie.add(header,"smaller");
              } else {
                  if (classie.has(header,"smaller")) {
                      classie.remove(header,"smaller");
                  }
              }
          });
      }
      window.onload = init();
  	function hideMenu()
  	{
  		$(".wsmenucontainer.clearfix").removeClass("wsoffcanvasopener");
  		$('body').css('overflow','auto');
