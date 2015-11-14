<div class="lister">
	<div class="shown">
		{shown}
<!-- BEGIN add -->
		<a class="add" href="{u_add}">{add}</a>
<!-- END add -->
	</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<ul class="news">
<!-- BEGIN news -->
	<li class="news news{news.type}">
<!-- BEGIN edit -->
		<div class="rf">
			<a class="edit" href="{news.edit}">{edit}</a>
			<a class="delete" href="{news.del}" onclick="return confirm('Вы уверены, что хотите удалить эту новость?');">{del}</a>
		</div>
<!-- END edit -->
		<h4 class="news-title"><a href="{news.url}">{news.title}</a> {news.vip}</h4>
		<div class="news-info">Опубликовано <b>{news.date}</b> в разделе <b>{news.group}</b></div>
		<div class="entry">{news.text}</div>
		<div class="news-comment"><a class="news-comments" href="http://27cm.ru{news.url}#disqus_thread" data-disqus-identifier="news27cm{news.id}">Нет комментариев</a><a href="{news.url}"> - обсудить эту новость</a></div>
	</li>
<!-- END news -->
</ul>

<div class="lister">
	<div class="shown">
		{shown}
<!-- BEGIN add -->
		<a class="add" href="{u_add}">{add}</a>
<!-- END add -->
	</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<script type="text/javascript">
    var disqus_shortname = '27cm';
    (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = '//' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
</script>