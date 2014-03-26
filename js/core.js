var emptyFn = function () { },
	getElementsByClassName;
		
if (document.getElementsByClassName) {
    getElementsByClassName = function (classname, parent) {
        return (parent || document).getElementsByClassName(classname);
    }
}
else {
    getElementsByClassName = function (theClass, parent) {
        parent = parent || document;
        var els = (typeof parent.all != "undefined") ? parent.all : parent.getElementsByTagName("*");
        var matchedArray = [],
			pattern = new RegExp("(^| )" + theClass + "( |$)");
        MW.forEach(els, function (el) {
            if (pattern.test(el.className)) {
                matchedArray.push(el);
            }
        });
        return matchedArray;
    }
}

var MW = window.MW = {
    extend: function (b, a) {
        for (var p in a || {}) b[p] = a[p];
        return b;
    },
    forEach: function (items, fn) {
        for (var i = items.length - 1; i >= 0; i--) fn(items[i]);
    },
}

// Ajax
var getXHR = function () { return false };
if (window.XMLHttpRequest) {
    getXHR = function () {
        return new XMLHttpRequest()
    }
}
else if (window.ActiveXObject) {
    getXHR = function () {
        try { return new ActiveXObject("Msxml2.XMLHTTP") } catch (e) {
            try { return new ActiveXObject("Microsoft.XMLHTTP") } catch (e) { }
        }
    }
}

// Check for native JSON parsing support
if (window.JSON && JSON.parse)
    MW.decodeJSON = JSON.parse;
else
// Old browser (IE), fall back to using eval
    MW.decodeJSON = function (text) { return eval('(' + text + ')'); };

var ajax = MW.ajax = function (url, options) {
    if (ajax._xhr) { // the last request is king
        ajax._xhr.onreadystatechange = emptyFn;
        ajax._xhr.abort();
    }
    options = MW.extend({
        type: 'post',
        // Set this to JSON to automatically decode the response
        responseType: 'text',
        // Whether a parameter should be added to prevent caching
        noCache: true,
        onSuccess: emptyFn,
        onFailure: emptyFn,
        onComplete: emptyFn
    }, options);
    var xhr = getXHR();
    var data = null;
    if (!xhr) return;

    if (options.noCache) {
        if (url.indexOf('?') == -1) {

            if (!url.endsWith("/")) {
                url += "/";
            }

            url += '?ts=' + (new Date).getTime();
        }
        else { url += '&ts=' + (new Date).getTime(); }
    }

    if (options.type == 'post') {
        if (options.parameters) {
            var values = [];
            for (var key in options.parameters || {}) {
                values.push(key + '=' + encodeURI(options.parameters[key]));
            }
            data = values.join('&');
        }
    }

    xhr.open(options.type, url, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                if (options.responseType == 'json')
                    var result = MW.decodeJSON(xhr.responseText);
                else
                    var result = xhr.responseText;

                options.onSuccess(xhr, result);
                options.onComplete(xhr, result);
            }
            else {
                options.onFailure(xhr);
            }
        }
    };
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    if (options.type == 'post') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    try {
        xhr.send(data);
        ajax._xhr = xhr;
    } catch (e) { }
};