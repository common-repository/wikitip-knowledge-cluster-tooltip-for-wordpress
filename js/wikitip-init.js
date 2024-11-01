var filter = [];
var thes;
var TOOLTIP_LOADING_TPL = init.TOOLTIP_LOADING_TPL,
    TOOLTIP_BODY_TPL = init.TOOLTIP_BODY_TPL,
    zetind = init.zetind;
var ACTIVE = init.ACTIVE == 1;
if (undefined === getCookie('wikitip_onoff'))
    ACTIVE = true;
else if (getCookie('wikitip_onoff') == 'on')
    ACTIVE = true;
else
    ACTIVE = false;
jQuery.Thesaurus({
    caseSentitive: init.caseSentitive,
    zetind: init.zetind,
    delay: init.delay,
    containers: [init.containers],
    effect: init.effect,
    JSON_DEF_URI: init.JSON_DEF_URI,
    JSON_REMOTE_POST_URI: init.JSON_REMOTE_POST_URI,
    JSON_LOCAL_POST_URI: init.JSON_LOCAL_POST_URI,
    MESSAGE1: init.MESSAGE1,
    MESSAGE2: init.MESSAGE2,
    MESSAGE3: init.MESSAGE3,
    MESSAGE4: init.MESSAGE4,
    MESSAGE5: init.MESSAGE5,
    MESSAGE6: init.MESSAGE6,
    MESSAGE7: init.MESSAGE7,
    MESSAGE8: init.MESSAGE8,
    MESSAGE9: init.MESSAGE9,
    MESSAGE10: init.MESSAGE10,
    MESSAGE11: init.MESSAGE11,
    MESSAGE12: init.MESSAGE12,
    show_count: init.show_count
});

jQuery(document).ready(function () {
    jQuery(".trigger").click(function () {
        jQuery(".panel").toggle("fast");
        jQuery(this).toggleClass("active");
        return false;
    });
});

function setCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    } else var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function deleteCookie(name) {
    setCookie(name, "", -1);
}

function removeElementFromArray(arr) {
    var what, a = arguments, L = a.length, ax;
    while (L > 1 && arr.length) {
        what = a[--L];
        while ((ax = arr.indexOf(what)) != -1) {
            arr.splice(ax, 1);
        }
    }
    return arr;
}