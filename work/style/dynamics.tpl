<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<span class="icon cal"></span>
		<input title="Дата начала отчёта" type="date" class="text" name="from" value="{from}" />
		<input title="Дата окончания отчёта" type="date" class="text" name="to" value="{to}" />
		<input type="submit" class="form-button" value="{show}" />
	</form>
	<div class="shown">
		<a class="date" href="{u_day7}">{day7}</a>
		<a class="date" href="{u_day30}">{day30}</a>
		<a class="date" href="{u_day90}">{day90}</a>
	</div>
	<div class="clear"></div>
</div>

<div id="graphic" style="width: 97%; height: 300px; margin: 10px auto;"></div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{date}</th>
	<th>{income}</th>
	<th>{outcome}</th>
	<th colspan="2">{total}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th>{date}</th>
	<th>{income}</th>
	<th>{outcome}</th>
	<th colspan="2">{total}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN date -->
<tr>
	<td align="center">{date.day}, {date.wd}</td>
	<td align="center">{date.in}</td>
	<td align="center">{date.out}</td>
	<td align="center">{date.total}</td>
	<td align="center" class="small">{date.delta}</td>
</tr>
<!-- END date -->
</tbody>

</table>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['{date}', '{total}'],
<!-- BEGIN gr -->
			[ '{gr.smd}', {gr.smt} ],
<!-- END gr -->
		]);

		var options = { legend: { position: 'none' }, chartArea: { top: "5%", left: "4%", width: "95%", height: "80%" }, pointSize: 3 };
		var chart = new google.visualization.LineChart(document.getElementById('graphic'));
		chart.draw(data, options);

	}
</script>