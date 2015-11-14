<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<span class="icon cal"></span>
		<input title="Дата начала отчёта" type="date" class="text" name="from" value="{from}" />
		<input title="Дата окончания отчёта" type="date" class="text" name="to" value="{to}" />
		<label title="{offer}">
			<span class="icon offer"></span>
			<select name="o" class="text">
				<option value="">&mdash; {offer} &mdash;</option>
<!-- BEGIN offer -->
				<option value="{offer.value}" {offer.select}>{offer.name}</option>
<!-- END offer -->
			</select>
		</label>
		<label title="{flow}">
			<span class="icon flow"></span>
			<select name="f" class="text">
				<option value="">&mdash; {flow} &mdash;</option>
<!-- BEGIN flow -->
				<option value="{flow.value}" {flow.select}>{flow.name}</option>
<!-- END flow -->
			</select>
		</label>
		<input type="submit" class="form-button" value="{show}" />
	</form>
	<div class="shown">
		<!--a href="{u_csv}" class="excel">CSV</a>
		<a href="/help/#target" class="help">Помощь</a-->
		<a href="{u_today}" class="date">Сегодня</a>
	</div>
	<div class="clear"></div>
</div>

<table class="post-table tablesorter" cellspacing="0" id="sourceslist">

<thead>
<tr>
	<!--th class="cb" width="1%"><input type="checkbox" /></th-->
	<th width="20%" colspan="2" class="headerSortDown">{target}</th>
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
	<!--td class="cb"><input type="checkbox" /></td-->
	<td><span class="target tg{stat.type}">{stat.name}</span></td>
	<td align="center" width="1%" nowrap="nowrap" class="small">
		<a href="{stat.edit}" class="edit">Правка</a>
		<a href="{stat.del}" class="delete" onclick="return confirm('{confirm}');">Удалить</a>
	</td>
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

<div class="lister">
	<form class="lf" action="{u_add}" method="post">
		<label title="Добавить новую цель">
			<span class="icon trgt"></span>
			<input title="Дата начала отчёта" type="text" class="text" name="name" placeholder="Название цели" />
		</label>
		<select name="type" class="text">
			<option value="0">&mdash; тип &mdash;</option>
			<option value="1">ВКонтакте</option>
		</select>
		<input type="submit" class="form-button" value="Добавить цель" />
	</form>
	<div class="clear"></div>
</div>

<script type="text/javascript" src="/style/jquery.tablesorter.js"></script>
<script type="text/javascript">
//	$("#sourceslist").tablesorter({ headers: { 0: { sorter: false} }, sortList: [[1,0]] });
	$("#sourceslist").tablesorter({ sortList: [[0,0]] });
</script>