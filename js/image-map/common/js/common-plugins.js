/*!
 * Escapify JQuery Plugin Library v0.1.0
 * http://www.therubinway.com/escapify
 *
 * Copyright 2010, Alan Rubin
 * Licensed under the MIT license.
 */
(function($){$.escapifyHTML=function(text){if(text){return $("<div/>").text(text).html();}else{return text;}};$.unescapifyHTML=function(text){if(text){return $("<div/>").html(text).text();}else{return text;}};})(jQuery);

/*!
 * jQuery plugin
 * serialize checkbox asbools
 * originally Written by Thomas Danemar at http://tdanemar.wordpress.com/2010/08/24/jquery-serialize-method-and-checkboxes/
 * modified to work as ceckbox as bits
 * this plugin serializes form data and stores checkbox values as int 1 or 0 
*/
(function($) {
    $.fn.serialize = function(options) {
		return $.param(this.serializeArray(options));
    };
	
    $.fn.serializeArray = function(options) {
        var o = $.extend({
            checkboxesAsBits: false
        }, options || {});
        var rselectTextarea = /select|textarea/i;
        var rinput = /text|hidden|password|search/i;
        var res = this.map(function() {
            return this.elements ? $.makeArray(this.elements) : this;
        }).filter(function() {
            return this.name && !this.disabled && (this.checked || (o.checkboxesAsBits && this.type === "checkbox") || rselectTextarea.test(this.nodeName) || rinput.test(this.type));
        }).map(function(i, elem) {
            var val = $(this).val();
            return val == null ? null : $.isArray(val) ? $.map(val, function(val, i) {
                return {
                    name: elem.name,
                    value: val
                };
            }) : {
                name: elem.name,
                value: (o.checkboxesAsBits && this.type === "checkbox") ? (this.checked ? 1 : 0) : val
            };
        }).get();
		
		//stores subfiled fields into basic one using join with glue "||||"
		var storage = {};
		
		var i = res.length
		while (i--) { //backward to splice
			var value = res[i];
			//is multikey
			if (/-multi-/i.test(value.name)){
				var paramNum = parseInt(value.name.match(/^param([0-9])/i)[1]),
					fieldName = 'param' + paramNum + 'Value';
				
				if (storage[fieldName] === undefined)
					storage[fieldName] = [];
				
				storage[fieldName].unshift(value.value); //iterating from back so unshift
				
				res.splice(i, 1); //remove from array
			}
		}

		//store into fields
		$.each( storage, function( key, value ) {
			res.push({name: key, value: value.join('||||') });
		});
		
		return res;
    };
})(jQuery);

/*! jQuery plugin that serializes form into object */
(function($) {
    $.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray({
            checkboxesAsBits: true
        });
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value);
            } else {
                o[this.name] = this.value;
            }
        });
		
        return o;
    };
})(jQuery);