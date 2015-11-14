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
		<label title="{comp}">
			<span class="icon comp"></span>
			<select name="c" class="text">
				<option value="">&mdash; {comp} &mdash;</option>
<!-- BEGIN comp -->
				<option value="{comp.value}" {comp.select}>{comp.name}</option>
<!-- END comp -->
			</select>
		</label>
		<input type="submit" class="form-button" value="{show}" />
	</form>
	<div class="shown">
		<a class="stats" href="{u_all}">{all}</a>
		<a class="date" href="{u_today}">{today}</a>
		<a class="date" href="{u_yest}">{yest}</a>
		<a class="date" href="{u_day7}">{day7}</a>
		<a class="date" href="{u_day30}">{day30}</a>
	</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th colspan="2">{name}</th>
	<th colspan="6">{wait}</th>
	<th colspan="11">{cancel}</th>
	<th colspan="5">{accept}</th>
	<th colspan="2">Доставка</th>
</tr>
<tr>
	<td class="sub">Кто</td>
	<td class="sub">Кол.</td>
	<td class="sub" colspan="2">Кол.</td>
	<td class="sub"><abbr title="{st1}">Нов</abbr></td>
	<td class="sub"><abbr title="{st2}">Обр</abbr></td>
	<td class="sub"><abbr title="{st3}">Пер</abbr></td>
	<td class="sub"><abbr title="{st4}">Нед</abbr></td>
	<td class="sub" colspan="2">{st5}</td>
	<td class="sub"><abbr title="{rs1}">{rm1}</abbr></td>
	<td class="sub"><abbr title="{rs2}">{rm2}</abbr></td>
	<td class="sub"><abbr title="{rs3}">{rm3}</abbr></td>
	<td class="sub"><abbr title="{rs4}">{rm4}</abbr></td>
	<td class="sub"><abbr title="{rs5}">{rm5}</abbr></td>
	<td class="sub"><abbr title="{rs6}">{rm6}</abbr></td>
	<td class="sub"><abbr title="{rs7}">{rm7}</abbr></td>
	<td class="sub"><abbr title="{rs8}">{rm8}</abbr></td>
	<td class="sub"><abbr title="{st12}">X</abbr></td>
	<td class="sub" colspan="2">Кол.</td>
	<td class="sub">{income}</td>
	<td class="sub">{outcome}</td>
	<td class="sub">{total}</td>
	<td class="sub">Почта</td>
	<td class="sub">СПСР</td>
</tr>
</thead>

<tfoot>
<tr>
	<td class="sub">Кто</td>
	<td class="sub">Кол.</td>
	<td class="sub" colspan="2">Кол.</td>
	<td class="sub"><abbr title="{st1}">Нов</abbr></td>
	<td class="sub"><abbr title="{st2}">Обр</abbr></td>
	<td class="sub"><abbr title="{st3}">Пер</abbr></td>
	<td class="sub"><abbr title="{st4}">Нед</abbr></td>
	<td class="sub" colspan="2">{st5}</td>
	<td class="sub"><abbr title="{rs1}">{rm1}</abbr></td>
	<td class="sub"><abbr title="{rs2}">{rm2}</abbr></td>
	<td class="sub"><abbr title="{rs3}">{rm3}</abbr></td>
	<td class="sub"><abbr title="{rs4}">{rm4}</abbr></td>
	<td class="sub"><abbr title="{rs5}">{rm5}</abbr></td>
	<td class="sub"><abbr title="{rs6}">{rm6}</abbr></td>
	<td class="sub"><abbr title="{rs7}">{rm7}</abbr></td>
	<td class="sub"><abbr title="{rs8}">{rm8}</abbr></td>
	<td class="sub"><abbr title="{st12}">X</abbr></td>
	<td class="sub" colspan="2">Кол.</td>
	<td class="sub">{income}</td>
	<td class="sub">{outcome}</td>
	<td class="sub">{total}</td>
	<td class="sub">Почта</td>
	<td class="sub">СПСР</td>
</tr>
<tr>
	<th colspan="2">{name}</th>
	<th colspan="6">{wait}</th>
	<th colspan="11">{cancel}</th>
	<th colspan="5">{accept}</th>
	<th colspan="2">Доставка</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN bl -->
<!-- BEGIN t -->
<tr><td class="head" colspan="27">{bl.t.name}</td></tr>
<!-- END t -->
<!-- BEGIN row -->
<tr>
	<td>{bl.row.ext} {bl.row.name} {bl.row.vip}</td>
	<td align="center" class="small">{bl.row.tt}</td>
	<td align="center"><b class="blue">{bl.row.st0}</b></td>
	<td align="center" class="small">{bl.row.pr0}%</td>
	<td align="center">{bl.row.st1}</td>
	<td align="center">{bl.row.st2}</td>
	<td align="center">{bl.row.st3}</td>
	<td align="center">{bl.row.st4}</td>
	<td align="center"><b class="red">{bl.row.st5}</b></td>
	<td align="center" class="small">{bl.row.pr5}%</td>
	<td align="center">{bl.row.dc1}</td>
	<td align="center">{bl.row.dc2}</td>
	<td align="center">{bl.row.dc3}</td>
	<td align="center">{bl.row.dc4}</td>
	<td align="center">{bl.row.dc5}</td>
	<td align="center">{bl.row.dc6}</td>
	<td align="center">{bl.row.dc7}</td>
	<td align="center">{bl.row.dc8}</td>
	<td align="center"><b class="yellow">{bl.row.st12}</b></td>
	<td align="center"><b class="green">{bl.row.st6}</b></td>
	<td align="center" class="small">{bl.row.pr6}%</td>
	<td align="center" class="small"><span class="rur green">{bl.row.mi}</span></td>
	<td align="center" class="small"><span class="rur red">{bl.row.mo}</span></td>
	<td align="center" class="small"><span class="rur blue">{bl.row.mt}</span></td>
	<td align="center" class="small"><abbr title="В пути: {bl.row.pr91}%" class="blue">{bl.row.st91}</abbr> / <abbr title="Получены: {bl.row.pr101}%" class="green">{bl.row.st101}</abbr> / <abbr title="Возвраты: {bl.row.pr111}%" class="red">{bl.row.st111}</abbr></td>
	<td align="center" class="small"><abbr title="В пути: {bl.row.pr92}%" class="blue">{bl.row.st92}</abbr> / <abbr title="Получены: {bl.row.pr102}%" class="green">{bl.row.st102}</abbr> / <abbr title="Возвраты: {bl.row.pr112}%" class="red">{bl.row.st112}</abbr></td>
</tr>
<!-- END row -->
<!-- END bl -->
</tbody>

</table>