	<div class="entry">{text}</div>

</div>

<div id="tools">
	<span>Инструменты:</span>
	<a href="{u_stats}" class="stats">Статистика по кликам</a>
	<a href="{u_flowstat}" class="flows">Разбор по потокам</a>
	<a href="{u_lead}" class="exti">Лиды и заказы</a>
	<a href="{u_sources}" class="link">Источники трафика</a>
	<a href="{u_target}" class="target">Трафик по целям</a>
	<a href="{u_domain}" class="parked">Паркованные домены</a>
</div>

<div id="flows">

<!-- BEGIN offer -->
	<div class="the-flow">

		<div class="flow-info">

			<div class="flow-links">
				<a href="{offer.add}" class="add green">Новый поток</a>
				<a href="#" class="flows grey" onclick="setflow('{offer.id}'); $('#link{offer.id}').toggle(); return false;">Сгенерировать ссылку потока</a>
			</div>

			<div class="flow-offer">{offer.name}</div>

			<div class="flow-stats">
				<a href="{offer.url}" class="offers">Подробнее об оффере</a>
				<a href="{offer.stats}" class="stats">{stats}</a>
			</div>

		</div>

		<div id="link{offer.id}" class="flow-link hidden">
			<table class="offer-link-table">

			<tr>
				<td class="olt-label">Поток</td>
				<td class="olt-field"><select id="offer{offer.id}flow" onchange="setflow({offer.id});">
				<!-- BEGIN flow -->
					<option value="{offer.flow.id}" id="flowdata{offer.flow.id}" data-site="{offer.flow.site}" data-space="{offer.flow.space}" data-cb="{offer.flow.cb}" data-param="{offer.flow.param}" data-url="{offer.flow.url}" data-pbu="{offer.flow.pbu}">{offer.flow.name}</option>
				<!-- END flow -->
				</select></td>
			</tr>

			<tr>
				<td class="olt-label">Лэндинг</td>
				<td class="olt-field"><select id="offer{offer.id}land" onchange="makelink({offer.id});">
			<!-- BEGIN site -->
					<option id="offer{offer.id}land{offer.site.id}" value="{offer.site.id}" data-cr="{offer.site.cr}" data-epc="{offer.site.epc}">{offer.site.url}</option>
			<!-- END site -->
				</select></td>
			</tr>

		<!-- BEGIN subsite -->
			<tr>
				<td class="olt-label">Прокладка</td>
				<td class="olt-field"><select id="offer{offer.id}space" onchange="makelink({offer.id});">
					<option value="0">&mdash; без прокладки &mdash;</option>
					<!-- BEGIN s -->
					<option id="offer{offer.id}space{offer.subsite.s.id}" value="{offer.subsite.s.id}" data-cr="{offer.subsite.s.cr}" data-epc="{offer.subsite.s.epc}">{offer.subsite.s.url}</option>
					<!-- END s -->
				</select></td>
			</tr>
		<!-- END subsite -->

			<tr>
				<td class="olt-label">Трафбек</td>
				<td class="olt-field"><input type="text" id="offer{offer.id}url" onchange="makelink({offer.id});" /></td>
			</tr>

			<tr>
				<td class="olt-label"><a target="_blank" href="/help/faq.html#postback">Постбэк</a></td>
				<td class="olt-field"><input type="text" id="offer{offer.id}pbu" onchange="makelink({offer.id});" /></td>
			</tr>

		<!-- BEGIN redmn -->
			<tr>
				<td class="olt-label">Редирект</td>
				<td class="olt-field"><select id="offer{offer.id}redmn" onchange="makelink({offer.id});">
					<option value="0">&mdash; стандартный домен сети &mdash; </option>
					<!-- BEGIN s -->
					<option value="{offer.redmn.s.url}">{offer.redmn.s.url}</option>
					<!-- END s -->
				</select></td>
			</tr>
		<!-- END redmn -->

			<tr id="targets{offer.id}">
				<td class="olt-label" align="right"><input type="checkbox" id="usetargets{offer.id}" onchange="loadtargets({offer.id});" /></td>
				<td class="olt-field"><label for="usetargets{offer.id}">Анализ трафика по целям</label> &nbsp; <a href="{u_target}" class="target">Настроить цели</a></td>
			</tr>

			<tr>
				<td class="olt-label" align="right"><input type="checkbox" id="usecomeback{offer.id}" onchange="makelink({offer.id});" /></td>
				<td class="olt-field"><label for="usecomeback{offer.id}">{flow_cb}</label></td>
			</tr>

			<tr>
				<td class="olt-label" align="right"><input type="checkbox" id="usesimple{offer.id}" onchange="makelink({offer.id});" /></td>
				<td class="olt-field"><label for="usesimple{offer.id}">Указать параметр <i>?flow=id</i> <small>(<b>обязательно для Mail.ru</b> и некоторых других сетей)</small></label></td>
			</tr>

			<tr><td colspan="2" class="olt-button">
				<input type="button" onclick="makelink({offer.id});" value="Сгенерировать ссылку потока" />
			</td></tr>

			<tr>
				<td class="olt-label">Ссылка потока</td>
				<td class="olt-field"><input type="text" class="flow-link" readonly="readonly" id="offer{offer.id}link" placeholder="Нажмите «Сгенерировать ссылку потока» для получения ссылки" /></td>
			</tr>

			<tr><td colspan="2" class="olt-button">
				Для выбранного сайта: <b>CR</b> - <span id="offer{offer.id}cr">0.0</span>%
				<b>EPC</b> - <span id="offer{offer.id}epc" class="rur green">0.0</span>
			</td></tr>

			</table>
		</div>

		<table class="post-table">
			<thead><tr>
				<th width="50%">{name}</th>
				<th width="10%">CR</th>
				<th width="10%">EPC</th>
				<th width="10%">{total}</th>
				<th width="20%">{action}</th>
			</tr></thead>
			<tbody>
