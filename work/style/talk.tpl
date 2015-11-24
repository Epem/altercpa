	<h2>{title}</h2>
	<div class="news-single-info news{type}">Опубликовано <b>{date}</b> в разделе <b>{group}</b></div>
	<div class="entry">{text}</div>

	<div id="disqus_thread"></div>
    <script type="text/javascript">
        var disqus_shortname = '{disqus}';
        var disqus_identifier = 'news{disqus}{id}';
        var disqus_url = '{base}{url}';
        var disqus_title = '{title}';
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
