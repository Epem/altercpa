<!-- BEGIN mce -->
{mce}
<script language="javascript" type="text/javascript">
 	function myFileBrowser (field_name, url, type, win) {
	    tinyMCE.activeEditor.windowManager.open({
	        file : '/files',
	        title : 'Выберите файл для вставки',
	        width : 900,
	        height : 500,
	        resizable : "yes",
	        inline : "yes",
	        close_previous : "no"
	    }, { window : win, input : field_name } );
	    return false;
	}
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		language : "ru",
		editor_selector : "simplemce",
		plugins : "advimage,inlinepopups",
  		theme_advanced_buttons1 : "bold,italic,strikethrough,bullist,numlist,outdent,indent,link,unlink,image,|,code",
		theme_advanced_buttons2 : "", theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		file_browser_callback : 'myFileBrowser',
		remove_script_host : false,
		relative_urls : false
	});
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		language : "ru",
		editor_selector : "advancemce",
		plugins : "table,searchreplace,fullscreen,paste,advimage,inlinepopups,media",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,pastetext,pasteword,|,search,replace,|,link,unlink,|,sub,sup,|,image,media,charmap,|,code,fullscreen",
		theme_advanced_buttons2 : "formatselect,fontselect,fontsizeselect,forecolor,backcolor,|,tablecontrols,|,visualaid",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		file_browser_callback : 'myFileBrowser',
		remove_script_host : false,
		relative_urls : false
	});
</script>
<!-- END mce -->
<!-- BEGIN color -->
{colorjs}
<script type="text/javascript">
	$(document).ready(function(){
		$(".colorpick").ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		}).bind('keyup', function(){
			$(this).ColorPickerSetColor(this.value);
		});
	});
</script>
<!-- END color -->
<!-- BEGIN codemirror -->
{codemirror}
<!-- BEGIN lang -->
{codemirror.lang.path}
<!-- END lang -->
<script type="text/javascript">
	$(document).ready(function(){
		$(".codemirror").each(function(id, el){
			var editoe = CodeMirror.fromTextArea( el, {
				mode: $(el).attr('lang'),
				lineNumbers: true,
			});
		});
	});
</script>
<!-- END codemirror -->

<form id="{name}" name="{name}" action="{action}" method="{method}" {encoding}>

<h2 class="caption">{title}</h2>

