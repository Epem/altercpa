<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<span class="icon cal"></span>
		<input title="Дата начала отчёта" type="date" class="text" name="from" value="{from}" />
		<input title="Дата окончания отчёта" type="date" class="text" name="to" value="{to}" />
		<input type="submit" class="form-button" value="{show}" />
	</form>
	<div class="shown">
		<a href="{u_today}" class="date">Сегодня</a>
		<a href="{u_yesterday}" class="date">Вчера</a>
	</div>
	<div class="clear"></div>
</div>

<table class="post-table tablesorter" cellspacing="0" id="sourceslist">

<thead>
<tr>
	<th width="10%">{offer}</th>
	<th width="10%">{flow}</th>
	<th width="10%">{spaces}</th>
	<th width="10%">{unique}</th>
	<th width="10%">Лэндинг</th>
	<th width="10%">{unique}</th>
	<th width="10%">{accept}</th>
	<th width="10%">{wait}</th>
	<th width="10%">{cancel}</th>
	<th width="10%">{total}</th>
</tr>
</thead>

<tbody>
<!-- BEGIN stat -->
<tr class="{stat.class}">
	<td>{stat.offer}</td>
	<td>{stat.flow}</td>
	<td align="center">{stat.spaces}</td>
	<td align="center">{stat.suni}</td>
	<td align="center">{stat.clicks}</td>
	<td align="center">{stat.unique}</td>
	<td align="center">{stat.ca}</td>
	<td align="center">{stat.cw}</td>
	<td align="center">{stat.cc}</td>
	<td align="center">{stat.ct}</td>
</tr>
<!-- END stat -->
<!-- BEGIN nostat -->
<tr><td colspan="10" class="noitems">{nostats}</td></tr>
<!-- END nostat -->
</tbody>

</table>

<script type="text/javascript" src="/style/jquery.tablesorter.js"></script>
<script type="text/javascript">
	$("#sourceslist").tablesorter({ sortList: [[0,0],[1,0]] });
</script>