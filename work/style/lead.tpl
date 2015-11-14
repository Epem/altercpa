<div class="entry">{text}</div>

<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<span class="icon cal"></span>
		<input title="Дата" type="date" class="text" name="d" value="{d}" />
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
		<label title="{site}">
			<span class="icon site"></span>
			<select name="w" class="text">
				<option value="">&mdash; {site} &mdash;</option>
<!-- BEGIN site -->
				<option value="{site.value}" {site.select}>{site.name}</option>
<!-- END site -->
			</select>
		</label>
		<label title="{status}">
			<span class="icon istatus"></span>
			<select name="s" class="text">
				<option value="">&mdash; {status} &mdash;</option>
<!-- BEGIN status -->
				<option value="{status.value}" {status.select}>{status.name}</option>
<!-- END status -->
			</select>
		</label>
		<input type="submit" class="form-button" value="{show}" />
	</form>
<!-- BEGIN help -->
	<div class="shown"><a href="/help/#lead" class="help">Помощь</a></div>
<!-- END help -->
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{date}</th>
	<th>{offer}</th>
	<th>{flow}</th>
	<th>{site}</th>
	<th>{space}</th>
	<th colspan="2" width="5%">IP</th>
	<th>{status}</th>
	<th>{calls}</th>
	<th>{reason}</th>
	<th colspan="2">Тизер</th>
	<th>Площадка</th>
</tr>
</thead>

<tfoot>
<tr>
	<th>{date}</th>
	<th>{offer}</th>
	<th>{flow}</th>
	<th>{site}</th>
	<th>{space}</th>
	<th colspan="2" width="5%">IP</th>
	<th>{status}</th>
	<th>{calls}</th>
	<th>{reason}</th>
	<th colspan="2">Тизер</th>
	<th>Площадка</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN order -->
<tr>
	<td align="center" nowrap="nowrap" class="small">{order.time}</td>
	<td align="center" nowrap="nowrap">{order.offer}</td>
	<td align="center">{order.flow}</td>
	<td align="center"><a href="http://{order.site}/" target="_blank">{order.site}</a></td>
	<td align="center" class="small"><a href="http://{order.space}/" target="_blank">{order.space}</a></td>
	<td align="center" class="small"><a href="https://www.reg.ru/whois/?dname={order.ip}">{order.ip}</a></td>
	<td align="center" class="cb"><img src="/data/flag/{order.country}.png" alt="{order.country}" title="{order.country}" /></td>
	<td align="center"><span class="status status{order.stid}">{order.status}</span></td>
	<td align="center">{order.calls}</td>
	<td align="center" class="small"><span class="red">{order.reason}</span></td>
	<td align="center" class="small">{order.utm_id}</td>
	<td align="center" class="small">{order.utm_cn}</td>
	<td align="center" class="small">{order.utm_src}</td>
</tr>
<!-- END order -->
<!-- BEGIN nostat -->
<tr><td colspan="13" class="noitems">{nostats}</td></tr>
<!-- END nostat -->
</tbody>

</table>


<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>