<!-- BEGIN flow -->
			<tr>
				<td><a href="{offer.flow.stats}" class="stats" title="{stats} - {offer.flow.name}">{offer.flow.name}</a></td>
				<td align="center" nowrap="nowrap">{offer.flow.cr}%</td>
				<td align="center" nowrap="nowrap">{offer.flow.epc}</td>
				<td align="center" nowrap="nowrap"><span class="rur">{offer.flow.total}</span></td>
				<td align="center" nowrap="nowrap">
					<a class="flows" href="#" onclick="return setlink( {offer.id}, {offer.flow.id} )">Ссылка</a>
					<a class="edit" href="{offer.flow.edit}">{edit}</a>
					<a class="delete" href="{offer.flow.del}" onclick="return confirm('{confirm}')">{del}</a>
				</td>
			</tr>
<!-- END flow -->
			</tbody>
		</table>
	</div>
<!-- END offer -->

<!-- BEGIN noflow -->
<div class="helpblock">
	<p>Вы пока не создали ни одного потока трафика. Для начала работы необходимо иметь хотя бы один поток. Чтобы создать поток, необходимо выбрать интересующий вас оффер. Для этого перейдите в раздел "Офферы" основного меню.</p>
	<a href="/offers"><img src="/style/help/flow1.png" alt="Перейдите в раздел Офферы" /></a>
	<p>В списке офферов вы сможете подобрать подходящие вам предложения. Чтобы просмотреть подробную информацию об оффере, ознакомиться с имеющимися рекламными материалами, лэндингами, прокладками и другой дополнительной информацией об оффере, кликните по его названию или картинке. Для создания потока нажмите на кнопку "Создать новый поток" соответствующего оффера.</p>
	<a href="/offers"><img src="/style/help/flow2.png" alt="Нажмите на Создать новый поток" /></a>
	<p>При создании потока вам будет предложено указать его название для удобства анализа. По окончании создания потока вы сможете сгенерировать ссылку для  трафика.</p>
</div>
<!-- END noflow -->

