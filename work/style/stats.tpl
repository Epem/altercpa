<div class="entry">{text}</div>

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
<!-- BEGIN help -->
		<a href="/help/#stats" class="help">Помощь</a>
<!-- END help -->
		<a href="{u_csv}" class="excel">CSV</a>
	</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{date}</th>
	<th colspan="2">{spaces}</th>
	<th colspan="2">Лэндинг</th>
	<th colspan="2">Конверсия</th>
	<th colspan="2">{accept}</th>
	<th colspan="2">{wait}</th>
	<th colspan="2">{cancel}</th>
</tr>
<tr>
	<td class="sub">{from} - {to}</td>
	<td class="sub">{clicks}</td>
	<td class="sub">{unique}</td>
	<td class="sub">{clicks}</td>
	<td class="sub">{unique}</td>
	<td class="sub">CR</td>
	<td class="sub">EPC</td>
	<td class="sub">Кол.</td>
	<td class="sub">Сумма</td>
	<td class="sub">Кол.</td>
	<td class="sub">Сумма</td>
	<td class="sub">Кол.</td>
	<td class="sub">Сумма</td>
</tr>
</thead>

<tfoot>
<tr>
	<td class="sub">{from} - {to}</td>
	<td class="sub">{clicks}</td>
	<td class="sub">{unique}</td>
	<td class="sub">{clicks}</td>
	<td class="sub">{unique}</td>
	<td class="sub">CR</td>
	<td class="sub">EPC</td>
	<td class="sub">Кол.</td>
	<td class="sub">Сумма</td>
	<td class="sub">Кол.</td>
	<td class="sub">Сумма</td>
	<td class="sub">Кол.</td>
	<td class="sub">Сумма</td>
</tr>
<tr>
	<th>{date}</th>
	<th colspan="2">{spaces}</th>
	<th colspan="2">Лэндинг</th>
	<th colspan="2">Конверсия</th>
	<th colspan="2">{accept}</th>
	<th colspan="2">{wait}</th>
	<th colspan="2">{cancel}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN stat -->
<tr>
	<td align="center">{stat.date}</td>
	<td align="center">{stat.spaces}</td>
	<td align="center">{stat.suni}</td>
	<td align="center">{stat.clicks}</td>
	<td align="center">{stat.unique}</td>
	<td align="center">{stat.cr}%</td>
	<td align="center">{stat.epc}</td>
	<td align="center"><a href="{stat.ua}">{stat.ca}</a></td>
	<td align="center">{stat.sa}</td>
	<td align="center"><a href="{stat.uw}">{stat.cw}</a></td>
	<td align="center">{stat.sw}</td>
	<td align="center"><a href="{stat.uc}">{stat.cc}</a></td>
	<td align="center">{stat.sc}</td>
</tr>
<!-- END stat -->
<!-- BEGIN nostat -->
<tr><td colspan="13" class="noitems">{nostats}</td></tr>
<!-- END nostat -->
</tbody>

</table>