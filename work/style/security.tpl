<div class="entry">{text}</div>

<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<label title="{search}">
			<span class="icon search"></span>
			<input type="text" class="text" name="s" value="{s}" placeholder="Имя, адрес, телефон" />
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
		<input type="submit" class="form-button" value="{find}" />
	</form>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{status}</th>
	<th colspan="2">{offer}</th>
	<th>{name} / {phone}</th>
	<th colspan="2">{address}</th>
	<th>Источник</th>
</tr>
</thead>

<tfoot>
<tr>
	<th>{status}</th>
	<th colspan="2">{offer}</th>
	<th>{name} / {phone}</th>
	<th colspan="2">{address}</th>
	<th>Источник</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN ord -->
<tr class="{ord.rowclass}">
	<td align="center" nowrap="nowrap"><span class="status status{ord.stid}">{ord.status}</span></td>
	<td align="center" nowrap="nowrap" class="small" colspan="2">{ord.offer}</td>
	<td class="small"><a href="{ord.edit}">{ord.name}</a></td>
	<td colspan="2">{ord.addr}</td>
	<td align="center"><a class="order-source source-{ord.uclass}" href="?wm={ord.uid}">{ord.uname}</a></td>
</tr>
<tr class="{ord.rowclass}">
	<td align="center" nowrap="nowrap">{ord.time}</td>
	<td align="center" nowrap="nowrap">{ord.count}</td>
	<td align="center" nowrap="nowrap">{ord.price}</td>
	<td align="center" nowrap="nowrap"><a href="{ord.phone_call}" class="phone-{ord.phone_ok} {ord.phone_class}">+{ord.phone}</a></td>
	<td>
		<a class="deliv deliv{ord.delivery}" href="#" onclick="return trackinfo('{ord.id}')">{ord.delivern}</a> <a href="{ord.track_url}" title="{ord.track_check}" class="{ord.track_cls}" target="_blank">{ord.track_info}</a>
	</td>
	<td nowrap="nowrap" align="center" class="small">
		<span class="exti">{ord.id}</span>
		<a class="accept" href="{ord.u_uncheck}" onclick="return confirm('Вы уверены?');">Снять с контроля</a>
		<a class="decline" href="{ord.u_reset}" onclick="return confirm('Вы уверены?');">Отменить заказ</a>
	</td>
	<td align="center" class="small"><span class="newflow">{ord.src}</span></td>
</tr>
<tr class="dark"><td class="cb" colspan="7" id="track{ord.id}"></td></tr>
<!-- END ord -->
<!-- BEGIN nord -->
<tr><td colspan="7" class="noitems">Нет заказов на контроле службы безопасности.</td></tr>
<!-- END nord -->
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
</script>
