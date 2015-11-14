<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<label title="{search}">
			<span class="icon search"></span>
			<input type="text" class="text" name="s" value="{s}" placeholder="Имя, адрес, телефон" />
		</label>
		<label title="{status}">
			<span class="icon istatus"></span>
			<select name="f" class="text">
				<option value="">&mdash; {status} &mdash;</option>
				<option value="8" {f8}>В пути</option>
				<option value="9" {f9}>Доставлен</option>
			</select>
		</label>
		<label title="Служба">
			<span class="icon dlvr"></span>
			<select name="t" class="text">
				<option value="">&mdash; Служба &mdash;</option>
	<!-- BEGIN type -->
				<option value="{type.value}" {type.select}>{type.name}</option>
	<!-- END type -->
			</select>
		</label>
		<label title="{offer}">
			<span class="icon offer"></span>
			<select name="o" class="text">
				<option value="">&mdash; {offer} &mdash;</option>
	<!-- BEGIN offer -->
				<option value="{offer.value}" {offer.select}>{offer.name}</option>
	<!-- END offer -->
			</select>
		</label>
		<label title="{date}">
			<span class="icon cal"></span>
			<input type="date" class="text" name="d" value="{d}" />
		</label>
<!-- BEGIN comps -->
		<label title="{company}">
			<span class="icon comp"></span>
			<select name="c" class="text">
				<option value="">&mdash; {company} &mdash;</option>
	<!-- BEGIN c -->
				<option value="{comps.c.value}" {comps.c.select}>{comps.c.name}</option>
	<!-- END c -->
			</select>
	<!-- END comps -->
		</label>
		<input type="hidden" name="wm" value="{wm}" />
		<input type="hidden" name="md" value="{md}" />
		<input type="submit" class="form-button" value="{find}" />
	</form>
	<div class="shown"><a href="{u_mode}" class="{c_mode}">{mode}</a></div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{status}</th>
	<th colspan="2">{offer}</th>
	<th>Обзвон</th>
	<th>{name} / {phone}</th>
	<th colspan="2">{address}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th>{status}</th>
	<th colspan="2">{offer}</th>
	<th>Обзвон</th>
	<th>{name} / {phone}</th>
	<th colspan="2">{address}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN ord -->
<tr class="{ord.rowclass}">
	<td align="center" nowrap="nowrap"><span class="status status{ord.stid}">{ord.status}</span></td>
	<td align="center" nowrap="nowrap" class="small" colspan="2">{ord.offer}</td>
	<td class="cb">
		<form id="call{ord.id}" action="{ord.u_call}" method="post">
			<select class="intable-select" name="status" onchange="callform({ord.id});">
				<option value="0">--- результат звонка ---</option>
<!-- BEGIN action -->
				<option value="{ord.action.v}">{ord.action.n}</option>
<!-- END action -->
			</select>
		</form>
	</td>
	<td class="small"><a href="{ord.edit}">{ord.name}</a></td>
	<td colspan="2">{ord.addr}</td>
</tr>
<tr class="{ord.rowclass}">
	<td align="center" nowrap="nowrap" class="small">{ord.time}</td>
	<td align="center" nowrap="nowrap">{ord.count}</td>
	<td align="center" nowrap="nowrap">{ord.price}</td>
	<td align="center" class="cb xsmall">
		<span class="trackcalls trackcall{ord.result}">{ord.call}</span>
		<span class="{ord.cls}">{ord.called}</span>
	</td>
	<td align="center" nowrap="nowrap">
		<a href="{ord.phone_call}" class="phone-{ord.phone_ok} {ord.phone_class}">+{ord.phone}</a>
		<small title="Прозвонов по этому номеру" class="red">{ord.calls}</small>
	</td>
	<td>
		<a class="deliv deliv{ord.delivery}" href="#" onclick="return trackinfo('{ord.id}')">{ord.track_code}</a> <a href="{ord.track_url}" title="{ord.track_check}" class="{ord.track_cls}" target="_blank">{ord.track_info}</a>
	</td>
	<td nowrap="nowrap" align="center" class="small">
<!-- BEGIN confirm -->
		<a class="deliver" href="{ord.u_deliver}" onclick="return confirm('Вы уверены?');">Доставлен</a>
<!-- END confirm -->
		<a class="accept" href="{ord.u_confirm}" onclick="return confirm('Вы уверены?');">Оплачен</a>
		<a class="decline" href="{ord.u_return}" onclick="return confirm('Вы уверены?');">Возврат</a>
		<a class="order-source source-{ord.uclass}" href="?wm={ord.uid}">{ord.uname}</a>
	</td>
</tr>
<tr class="dark"><td class="cb" colspan="7" id="track{ord.id}"></td></tr>
<!-- END ord -->
</tbody>

</table>

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<script type="text/javascript">
	function trackinfo ( oid ) {		if ( $("#track"+oid).text() ) {
			$("#tracktable"+oid).toggle();
		} else $("#track"+oid).load("{u_trackinfo}"+oid);
		return false;
	}

	function callform ( uid ) {		if (confirm("Вы уверены, что хотите изменить статус данного заказа?")) {        	$("#call"+uid).submit();
		} else return false;
	}

</script>
