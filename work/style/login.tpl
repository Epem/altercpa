<!DOCTYPE html>
<html dir="ltr" lang="ru-RU">

<head>
	<title>{site}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="/style/login.css" type="text/css" />
	<script type="text/javascript" src="/style/jquery.js"></script>
	<link rel="icon" type="image/png" href="/favicon.png" />
	<link rel="shortcut icon" type="image/png" href="/favicon.png" />
</head>

<body>

<div id="login"><div class="wrap">
	<form id="loginform" action="" method="post">
		<span>{login}</span>
		<input type="email" name="in_user" id="in_user" class="input email" placeholder="{mail}" required="required" /><input type="password" name="in_pass" id="in_pass" class="input pass" placeholder="{pass}" required="required" /><input type="submit" class="submit" value="{enter}" tabindex="100" />
		<a href="#" onclick="$('#recoverform').show();$('#loginform').hide();return false;">{forgot}</a>
	</form>
	<form id="recoverform" action="" method="post">
		<span>{formail}</span>
		<input type="email" name="recover" id="recover" class="input email" placeholder="{mail}" required="required" /><input type="submit" class="submit" value="{recover}" tabindex="100" />
		<a href="#" onclick="$('#recoverform').hide();$('#loginform').show();return false;">{cancel}</a>
	</form>
</div></div>

<div id="container">

	<div id="header">
		<h1><a href="/"><span>{site}</span></a></h1>
		<h2>Удобные интернет-продажи для Вас!</h2>
		<p>Мы предлагаем качественный сервис для привлечения клиентов и осуществления сделок купли-продажи товаров через интернет.</p>
		<div id="register">
<!-- BEGIN error -->
			<div class="error">{re}</div>
<!-- END error -->
			<form action="" method="post">
			<input type="hidden" name="register" value="user" />
			<input type="hidden" name="ref" value="{ref}" />
			<div class="wrap">
				<input type="text" name="name" class="input name {er3}" placeholder="{user}" required="required" />
				<input type="email" name="email" class="input email {er1}" placeholder="{mail}" required="required" />
				<input type="text" name="pass" class="input pass {er2}" placeholder="{pass}" required="required" />
			</div>
			<input type="submit" class="register" value="{register}" tabindex="100" />
			</form>
		</div>
	</div>

	<div class="textblock">

	<div class="textblock" id="about">

        <h2>Партнёрская программа для веб-мастеров</h2>
		<p>Веб-мастерам и специалистам, занимающимся привлечением покупателей и клиентов, мы предлагаем принять участие в нашей партнёрской программе. К Вашим услугам: стабильные выплаты, продвинутая аналитика, только качественные офферы от наших партнёров.</p>
		<p>Партнёрская программа работает по модели CPS («cost per sale» - оплата за продажу), Вы получаете партнёрское вознаграждение за каждый принятый нашими продавцами-участниками заказ.</p>

		<h2>Продавцам</h2>
		<p>Наш проект помогает в поиске потенциальных покупателей для ваших товаров. Мы предлагаем готовое решение для торгового бизнеса: привлечение клиентов и удобный интерфейс работы с покупателями, заказами и товарами. К вашим услугам SMS-информирование покупателей, отслеживание доставки товаров, неограниченное количество работающих менеджеров, инструменты аналитики. Вы можете связаться с нами: <a href="mailto:info@work.cpa">info@work.cpa</a></p>

	</div>

	<div id="footer">
		<div class="wm"><noindex>
			<a href="http://www.megastock.ru/" target="_blank"><img src="/style/images/wm.png" alt="www.megastock.ru" border="0"></a>
			<a href="https://passport.webmoney.ru/asp/certview.asp?wmid=012345678910" target="_blank"><img src="/style/images/wma.png" alt="Здесь находится аттестат нашего WM идентификатора" border="0" /></a>
		</noindex></div>
		<div class="copyright">© 2014г. {site} - платформа автоматизации интернет-торговли</div>
	</div>

</div>

</body>

</html>