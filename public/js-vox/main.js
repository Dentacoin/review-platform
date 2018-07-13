var VoxTest = {}
ajax_is_running = false;
var handleScrollbars;
var scrollToActive;
var FB_status;
var fbLogin;
var fb_city_id;
var checkFilledDots;

$(document).ready(function(){

	$.cookie.json = true;

	checkFilledDots = function( event, index) {
		console.log('alee');
		var goods = new Array;
		var flickity = $('.flickity:visible');
		var missing = false;
		if( flickity.length ) {
			flickity.find('.answer-radios-group').each( function() {
	            if( $(this).find('.answer-radio.active-label').length ) {
	                goods.push(true);
	            } else {
	                goods.push(false);
	                missing = true;
	            }
	        } );
	        console.log(goods);
	        var i=0;
	        flickity.find('.flickity-page-dots .dot').each( function() {
	            if(goods[i]) {
	                $(this).addClass('filled');
	            } else {
	                $(this).removeClass('filled');
	            }
	            i++;
	        } );

	        if(!missing) {
				$('.question-group:visible .next-answer').show();
	        }
		}
	}

	VoxTest.handleNextQuestion = function() {
		$('#current-question-bar').css('width', ((vox.current / vox.count)*100)+'%');
		var mins = Math.ceil( (vox.count - vox.current)/6 );
		$('#current-question-num').html( mins<2 ? '<1' : '~'+mins );
		if(vox.current>1) {
			var reward = ( (vox.current-1) / vox.count) * vox.reward;
			$('#current-question-reward').html( Math.round(reward) );
			$('#dcn-test-reward-before').hide();
			$('#dcn-test-reward-after').show();
		}

		if (window.innerWidth < 768) {

			$('.question-group:visible .flickity').flickity({
				wrapAround: true,
				adaptiveHeight: true,
				draggable: false
			});

			$('.question-group:visible .flickity').on( 'select.flickity', checkFilledDots);
			$('.question-group:visible .next-answer').hide();
		}

		if($('.question-group:visible').hasClass('shuffle')) {
			var parent = $('.question-group:visible .answers');
		    var divs = parent.children();
		    while (divs.length) {
		        parent.append(divs.splice(Math.floor(Math.random() * divs.length), 1)[0]);
		    }
		}
		
	}
	if(typeof(vox)!='undefined') {
		VoxTest.handleNextQuestion();		
	}


    $('.country-select').change( function() {
    	var city_select = $(this).closest('form').find('.city-select').first();
    	city_select.attr('disabled', 'disabled');
		$.ajax( {
			url: '/cities/' + $(this).val(),
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log(data);
    			city_select.attr('disabled', false)
			    .find('option')
			    .remove();
			    for(var i in data.cities) {
			    	console.log( fb_city_id, data.cities[i] );
    				city_select.append('<option value="'+i+'" '+(fb_city_id && fb_city_id==data.cities[i] ? 'selected="selected"' : '' )+'>'+data.cities[i]+'</option>');
			    }
				//city_select
				//$('#modal-message .modal-body').html(data);
			}
		});
    } );

	$('.alert-update button').click( function() {
		console.log('aleee');
		Cookies.set('show-update', 'ok', { expires: 365 });
	} );


    $('#read-privacy').change( function(e) {
    	if ($(this).is(':checked')) {
    		$(this).parent().next().css('display', 'block');
    	} else {
    		$(this).parent().next().hide();
    	}
    });


    $('.agree-gdpr').click( function() {
    	$.ajax( {
			url: '/'+lang +'/accept-gdpr',
			type: 'GET',
			success: function( data ) {
				$('#gdprPopupVox').removeClass('active');
			}
		});
    });


	$('#login-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;
        $('#login-error').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.href = data.url;
                } else {
                	if(data.banned) {
                		window.location.href = data.banned;
                	} else {
                		$('#login-error').show();
                	}
                }
                ajax_is_running = false;
            }, "json"
        );

	} );

	if(window.location.hash=='#register') {
		$("#registerPopup").modal();
	}

	if(window.location.hash=='#login') {
		$("#loginPopup").modal();
	}

	$('#register-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;
        $('#register-error').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.href = data.url;
                } else {
                	$('#register-error').show();
                	$('#register-error span').html('');
                	for(var i in data.messages) {
                		$('#register-error span').append(data.messages[i]+'<br/>');
                	}
                }
                ajax_is_running = false;
            }, "json"
        );

	} );


	scrollToActive = function() {
		$('.questions-inner .another-question.active').offset();
		var scroll = 0;
		var tmp = $('.questions-inner .another-question.active');
		while(tmp.length) {
			scroll += tmp.outerHeight() + parseFloat(tmp.css('margin-bottom'));
			tmp = tmp.prev();
		}
		//var active_top = $('.questions-inner .another-question.active').position().top - 250 + $('.questions-inner .another-question.active').outerHeight()/2;
		var active_top = scroll - 250 - $('.questions-inner .another-question.active').outerHeight()/2;
		$('.questions-inner').stop().animate({scrollTop:active_top}, 300, 'swing');
	}

	handleScrollbars = function() {
		var parent = document.getElementById('questions-wrapper');
		var child = document.getElementById('questions-inner');
		if(parent && child) {
			child.style.width = parent.offsetWidth + (child.offsetWidth - child.clientWidth) + "px";
		}

		if($(window).width()>992 && $('.stats-col').height() < $(window).height()) {
			var elements = $('.should-be-sticky').addClass('sticky');
			Stickyfill.add(elements);			
		} else {
			var elements = $('.should-be-sticky').removeClass('sticky');
			Stickyfill.remove(elements);
		}
	}
	handleScrollbars();
	$(window).resize( handleScrollbars );

	$('.triangle-down').click( function() {
		var active = $('.another-question.active');
		if(active.nextAll('.another-question').length) {
			active.nextAll('.another-question').first().addClass('active');
			active.removeClass('active');
			scrollToActive();
		}
	} );
	$('.triangle-up').click( function() {
		var active = $('.another-question.active');
		if(active.prevAll('.another-question').length) {
			active.prevAll('.another-question').first().addClass('active');
			active.removeClass('active');
			scrollToActive();
		}
	} );
	$('.another-question').click( function() {
		if( !$(this).hasClass('active') ) {
			$('.another-question.active').removeClass('active');
			$(this).addClass('active');
			scrollToActive();
		}
	} );

	$('.questions-inner').on('wheel', function(event){
		event.preventDefault();
		if(event.originalEvent.deltaY < 0){
			$('.triangle-up').click(); // wheeled up
		} else {
			$('.triangle-down').click();
		}
	});;


	$('#language-selector').change( function() {
		var arr = window.location.pathname.split('/');
		arr[1] = $(this).val();
		window.location.href = window.location.origin + arr.join('/');
	});


	$('#phone-verify-send-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
        
        $('#phone-verify-send-form').find('.alert').hide();

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                    $('#phone-verify-send-form').hide();
                    $('#phone-verify-code-form').show();
                } else {
                    if(data.reason && data.reason=='phone_taken') {
                        $('#phone-taken').show();
                    } else {
                        $('#phone-invalid').show();
                    }
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

	$('#phone-verify-code-form').submit( function(e) {
		e.preventDefault();

		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	window.location.reload();
                } else {
                    $('#phone-verify-code-form').find('.alert').show();
                }
                ajax_is_running = false;
            }, "json"
        );

        return false;

    } );

    setInterval( function() {
		$.ajax( {
			url: '/question-count',
			type: 'GET',
			dataType: 'json',
			success: function( data ) {
				console.log(data);
				var my_amount = parseInt($('#header-balance').html()) * data.dcn_price_full

				$('#header_questions').html(data.question_count);
				$('#header-rate').html(data.dcn_price);
				$('#header-change').html('('+data.dcn_change+'%)').css('color', parseFloat(data.dcn_change)>0 ? '#4caf50' : '#e91e63' );
				$('#header-usd').html( '$' + parseFloat(my_amount).toFixed(2) );
			}
		});

    }, 10000 );


    //Selects
    $(".select-me").on("click focus", function () {
        $(this).select();
    });

    $('.native-share').click( function() {
        navigator.share({
            title: $(this).attr('data-title'),
            url: $(this).attr('data-url')
        }).then(() => console.log('Successful share'))
        .catch((error) => console.log('Error sharing:', error));

    });

    function share_buttons() {
		$('.share.google, .share.fb, .share.twt, .share.reddit').click( function() {
			var post_url = $(this).attr('data-url');
			var post_title = $(this).attr('data-title');
			if ($(this).hasClass('fb')) {
				var url = 'https://www.facebook.com/dialog/share?app_id=1906201509652855&display=popup&href=' + escape(post_url);
			} else if ($(this).hasClass('twt')) {
				var url = 'https://twitter.com/share?url=' + escape(post_url) + '&text=' + post_title;
			} else if ($(this).hasClass('google')) {
				var url = 'https://plus.google.com/share?url=' + escape(post_url);
			}
			window.open( url , 'ShareWindow', 'height=450, width=550, top=' + (jQuery(window).height() / 2 - 275) + ', left=' + (jQuery(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
		});
	}
	share_buttons();

	$('#provide-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}

		ajax_is_running = true;
        $('#provide-form .alert').hide();
        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#provide-succcess').show().css({
                		'margin-top': 0,
                		'margin-bottom': 22
                	});
                	$('#provide-succcess').html(data.message);
                	$('#provide-form').hide();
                	$('#invite-wrapper').show();
                } else {
                	$('#provide-invalid').show();
                	$('#provide-invalid').html(data.message);
                }
                ajax_is_running = false;
            }, "json"
        );

	});

	$('#set-email-form').submit( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
		$('#set-email-error').hide();

        $.post( 
            $(this).attr('action'), 
            $(this).serialize() , 
            function( data ) {
                if(data.success) {
                	$('#set-email-form').hide();
                	$('#email-refresh').show();
                	$('#verify-email-span').html( $('#verify-email').val() );
                } else {
                	$('#set-email-error').show().html(data.message);
                }
                ajax_is_running = false;
            }, "json"
        );

	} );

	$('#stop-submit').click( function(e) {
		e.preventDefault();
		if(ajax_is_running) {
			return;
		}
		ajax_is_running = true;
        $('#stop-error').hide();
        
        $.post( 
            '/wait', 
            {
            	email: $('#stop-email').val(),
            	name: $('#stop-name').val(),
            }, 
            function( data ) {
            	console.log(data);
                if(data.success) {
                	$('#stop-submit').after( $('<div style="margin-top: 20px; margin-bottom: 0px;" class="alert alert-success">Thank you. We\'ll let you know as soon as registrations are open again.</div>') );
                	$('#stop-submit').remove();
                } else {
                	if( !$('#stop-error').length ) {
                		$('#stop-submit').after( $('<div style="margin-top: 20px; margin-bottom: 0px;" class="alert alert-warning" id="stop-error"></div>') );
                	}
                	$('#stop-error').html('').show();
                	for(var i in data.messages) {
                		$('#stop-error').append(data.messages[i] + '<br/>');
                	}
                }
                ajax_is_running = false;
            }, "json"
        );

	} );

	$('.new-popup .closer').click( function() {
		$('.new-popup').removeClass('active');
	} );

	$('.close').click( function() {
        $(this).closest('.modal').hide();
    });

	if( $('.popup-welcome').length  ) {
		$('.popup-welcome').addClass('active');
	}

	if( $('.popup-tutorial').length  ) {
		$('.popup-tutorial').addClass('active');
	    $('.step-btn').click( function() {
	        if( $(this).closest('.step').next().hasClass('step') ) {
	            $(this).closest('.step').next().show();
	            $(this).closest('.step').hide();
	        } else {
	            $('.popup-tutorial').removeClass('active');
	        }
	    } );		
	}

});












