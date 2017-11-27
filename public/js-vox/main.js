var VoxTest = {}
ajax_is_running = false;
var handleScrollbars;
var scrollToActive;
var FB_status;
var fbLogin;
var fb_city_id;

$(document).ready(function(){
	$.cookie.json = true;

	VoxTest.handleNextQuestion = function() {
		$('#current-question-bar').css('width', ((vox.current / vox.count)*100)+'%');
		$('#current-question-num').html( vox.current );
		var reward = (vox.current / vox.count) * vox.reward;
		$('#current-question-reward').html( Math.round(reward) );
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
		console.log('scroll', active_top);
		$('.questions-inner').stop().animate({scrollTop:active_top}, 300, 'swing');
	}

	handleScrollbars = function() {
		var parent = document.getElementById('questions-wrapper');
		var child = document.getElementById('questions-inner');
		if(parent && child) {
			child.style.width = parent.offsetWidth + (child.offsetWidth - child.clientWidth) + "px";
		}
	}
	handleScrollbars();
	$(window).resize( handleScrollbars );

	$('.triangle-down').click( function() {
		var active = $('.another-question.active');
		if(active.next().length) {
			active.next().addClass('active');
			active.removeClass('active');
			scrollToActive();
		}
	} );
	$('.triangle-up').click( function() {
		var active = $('.another-question.active');
		if(active.prev().length) {
			active.prev().addClass('active');
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