	<div class="entry">{text}</div>

</div>

<div id="offers">

<!-- BEGIN offer -->
	<div class="the-offer">
		<a href="{offer.u}"><img class="offer-image" src="{offer.logo}" alt="{offer.name}" /></a>
		<div class="offer-name">
			<span class="rur green">{offer.price}</span>
			<a href="{offer.u}">{offer.name}</a>
		</div>
		<div class="offer-descr">{offer.text}</div>
		<div class="offer-pay">
			<div>{offer.country}</div>
			{wm}: <span class="rur green">{offer.wm}</span>
		</div>
		<div class="offer-info">
			<div class="offer-gender">
				<span class="stat-m">{offer.stat_m}%</span>
				<span class="stat-f">{offer.stat_f}%</span>
			</div>
			<div class="offer-epc"><b>CR</b>: {offer.cr}% <b>EPC</b>: {offer.epc}</div>
		</div>
<!-- BEGIN wm -->
		<div class="offer-ref">{ref}: {offer.ref}</div>
		<a class="offer-add" onclick="return confirm('{confirm}')" href="{offer.add}">
			{add}
			<span>{offer.status}</span>
		</a>
<!-- END wm -->
	</div>
<!-- END offer -->

	<div class="clear"></div>