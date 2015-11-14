<!-- BEGIN face -->
	<p>Служба поддержки стабильно работает по будням с 9 до 18 часов по московскому времени. В остальное время мы стараемся отвечать на Ваши запросы по мере возможности. Прежде чем задать вопрос в техническую поддержку, рекомендуется посмотреть <a href="/help/#wm">данное руководство</a> и ознакомиться с <a href="/help/faq.html">FAQ</a>. Если там не нашлось нужного ответа, или он вас не устроил, или вам просто нравится общаться с живыми людьми - служба технической поддержки к вашим услугам!</p>

</div>

<script type="text/javascript" src="/style/support.js"></script>

<div id="site-support">

	<div id="support-form" class="minimal">

		<form action="{u_add}" method="post" id="suppform">
			<textarea id="supptext" name="text" class="form-text" rows="4" placeholder="{placeholder}"></textarea>
			<div class="button"><input id="suppbutt" type="submit" value="{add}" class="form-button" /></div>
		</form>

	</div>

	<ul id="support" class="comment-list">
<!-- END face -->
<!-- BEGIN msg -->
		<li class="{msg.rclass}">
	       	<div class="comment-info">
	       		<span class="{msg.uclass}"><a href="{msg.link}">{msg.user}</a></span>
	       		<span class="time">{msg.time}</span>
<!-- BEGIN admin -->
				<a href="mailto:{msg.admin.u}" class="email">{msg.admin.u}</a>
				<a href="http://ipgeobase.ru/?address={msg.ip}" target="_blank" class="flows">{msg.ip}</a>
				<span class="order-country" >{msg.geo}</span>
<!-- END admin -->
	       	</div>
	       	<div class="comment-text">{msg.text}</div>
		</li>
<!-- END msg -->
<!-- BEGIN more -->
		<li class="sitemore"><a href="#" onclick="return morenews();">{showmore}</a></li>
<!-- END more -->
<!-- BEGIN face -->
	</ul>

<!-- BEGIN nomsg -->
<div class="nomsg"><span>{nomessage1}</span>{nomessage2}</div>
<!-- END nomsg -->

<audio id="suppnotify">
	<source src="/style/audio/notify.ogg" type="audio/ogg" />
	<source src="/style/audio/notify.mp3" type="audio/mpeg" />
	<source src="/style/audio/notify.wav" type="audio/wav" />
</audio>

<script type="text/javascript">
var loadlink = "{u_load}";
</script>
<!-- END face -->
<script type="text/javascript">
	tbset( {mn}, {mx}, {mc} );
</script>