/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2006, 2014 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD (Register as an anonymous module)
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// Node/CommonJS
		module.exports = factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (arguments.length > 1 && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {},
			// To prevent the for loop in the first place assign an empty array
			// in case there are no cookies at all. Also prevents odd result when
			// calling $.cookie().
			cookies = document.cookie ? document.cookie.split('; ') : [],
			i = 0,
			l = cookies.length;

		for (; i < l; i++) {
			var parts = cookies[i].split('='),
				name = decode(parts.shift()),
				cookie = parts.join('=');

			if (key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));



/*!
  * Stickyfill – `position: sticky` polyfill
  * v. 2.0.3 | https://github.com/wilddeer/stickyfill
  * MIT License
  */
!function(a,b){"use strict";function c(a,b){if(!(a instanceof b))throw new TypeError("Cannot call a class as a function")}function d(a,b){for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c])}function e(a){return parseFloat(a)||0}function f(a){for(var b=0;a;)b+=a.offsetTop,a=a.offsetParent;return b}function g(){function c(){a.pageXOffset!=k.left?(k.top=a.pageYOffset,k.left=a.pageXOffset,n.refreshAll()):a.pageYOffset!=k.top&&(k.top=a.pageYOffset,k.left=a.pageXOffset,l.forEach(function(a){return a._recalcPosition()}))}function d(){f=setInterval(function(){l.forEach(function(a){return a._fastCheck()})},500)}function e(){clearInterval(f)}c(),a.addEventListener("scroll",c),a.addEventListener("resize",n.refreshAll),a.addEventListener("orientationchange",n.refreshAll);var f=void 0,g=void 0,h=void 0;"hidden"in b?(g="hidden",h="visibilitychange"):"webkitHidden"in b&&(g="webkitHidden",h="webkitvisibilitychange"),h?(b[g]||d(),b.addEventListener(h,function(){b[g]?e():d()})):d()}var h=function(){function a(a,b){for(var c=0;c<b.length;c++){var d=b[c];d.enumerable=d.enumerable||!1,d.configurable=!0,"value"in d&&(d.writable=!0),Object.defineProperty(a,d.key,d)}}return function(b,c,d){return c&&a(b.prototype,c),d&&a(b,d),b}}(),i=!1;a.getComputedStyle?!function(){var a=b.createElement("div");["","-webkit-","-moz-","-ms-"].some(function(b){try{a.style.position=b+"sticky"}catch(a){}return""!=a.style.position})&&(i=!0)}():i=!0;var j="undefined"!=typeof ShadowRoot,k={top:null,left:null},l=[],m=function(){function g(a){if(c(this,g),!(a instanceof HTMLElement))throw new Error("First argument must be HTMLElement");if(l.some(function(b){return b._node===a}))throw new Error("Stickyfill is already applied to this node");this._node=a,this._stickyMode=null,this._active=!1,l.push(this),this.refresh()}return h(g,[{key:"refresh",value:function(){if(!i&&!this._removed){this._active&&this._deactivate();var c=this._node,g=getComputedStyle(c),h={top:g.top,display:g.display,marginTop:g.marginTop,marginBottom:g.marginBottom,marginLeft:g.marginLeft,marginRight:g.marginRight,cssFloat:g.cssFloat};if(!isNaN(parseFloat(h.top))&&"table-cell"!=h.display&&"none"!=h.display){this._active=!0;var k=c.parentNode,l=j&&k instanceof ShadowRoot?k.host:k,m=c.getBoundingClientRect(),n=l.getBoundingClientRect(),o=getComputedStyle(l);this._parent={node:l,styles:{position:l.style.position},offsetHeight:l.offsetHeight},this._offsetToWindow={left:m.left,right:b.documentElement.clientWidth-m.right},this._offsetToParent={top:m.top-n.top-e(o.borderTopWidth),left:m.left-n.left-e(o.borderLeftWidth),right:-m.right+n.right-e(o.borderRightWidth)},this._styles={position:c.style.position,top:c.style.top,bottom:c.style.bottom,left:c.style.left,right:c.style.right,width:c.style.width,marginTop:c.style.marginTop,marginLeft:c.style.marginLeft,marginRight:c.style.marginRight};var p=e(h.top);this._limits={start:m.top+a.pageYOffset-p,end:n.top+a.pageYOffset+l.offsetHeight-e(o.borderBottomWidth)-c.offsetHeight-p-e(h.marginBottom)};var q=o.position;"absolute"!=q&&"relative"!=q&&(l.style.position="relative"),this._recalcPosition();var r=this._clone={};r.node=b.createElement("div"),d(r.node.style,{width:m.right-m.left+"px",height:m.bottom-m.top+"px",marginTop:h.marginTop,marginBottom:h.marginBottom,marginLeft:h.marginLeft,marginRight:h.marginRight,cssFloat:h.cssFloat,padding:0,border:0,borderSpacing:0,fontSize:"1em",position:"static"}),k.insertBefore(r.node,c),r.docOffsetTop=f(r.node)}}}},{key:"_recalcPosition",value:function(){if(this._active&&!this._removed){var a=k.top<=this._limits.start?"start":k.top>=this._limits.end?"end":"middle";if(this._stickyMode!=a){switch(a){case"start":d(this._node.style,{position:"absolute",left:this._offsetToParent.left+"px",right:this._offsetToParent.right+"px",top:this._offsetToParent.top+"px",bottom:"auto",width:"auto",marginLeft:0,marginRight:0,marginTop:0});break;case"middle":d(this._node.style,{position:"fixed",left:this._offsetToWindow.left+"px",right:this._offsetToWindow.right+"px",top:this._styles.top,bottom:"auto",width:"auto",marginLeft:0,marginRight:0,marginTop:0});break;case"end":d(this._node.style,{position:"absolute",left:this._offsetToParent.left+"px",right:this._offsetToParent.right+"px",top:"auto",bottom:0,width:"auto",marginLeft:0,marginRight:0})}this._stickyMode=a}}}},{key:"_fastCheck",value:function(){this._active&&!this._removed&&(Math.abs(f(this._clone.node)-this._clone.docOffsetTop)>1||Math.abs(this._parent.node.offsetHeight-this._parent.offsetHeight)>1)&&this.refresh()}},{key:"_deactivate",value:function(){var a=this;this._active&&!this._removed&&(this._clone.node.parentNode.removeChild(this._clone.node),delete this._clone,d(this._node.style,this._styles),delete this._styles,l.some(function(b){return b!==a&&b._parent&&b._parent.node===a._parent.node})||d(this._parent.node.style,this._parent.styles),delete this._parent,this._stickyMode=null,this._active=!1,delete this._offsetToWindow,delete this._offsetToParent,delete this._limits)}},{key:"remove",value:function(){var a=this;this._deactivate(),l.some(function(b,c){if(b._node===a._node)return l.splice(c,1),!0}),this._removed=!0}}]),g}(),n={stickies:l,Sticky:m,addOne:function(a){if(!(a instanceof HTMLElement)){if(!a.length||!a[0])return;a=a[0]}for(var b=0;b<l.length;b++)if(l[b]._node===a)return l[b];return new m(a)},add:function(a){if(a instanceof HTMLElement&&(a=[a]),a.length){for(var b=[],c=function(c){var d=a[c];return d instanceof HTMLElement?l.some(function(a){if(a._node===d)return b.push(a),!0})?"continue":void b.push(new m(d)):(b.push(void 0),"continue")},d=0;d<a.length;d++){c(d)}return b}},refreshAll:function(){l.forEach(function(a){return a.refresh()})},removeOne:function(a){if(!(a instanceof HTMLElement)){if(!a.length||!a[0])return;a=a[0]}l.some(function(b){if(b._node===a)return b.remove(),!0})},remove:function(a){if(a instanceof HTMLElement&&(a=[a]),a.length)for(var b=function(b){var c=a[b];l.some(function(a){if(a._node===c)return a.remove(),!0})},c=0;c<a.length;c++)b(c)},removeAll:function(){for(;l.length;)l[0].remove()}};i||g(),"undefined"!=typeof module&&module.exports?module.exports=n:a.Stickyfill=n}(window,document);