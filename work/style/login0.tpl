<!DOCTYPE html>
<html lang="ru">

<head>
	<title>{site}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="/style/register.css" type="text/css" />
	<script type="text/javascript" src="/style/jquery.js"></script>
	<script type="text/javascript">
		function section ( name ) {			$("section").hide();
			$("#"+name).show();
			return false;
		}
		$(function() {
			var start = "login";
<!-- BEGIN error -->
			start = "register";
<!-- END error -->
			section( start );
		});
	</script>
</head>

<body>
<div id="container">
	<div id="form">

	<section id="login">

		<div class="block">
			<div class="head">{login}</div>
			<div class="wrap"><form action="" method="post">
				<input type="email" name="in_user" id="in_user" class="input email" placeholder="{mail}" required="required" />
				<input type="password" name="in_pass" id="in_pass" class="input pass" placeholder="{pass}" required="required" />
				<input type="submit" class="submit" value="{enter}" />
			</form></div>
		</div>

		<p><b><a href="#login" onclick="return section('register')">{reg}</a></b></p>
		<p><a href="#login" onclick="return section('forgot')">{forgot}</a></p>

	</section>

	<section id="forgot">

		<div class="block">
			<div class="head">{forgot}</div>
			<div class="wrap"><form action="" method="post">
				<input type="email" name="recover" id="recover" class="input email" placeholder="{formail}" required="required" />
				<input type="submit" class="submit" value="{recover}" tabindex="100" />
			</form></div>
		</div>
		<p><a href="#login" onclick="return section('login')">{login}</a></p>

	</section>

	<section id="register">

		<div class="block">
			<div class="head">{reg}</div>
<!-- BEGIN error -->
			<div class="error">{re}</div>
<!-- END error -->
			<div class="wrap"><form action="" method="post">
				<input type="hidden" name="register" value="user" />
				<input type="hidden" name="ref" value="{ref}" />
				<input type="text" name="name" class="input name {er3}" placeholder="{user}" required="required" />
				<input type="email" name="email" class="input email {er1}" placeholder="{mail}" required="required" />
				<input type="text" name="pass" class="input pass {er2}" placeholder="{pass}" required="required" />
				<input type="submit" class="submit" value="{register}" />
			</form></div>
		</div>

		<p><a href="#login" onclick="return section('login')">{login}</a></p>

	</section>

	</div>
</div>

</body>
</html>