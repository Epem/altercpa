<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru-RU">

<head>
	<title>{title}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- BEGIN meta -->
	 {meta.m}
<!-- END meta -->
	<link rel="icon" type="image/png" href="/favicon.png" />
	<link rel="shortcut icon" type="image/png" href="/favicon.png" />
	<script type="text/javascript">
	    $(document).ready(function() { $("#menu ul").css({display: "none"}); $("#menu li").hover(function(){$(this).find('ul:first').css({visibility: "visible", display: "none"}).show(268);},function(){$(this).find('ul:first').css({visibility: "hidden"});}); });
	</script>
</head>

<body id="altervision-core" class="{headclass}">

<div id="container">

	<div id="header">

		<div id="header-logo">

			<div id="header-title">
				<h1 class="{headclass}"><a href="/"><span>{site_name}</span></a></h1>
			</div>

			<div id="header-user">
				<div class="user-hello">
					<div class="user-name">{user_name}</div> {user_vip} {user_ext}
					<a class="logout" href="{u_logout}">{logout}</a>
				</div>
				<div class="user-line">
					<a class="money" href="{u_money}">{money}</a>: {cash}
<!-- BEGIN support -->
					<a class="support" href="{u_support}" title="{support}">{support}</a>
<!-- END support -->
					<a href="{u_profile}" class="profile">{profile}</a>
				</div>
			</div>

		</div>

	</div>

	<div id="page">

		<div id="mainline">{mainline}</div>

		<ul id="menu">
<!-- BEGIN menu -->
			<li class="{menu.hassubs}"><a href="{menu.link}" class="{menu.div}">{menu.name}</a>
<!-- BEGIN sub -->
					<ul>
<!-- BEGIN item -->
						<li><a href="{menu.sub.item.link}">{menu.sub.item.name}</a></li>
<!-- END item -->
					</ul>
<!-- END sub -->
			</li>
<!-- END menu -->
		</ul>

		<div id="content">

<!-- BEGIN info -->
		<div id="info">
<!-- BEGIN msg -->
			<p class="msg-{info.msg.type}">{info.msg.text}</p>
<!-- END msg -->
		</div>

		<script type="text/javascript">
	    	function hidemessages () { $('#info').fadeOut('slow'); }
		    $(document).ready(function() {	setTimeout ("hidemessages();", 10000); });
	    </script>
<!-- END info -->