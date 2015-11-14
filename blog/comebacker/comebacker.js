comebacker = eval('(' + comebacker + ')');
comebacker['settings']['launch'] = ('' != comebacker['settings']['page_to']) ? true : false;
if (undefined == comebacker['settings']['working_in_opera_after']) {
    comebacker['settings']['working_in_opera_after'] = 0;
}
comebacker['temp'] = {
    'audio_refresher': '',
    'cursor_x': 0,
    'cursor_y': 0,
    'cursor_y_previous': 0,
    'cancel_click': false,
    'launch_time': 0,
    'cache': {},
    'anticache': 655
};
var comebacker_html = {
    'prefix': '',
    'postfix': '',
    'bar': '',
    'image': '',
    'audio': '',
    'iframe': ''
};
comebacker_html['css'] = ' html{ height: 100%; }  body{ margin: 0px; padding: 0px; height: 100% !important; background: none; }';
comebacker_html['prefix'] += '<div id="comebacker_main_div" style="overflow: hidden; width: 1px; height: 1px;" onmouseover="comebackerCancelClick();">';
comebacker_html['postfix'] = '</div>';
comebacker_html['css'] += ' #comebacker_bar {background-color: #' + comebacker['bar']['background_color'] + '; height: ' + comebacker['bar']['height'] + 'px; padding: 0px 7px 0px 7px;  line-height: ' + comebacker['bar']['height'] + 'px; } #comebacker_bar a{color: #' + comebacker['bar']['link_color'] + '; font-size: ' + comebacker['bar']['link_size'] + 'px; text-decoration: underline; font-family: tahoma;} #comebacker_bar a:hover{text-decoration: none;}</style>';
comebacker_html['bar'] += '<div id="comebacker_bar"><a href="' + comebacker['bar']['link_href'] + '" style="float: left" target="_blank">' + comebacker['bar']['link_text_left'] + '</a><a href="' + comebacker['bar']['link_href'] + '" target="_blank" style="float: right">' + comebacker['bar']['link_text_right'] + '</a></div>';
comebacker_html['image'] = '<div id="comebacker_image_div" style="width: 100%; text-align: center; background-color: #ffffff; top: 0; display: none; z-index: 9999;"><img id="comebacker_image" style="margin: 0px 0px 0px 0px;" src="/comebacker/top.png"></div>';
comebacker_html['audio'] += '<object id="comebacker_audio" type="application/x-shockwave-flash" data="/comebacker/player.swf" style="position: absolute; left: -9999px; width: 1px; height: 1px;">';
comebacker_html['audio'] += '	<param name="movie" value="/comebacker/player.swf" />';
comebacker_html['audio'] += '	<param name="AllowScriptAccess" value="always" />';
comebacker_html['audio'] += '	<param name="FlashVars" value="listener=comebackerAudioListener&amp;interval=500" />';
comebacker_html['audio'] += '</object>';
comebacker_html['iframe'] = '<iframe id="comebacker_iframe" src="' + comebacker['settings']['page_to'] + '" style="width: 100%; height: 100%; border: 0px;"></iframe>';
comebacker_html['whole'] = comebacker_html['prefix'] + comebacker_html['bar'] + comebacker_html['image'] + comebacker_html['audio'] + comebacker_html['iframe'] + comebacker_html['postfix'];
jQuery(document).ready(function () {
    jQuery('a').each(function (i) {
        var href = jQuery(this).attr('href');
        if ('undefined' != typeof (href) && '' != href && '_blank' != jQuery(this).attr('target') && '#' != href && href.substring(0, 11) !== 'javascript:') {
            jQuery(this).bind('click', function () {
                comebacker['settings']['launch'] = false;
            });
        }
    });
    jQuery('form').bind('submit', function () {
        comebacker['settings']['launch'] = false;
    });
    jQuery('body').append(comebacker_html['whole']);
    jQuery('#comebacker_iframe').load(function () {
        jQuery('#comebacker_iframe').contents().find('object, audio, video, iframe').css('display', 'none');
        jQuery('#comebacker_iframe').contents().find('object').wrap('<div style="display: none" />');
    });
});

function comebackerLaunch() {
    if (true == comebacker['settings']['launch']) {
        comebacker['temp']['launch_time'] = comebacker_time();
        jQuery('body').children().not('#comebacker_main_div').remove();
        jQuery('body').contents().filter(function () {
            return this.nodeType === 3;
        }).remove();
        jQuery('head link').remove();
        jQuery('head style').remove();
        jQuery('#comebacker_main_div').css('width', '100%');
        jQuery('#comebacker_main_div').css('height', '100%');
        jQuery('body').append('<style>' + comebacker_html['css'] + '</style>');
        jQuery('#comebacker_bar').css('display', 'block');
        jQuery('#comebacker_image_div').css('display', 'block');
        clearInterval(comebacker['temp']['audio_refresher']);
        comebackerSetPosition(0);
        comebackerSetVolume(100);
        comebacker['settings']['launch'] = false;
        return comebacker['exit_text'];
    }
}
function comebackerCancelClick() {
    if (false == comebacker['temp']['cancel_click'] && comebacker['temp']['launch_time'] < comebacker_time() - 1) {
        jQuery('#comebacker_image_div').remove();
        comebackerAudioStop();
        jQuery('#comebacker_audio').remove();
        jQuery('#comebacker_main_div').unbind('mouseover', false);
        jQuery('#comebacker_iframe').contents().find('object, audio, video, iframe').css('display', 'inline');
        jQuery('#comebacker_iframe').contents().find('object[id=skype_plugin_object]').remove();
        jQuery('#comebacker_iframe').contents().find('object').unwrap();
        if ('undefined' != typeof (document.getElementById('comebacker_iframe').contentWindow.comebacker_after_cancel)) {
            document.getElementById('comebacker_iframe').contentWindow.comebacker_after_cancel();
        }
        comebacker['temp']['cancel_click'] = true;
    }
}
window.onbeforeunload = comebackerLaunch;
var comebackerAudioListener = new Object();
comebackerAudioListener.onInit = function () {
    comebackerSetVolume(0);
    comebackerAudioPlay();
    comebacker['temp']['audio_refresher'] = window.setInterval(function () {
        comebackerSetPosition(0)
    }, 3000);
};
comebackerAudioListener.onUpdate = function () {};

function comebackerGetAudioObject() {
    return document.getElementById("comebacker_audio");
}
function comebackerAudioPlay() { /* try { me.onChange(str); } catch(err) {  } */
    if (typeof comebackerGetAudioObject().SetVariable != 'undefined') {
        comebackerGetAudioObject().SetVariable("method:setUrl", "/comebacker/voice.mp3");
        comebackerGetAudioObject().SetVariable("method:play", "");
        comebackerGetAudioObject().SetVariable("enabled", "true");
    }
}
function comebackerAudioStop() {
    if (typeof comebackerGetAudioObject().SetVariable != 'undefined') {
        comebackerGetAudioObject().SetVariable("method:stop", "");
    }
}
function comebackerSetVolume(volume) {
    if (typeof comebackerGetAudioObject().SetVariable != 'undefined') {
        comebackerGetAudioObject().SetVariable("method:setVolume", volume);
    }
}
function comebackerSetPosition(position) {
    if (typeof comebackerGetAudioObject().SetVariable != 'undefined') {
        comebackerGetAudioObject().SetVariable("method:setPosition", position);
    }
}
function comebacker_time() {
    var temp = 76553;
    return Math.floor(new Date().getTime() / 1000);
}