<table class="form">
<!-- BEGIN field -->
    <!-- BEGIN head -->
    <tr>
        <td class="form-headline" colspan="3">{field.head.value}</td>
    </tr>
    <!-- END head -->
    <!-- BEGIN line -->
    <tr>
        <td class="form-descr" colspan="3">{field.line.value}</td>
    </tr>
    <!-- END line -->
    <!-- BEGIN text -->
    <tr>
        <td class="form-label">{field.text.head}</td>
        <td class="form-field"><input class="form-text" type="text" id="form_{name}_{field.text.name}" name="{field.text.name}" maxlength="{field.text.maxwidth}" value="{field.text.value}" /></td>
        <td class="form-descr">{field.text.descr}</td>
    </tr>
    <!-- END text -->
    <!-- BEGIN pass -->
    <tr>
        <td class="form-label">{field.pass.head}</td>
        <td class="form-field"><input class="form-text" type="password" id="form_{name}_{field.pass.name}" name="{field.pass.name}" maxlength="{field.pass.maxwidth}" value="{field.pass.value}" /></td>
        <td class="form-descr">{field.pass.descr}</td>
    </tr>
    <!-- END pass -->
    <!-- BEGIN textarea -->
    <tr>
        <td class="form-label">{field.textarea.head}</td>
		<td class="form-field"><textarea id="form_{name}_{field.textarea.name}" name="{field.textarea.name}" rows="{field.textarea.rows}" class="form-text">{field.textarea.value}</textarea></td>
        <td class="form-descr">{field.textarea.descr}</td>
    </tr>
    <!-- END textarea -->
    <!-- BEGIN mces -->
    <tr>
        <td class="form-label">{field.mces.head}</td>
        <td class="form-field"><textarea id="form_{name}_{field.mces.name}" name="{field.mces.name}" class="form-text simplemce">{field.mces.value}</textarea></td>
        <td class="form-descr">{field.mces.descr}</td>
    </tr>
    <!-- END mces -->
    <!-- BEGIN mcea -->
    <tr>
        <td class="form-label">{field.mcea.head}</td>
        <td class="form-field"><textarea id="form_{name}_{field.mcea.name}" name="{field.mcea.name}" class="form-text advancemce">{field.mcea.value}</textarea></td>
        <td class="form-descr">{field.mcea.descr}</td>
    </tr>
    <!-- END mcea -->
    <!-- BEGIN code -->
    <tr>
        <td class="form-label">{field.code.head}</td>
        <td class="form-bbcode">
	        <textarea id="form_{name}_{field.code.name}" name="{field.code.name}" class="form-text codemirror" lang="{field.code.lang}" rows="5">{field.code.value}</textarea>
	    </td>
        <td class="form-descr">{field.code.descr}</td>
    </tr>
    <!-- END code -->
    <!-- BEGIN checkbox -->
    <tr>
        <td class="form-label"><input type="checkbox" class="form-checkbox" id="form_{name}_{field.checkbox.name}" name="{field.checkbox.name}" {field.checkbox.value} {field.checkbox.checked} /></td>
        <td class="form-descr" colspan="2"><b>{field.checkbox.head}</b><br />{field.checkbox.descr}</td>
    </tr>
    <!-- END checkbox -->
    <!-- BEGIN select -->
    <tr>
        <td class="form-label">{field.select.head}</td>
        <td class="form-field">
            <select class="form-text" name="{field.select.name}" id="form_{name}_{field.select.name}">
            <!-- BEGIN option -->
                <option value="{field.select.option.value}" {field.select.option.select}>{field.select.option.name}</option>
            <!-- END option -->
            </select>
        </td>
        <td class="form-descr">{field.select.descr}</td>
    </tr>
    <!-- END select -->
    <!-- BEGIN radio -->
    <tr>
        <td class="form-label">{field.radio.head}</td>
        <td class="form-field">
            <ul>
            <!-- BEGIN option -->
                <li><input type="radio" id="form_{name}_{field.radio.name}" name="{field.radio.name}" value="{field.radio.option.value}" {field.radio.option.select} /> {field.radio.option.head}</li>
            <!-- END option -->
            </ul>
        </td>
        <td class="form-descr">{field.radio.descr}</td>
     </tr>
    <!-- END radio -->
    <!-- BEGIN radioline -->
    <tr>
        <td class="form-label">{field.radioline.head}</td>
        <td class="form-field la" colspan="2">
		<!-- BEGIN option -->
			<label for="form_{name}_{field.radioline.name}"><input type="radio" id="form_{name}_{field.radioline.name}" name="{field.radioline.name}" value="{field.radioline.option.value}" {field.radioline.option.select} /> {field.radioline.option.name}</label>
		<!-- END option -->
        </td>
     </tr>
    <!-- END radioline -->
    <!-- BEGIN hidden -->
    <input class="form-hidden" type="hidden" name="{field.hidden.name}" value="{field.hidden.value}" />
    <!-- END hidden -->
    <!-- BEGIN file -->
    <tr>
        <td class="form-label">{field.file.head}</td>
        <td class="form-field"><input class="form-text" type="file" id="form_{name}_{field.file.name}" name="{field.file.name}" /></td>
        <td class="form-descr">{field.file.descr}</td>
    </tr>
    <!-- END file -->
    <!-- BEGIN date -->
    <tr>
        <td class="form-label">{field.date.head}</td>
        <td class="form-field"><input class="form-text" type="date" id="form_{name}_{field.date.name}" name="{field.date.name}" value="{field.date.value}" /></td>
        <td class="form-descr">{field.date.descr}</td>
    </tr>
    <!-- END date -->
    <!-- BEGIN captcha -->
    <tr>
        <td class="form-label">{field.captcha.head}</td>
        <td class="form-field"><script type="text/javascript">var RecaptchaOptions = { theme : "white", lang : "ru" };</script>{field.captcha.image}</td>
        <td class="form-descr">{field.captcha.descr}</td>
    </tr>
    <!-- END captcha -->
    <!-- BEGIN color -->
    <tr>
        <td class="form-label">{field.color.head}</td>
        <td class="form-field"><input class="form-text colorpick" type="text" id="form_{name}_{field.color.name}" name="{field.color.name}" value="{field.color.value}" /></td>
        <td class="form-descr">{field.color.descr}</td>
    </tr>
    <!-- END color -->
<!-- END field -->
</table>


<div class="form-buttons">
<!-- BEGIN buttons -->
<!-- BEGIN submit -->
    <input type="submit" name="{buttons.submit.name}" value="{buttons.submit.value}" class="form-submit" />
<!-- END submit -->
<!-- BEGIN reset -->
    <input type="reset" name="{buttons.reset.name} value="{buttons.reset.value}" class="form-reset" />
<!-- END reset -->
<!-- BEGIN cancel -->
    <input type="button" name="{buttons.button.name} value="{buttons.cancel.value}" class="form-cancel" onclick="javascript:history.back()" />
<!-- END cancel -->
<!-- END buttons -->
</div>

</form>