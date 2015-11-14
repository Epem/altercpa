
	<div id="offer-left">

		<div class="the-offer">
			<img class="offer-image" src="{logo}" alt="{offer_name}" />
			<div class="offer-name">
				<span class="rur green">{offer_price}</span>
				{offer_name}
			</div>
			<div class="offer-descr">{text}</div>
			<div class="offer-pay">
				<div>{country}</div>
				{wm}: <span class="rur green">{offer_wm}</span>
			</div>
			<div class="offer-info">
				<div class="offer-gender">
					<span class="stat-m">{stat_m}%</span>
					<span class="stat-f">{stat_f}%</span>
				</div>
				<div class="offer-epc"><b>CR</b>: {cr}% <b>EPC</b>: {epc}</div>
			</div>
<!-- BEGIN wm -->
			<div class="offer-ref">{ref}: {offer_ref}</div>
			<a class="offer-add" onclick="return confirm('{confirm}')" href="{u_add}">
				{add}
				<span>{status}</span>
			</a>
<!-- END wm -->
		</div>

		<div class="offer-wmi">{offer_info}</div>

	</div>

	<div id="offer-right">

		<div class="offer-medium"><div>

<!-- BEGIN bd -->
			<h3>Обработка заказов</h3>
			<table class="post-table">
				<tr>
					<th width="87%">Статус заказа</th>
					<th width="13%">Процент</th>
				</tr>
				<tr>
					<td>Ожидают подтверждения</td>
					<td align="center">{bd.w}%</td>
				</tr>
				<tr class="dark">
					<td>&mdash; Новые заказы</td>
					<td align="center">{bd.nw}%</td>
				</tr>
				<tr class="dark">
					<td>&mdash; В процессе обработки</td>
					<td align="center">{bd.pr}%</td>
				</tr>
				<tr class="dark">
					<td>&mdash; Перезвонить позднее</td>
					<td align="center">{bd.rc}%</td>
				</tr>
				<tr class="dark">
					<td>&mdash; Не удалось дозвониться</td>
					<td align="center">{bd.na}%</td>
				</tr>
				<tr>
					<td>Принятые заказы</td>
					<td align="center" class="green">{bd.a}%</td>
				</tr>
				<tr>
					<td>Отказы (подробнее - справа)</td>
					<td align="center" class="red">{bd.c}%</td>
				</tr>
				<tr><td colspan="2" class="sub">Статистика с {bd.from} по {bd.to}</td></tr>
			</table>

            <h3>Заказы по дням</h3>
			<div id="gbd" style="width: 90%; height: 250px; margin: 10px auto;"></div>
<!-- END bd -->

			<h3>Лэндинги</h3>
			<table class="post-table">
				<tr>
					<th width="80%">{url}</th>
					<th width="10%">CR</th>
					<th width="10%">EPC</th>
				</tr>
		<!-- BEGIN site -->
				<tr>
					<td><a href="http://{site.u}" target="_blank">{site.u}</a></td>
					<td align="center">{site.cr}%</td>
					<td align="center">{site.epc}</td>
				</tr>
		<!-- END site -->
		<!-- BEGIN nosite -->
				<tr><td class="noitems" colspan="3">Лэндинги ещё не добавлены</td></tr>
		<!-- END nosite -->
			</table>

<!-- BEGIN wm -->
			<h3>Потоки</h3>
			<table class="post-table">
				<tr>
					<th width="70%">Название потока</th>
					<th width="10%">CR</th>
					<th width="10%">EPC</th>
					<th width="10%">Заработок</th>
				</tr>
		<!-- BEGIN flow -->
				<tr>
					<td><a href="{wm.flow.stats}">{wm.flow.name}</a></td>
					<td align="center">{wm.flow.cr}%</td>
					<td align="center">{wm.flow.epc}</td>
					<td align="center">{wm.flow.total}</td>
				</tr>
		<!-- END flow -->
		<!-- BEGIN noflow -->
				<tr><td class="noitems" colspan="4">
					<b>{status}</b><br />
					<a onclick="return confirm('{confirm}')" href="{u_add}">{add}</a>
				</td></tr>
		<!-- END noflow -->
			</table>
<!-- END wm -->

		</div></div>

		<div class="offer-medium"><div>

<!-- BEGIN bt -->
			<h3>Причины отказов</h3>
			<table class="post-table">
				<tr>
					<th width="87%">Причина отказа</th>
					<th width="13%">Процент</th>
				</tr>
		<!-- BEGIN rs -->
				<tr>
					<td>{bt.rs.t}</td>
					<td align="center">{bt.rs.c}%</td>
				</tr>
		<!-- END rs -->
				<tr><td colspan="2" class="sub">Статистика с {bt.from} по {bt.to}</td></tr>
			</table>

            <h3>Заказы по часам</h3>
			<div id="gbt" style="width: 90%; height: 250px; margin: 10px auto;"></div>
<!-- END bt -->

			<h3>Прелэндинги</h3>
			<table class="post-table">
				<tr>
					<th width="80%">{url}</th>
					<th width="10%">CR</th>
					<th width="10%">EPC</th>
				</tr>
		<!-- BEGIN space -->
				<tr>
					<td><a href="http://{space.u}" target="_blank">{space.u}</a></td>
					<td align="center">{space.cr}%</td>
					<td align="center">{space.epc}</td>
				</tr>
		<!-- END space -->
		<!-- BEGIN nospace -->
				<tr><td class="noitems" colspan="3">У оффера нет прелендингов</td></tr>
		<!-- END nospace -->
			</table>
		</div></div>

		<div class="clear"></div>

	</div>

	<div class="clear"></div>

<!-- BEGIN bd -->
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {

			var databd = google.visualization.arrayToDataTable([
				['День', 'Заказы'],
<!-- BEGIN r -->
				[ '{bd.r.d}', {bd.r.c} ],
<!-- END r -->
			]);


<!-- END bd -->
<!-- BEGIN bt -->

			var databt = google.visualization.arrayToDataTable([
				['Час', 'Заказы'],
<!-- BEGIN r -->
				[ '{bt.r.t}', {bt.r.c} ],
<!-- END r -->
			]);

			var options = { legend: { position: 'none' }, chartArea: { top: "5%", left: "0%", width: "98%", height: "80%" } };

			var chartbd = new google.visualization.ColumnChart(document.getElementById('gbd'));
			chartbd.draw( databd, options );

			var chartbt = new google.visualization.ColumnChart(document.getElementById('gbt'));
			chartbt.draw( databt, options );

		}
	</script>
<!-- END bt -->