<script type="text/javascript">

	function setlink ( offer, flow ) {
		$("#offer"+offer+"flow").val( flow );
		setflow( offer );
		$("#link"+offer).show();
		return false;

	}

	function setflow ( offer ) {
		var flow = $("#offer"+offer+"flow").val();
        $("#offer"+offer+"land").val( $("#flowdata"+flow).attr("data-site") );
        $("#offer"+offer+"space").val( $("#flowdata"+flow).attr("data-space") );
        $("#offer"+offer+"url").val( $("#flowdata"+flow).attr("data-url") );
        $("#offer"+offer+"pbu").val( $("#flowdata"+flow).attr("data-pbu") );
        $("#usecomeback"+offer).prop( "checked", $("#flowdata"+flow).attr("data-cb") == 1 ? true : false )
        $("#usesimple"+offer).prop( "checked", $("#flowdata"+flow).attr("data-param") == 1 ? true : false )

		makelink( offer );
		return false;

	}

	function makelink ( offer ) {

		var usecb = $("#usecomeback"+offer).prop( "checked" ) ? 1 : 0;
		var usesm = $("#usesimple"+offer).prop( "checked" ) ? 1 : 0;
		var flsep = usesm ? "/?flow=" : "/?";
		var flow  = $("#offer"+offer+"flow").val();
		var landi = $("#offer"+offer+"land").val();
		var landn = $("#offer"+offer+"land"+landi).text();
		var spaci = parseInt( $("#offer"+offer+"space").val() );
		var spacn = $("#offer"+offer+"space"+spaci).text();
		var tburl = $("#offer"+offer+"url").val();
		var pburl = $("#offer"+offer+"pbu").val();
		var redmn = $("#offer"+offer+"redmn").val();
		var targt = $("#offer"+offer+"targt").val();
		var url = "http://";

		if ( typeof(redmn) == "undefined" || redmn == "" || redmn == "0" ) {
			if ( tburl == "" ) {
				if ( spaci > 0 ) {
		        	url += spacn + flsep + flow + "-" + landi;
		        	var cr = $("#offer"+offer+"space"+spaci).attr( "data-cr" );
		        	var epc = $("#offer"+offer+"space"+spaci).attr( "data-epc" );
				} else {					url += landn + flsep + flow;
		        	var cr = $("#offer"+offer+"land"+landi).attr( "data-cr" );
		        	var epc = $("#offer"+offer+"land"+landi).attr( "data-epc" );
				}
				if ( usecb ) url += "&cb";
			} else url = "{flow_rd}go"+flow;
		} else url = "http://"+redmn+"/go"+flow;

		if ( typeof(targt) != "undefined" && targt != "" && targt != "0" ) {
			url = url + ( ( url.indexOf('?') === -1 ) ? '?' : '&' ) + 't=' + targt;
		}

		$("#offer"+offer+"link").val( url );
		$("#offer"+offer+"cr").text( cr );
		$("#offer"+offer+"epc").text( epc );

		if ( $("#flowdata"+flow).attr( "data-site" ) != landi )		flowchange ( flow, 'site', 	landi );
		if ( $("#flowdata"+flow).attr( "data-space" ) != spaci )	flowchange ( flow, 'space', spaci );
		if ( $("#flowdata"+flow).attr( "data-cb" ) != usecb )		flowchange ( flow, 'cb', 	usecb );
		if ( $("#flowdata"+flow).attr( "data-param" ) != usesm )	flowchange ( flow, 'param', usesm );
		if ( $("#flowdata"+flow).attr( "data-url" ) != tburl )		flowchange ( flow, 'url', 	tburl );
		if ( $("#flowdata"+flow).attr( "data-pbu" ) != pburl )		flowchange ( flow, 'pbu', 	pburl );

	}

	function loadtargets ( offer ) {		$("#targets"+offer).load( "{flow_tgt}" + offer );
	}

	function flowchange ( flow, param, value ) {		$("#flowdata"+flow).attr( "data-"+param, value );
		$.get( "{flow_ajax}"+flow+"?"+param+"="+escape(value) );
	}

</script>