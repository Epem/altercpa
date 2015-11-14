<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-RU">

<head>
	<title>Файлы</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="/style/style.css" type="text/css" />
	<script type="text/javascript" src="/style/jquery.js"></script>
	<script type="text/javascript" src="/style/tiny_mce.js"></script>
	<script type="text/javascript" src="/style/jquery.tinymce.js"></script>
	<script type="text/javascript" src="/style/tiny_mce_popup.js"></script>
    <script type="text/javascript">
		function selectURL ( URL ) {
	        var win = tinyMCEPopup.getWindowArg("window");
	        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;
	        if (typeof(win.ImageDialog) != "undefined") {
	            if (win.ImageDialog.getImageData)		win.ImageDialog.getImageData();
	            if (win.ImageDialog.showPreviewImage)	win.ImageDialog.showPreviewImage(URL);
	        }
	        tinyMCEPopup.close();
	  	}
    </script>
</head>

<body id="altervision-core">

<div id="filelist">

	<form action="{upload}" method="post" enctype="multipart/form-data">
	<div class="upload-form">
		<table cellspacing="0" cellpadding="0"><tr>
			<td width="10%" align="left"><input class="form-button" type="submit" value="Загрузить" />
			<td width="90%" align="center"><input type="file" name="file" class="form-text" /></td>
		</tr></table>
	</div>
	</form>

	<ul class="files">
<!-- BEGIN file -->
		<li>
			<span class="name"><a href="#" onclick="selectURL('{file.url}')">{file.name}</a></span>
			<span class="more">{file.time}</span>
			<span class="size">{file.size}</span>
			<span class="del"><a href="{file.del}" class="delete" onclick="return confirm('Вы уверены, что хотите удалить этот объект?');">Удалить</a></span>
		</li>
<!-- END file -->
	</ul>

</div>

</body>
</html>