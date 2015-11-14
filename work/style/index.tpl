<!-- BEGIN pickitup -->
<a class="order-pickup" href="{u_pickup}">{pickup}</a>
<!-- END pickitup -->

<script type="text/javascript">
	function callform ( uid ) {		if (confirm("{call_confirm}")) {        	$("#call"+uid).submit();
		} else return false;
	}
</script>

<!-- BEGIN recall -->
<div class="entry">{recall.text}</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th width="1%">#</th>
	<th>{status}</th>
	<th colspan="2">{offer}</th>
	<th>{name} / {phone}</th>
	<th colspan="2">{address}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="1%">#</th>
	<th>{status}</th>
	<th colspan="2">{offer}</th>
	<th>{name} / {phone}</th>
	<th colspan="2">{address}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN ord -->
<tr>
	<td align="center" class="small">{recall.ord.id}</td>
	<td align="center" nowrap="nowrap"><span class="status status{recall.ord.stid}">{recall.ord.status}</span></td>
	<td colspan="2" align="center" nowrap="nowrap">{recall.ord.offer}</td>
	<td><a href="{recall.ord.edit}">{recall.ord.name}</a></td>
	<td align="center">{recall.ord.index}</td>
	<td>{recall.ord.addr}</td>
</tr>
<tr>
	<td align="center" nowrap="nowrap" class="cb"><a class="order-info" href="{recall.ord.edit}">{info}</a></td>
	<td align="center" nowrap="nowrap">{recall.ord.time}</td>
	<td align="center" nowrap="nowrap">{recall.ord.count}</td>
	<td align="center" nowrap="nowrap">{recall.ord.price}</td>
	<td align="center" nowrap="nowrap"><a href="{recall.ord.phone_call}" class="phone-{recall.ord.phone_ok} {recall.ord.phone_class}">+{recall.ord.phone}</a>  {recall.ord.calls}</td>
	<td colspan="2" class="cb">
		<form id="call{recall.ord.id}re" action="{recall.ord.action}" method="post">
			<select class="intable-select" name="status" onchange="callform('{recall.ord.id}re');">
				<option value="0">{call_default}</option>
				<option value="ok">{call_ok}</option>
				<optgroup label="{call_re}">
<!-- BEGIN re -->
					<option value="re{recall.ord.re.val}">{recall.ord.re.name}</option>
<!-- END re -->
				</optgroup>
				<optgroup label="{call_no}">
<!-- BEGIN no -->
					<option value="no{recall.ord.no.val}">{recall.ord.no.name}</option>
<!-- END no -->
				</optgroup>
				<optgroup label="{cancel}">
<!-- BEGIN cancel -->
					<option value="cancel{recall.ord.cancel.val}">{recall.ord.cancel.name}</option>
<!-- END cancel -->
				</optgroup>
			</select>
		</form>
	</td>
</tr>
<tr class="dark"><td class="cb" colspan="7"></td></tr>
<!-- END ord -->
</tbody>
</table>
<!-- END recall -->

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
	<!-- BEGIN status -->
				<option value="{status.value}" {status.select}>{status.name}</option>
	<!-- END status -->
				<optgroup label="Дополнительно">
					<option value="-1" {o_1}>Без отказов</option>
					<option value="-2" {o_2}>В обработке</option>
					<option value="-3" {o_3}>Принятые</option>
				</optgroup>
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
<!-- BEGIN all -->
		<label><input type="checkbox" name="a" value="1" {all.a} /> {showall}</label>
<!-- END all -->
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
		<input type="hidden" name="src" value="{src}" />
		<input type="submit" class="form-button" value="{find}" />
	</form>
	<div class="shown">
		<a class="excel" href="{u_csv}">CSV</a>
<!-- BEGIN couriers -->
		<a class="excel" href="{u_courier}">{courier}</a>
<!-- END couriers -->
	</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th width="1%">#</th>
	<th>{status}</th>
	<th colspan="3">{offer}</th>
	<th>{name} / {phone}</th>
	<th colspan="3">{address}</th>
	<th>{info}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="1%">#</th>
	<th>{status}</th>
	<th colspan="3">{offer}</th>
	<th>{name} / {phone}</th>
	<th colspan="3">{address}</th>
	<th>{info}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN ord -->
<tr>
	<td align="center" class="small" title="{ord.comment}">{ord.id}</td>
	<td align="center" nowrap="nowrap" class="cb"><a class="status status{ord.stid}" href="?f={ord.stid}">{ord.status}</a></td>
	<td colspan="3" align="center" nowrap="nowrap" class="cb"><a class="order-offer" href="?o={ord.oid}">{ord.offer}</a></td>
	<td><a href="{ord.edit}">{ord.name}</a></td>
	<td align="center" width="15"><a href="http://ipgeobase.ru/?address={ord.ip}" target="_blank"><img height="11" width="16" src="/data/flag/{ord.country}.png" alt="{ord.country}" title="{ord.ip} - {ord.country}" /></a></td>
	<td align="center">{ord.index}</td>
	<td>{ord.addr}</td>
<!-- BEGIN ip -->
	<td align="center"><span class="order-user">{ord.manager}</span></td>
<!-- END ip -->
<!-- BEGIN comp -->
	<td align="center"><a class="order-comp" href="?c={ord.comp.id}">{ord.comp.name}</a></td>
<!-- END comp -->
</tr>
<tr>
	<td align="center" nowrap="nowrap" class="cb"><a class="order-info" href="{ord.edit}">{info}</a></td>
	<td align="center" nowrap="nowrap" class="small cb">{ord.time}</td>
	<td align="center" nowrap="nowrap">{ord.count}</td>
	<td align="center" nowrap="nowrap">{ord.price}</td>
	<td class="cb" align="center" nowrap="nowrap"><abbr class="icon paid{ord.paid}" title="{ord.paidinfo}"></abbr></td>
	<td align="center" nowrap="nowrap"><a href="{ord.phone_call}" class="phone-{ord.phone_ok} {ord.phone_class}">+{ord.phone}</a> {ord.calls}</td>
	<td colspan="3" class="{ord.actcls}">
<!-- BEGIN pickup -->
<!-- BEGIN move -->
		<form id="call{ord.id}mv" action="{ord.pickup.move.u}" method="post">
			<select class="intable-select short" name="comp" onchange="callform('{ord.id}mv');">
				<option>-- перенести --</option>
<!-- BEGIN comp -->
				<option value="{ord.pickup.move.comp.val}">{ord.pickup.move.comp.name}</option>
<!-- END comp -->
			</select>
        </form>
<!-- END move -->
		<a class="order-info" href="{ord.pickup.u}" onclick="return confirm('{pick_confirm}');">{pickup}</a>
<!-- END pickup -->
<!-- BEGIN call -->
		<form id="call{ord.id}" action="{ord.call.action}" method="post">
			<select class="intable-select" name="status" onchange="callform({ord.id});">
				<option value="0">{call_default}</option>
				<option value="ok">{call_ok}</option>
				<optgroup label="{call_re}">
<!-- BEGIN re -->
					<option value="re{ord.call.re.val}">{ord.call.re.name}</option>
<!-- END re -->
				</optgroup>
				<optgroup label="{call_no}">
<!-- BEGIN no -->
					<option value="no{ord.call.no.val}">{ord.call.no.name}</option>
<!-- END no -->
				</optgroup>
				<optgroup label="{cancel}">
<!-- BEGIN cancel -->
					<option value="cancel{ord.call.cancel.val}">{ord.call.cancel.name}</option>
<!-- END cancel -->
				</optgroup>
			</select>
		</form>
<!-- END call -->
<!-- BEGIN cancel -->
		<span class="red">{ord.cancel.reason}</span>
<!-- END cancel -->
<!-- BEGIN pack -->
		<a class="accept" href="{ord.pack.done}" onclick="return confirm('{pack_confirm}')">{packed}</a>
		<span class="deliv deliv{ord.delivery}">{ord.delivern}</span>
<!-- BEGIN doc -->
		<a class="excel" href="{ord.pack.docs}">{packdocs}</a>
<!-- END doc -->
		<b>{ord.pack.items} </b>
<!-- BEGIN pres -->
		<b class="present">{ord.pack.present}</b>
<!-- END pres -->
<!-- END pack -->
<!-- BEGIN send -->
		<form class="inline" action="{ord.send.u}" method="post" onsubmit="return confirm('{track_confirm}');">
			<input type="text" class="intable-text" name="code" required="required" placeholder="{track_code}" /><input type="submit" class="intable-button" value="{track_send}" />
		</form>
		<span class="deliv deliv{ord.delivery}">{ord.delivern}</span>
<!-- END send -->
<!-- BEGIN esend -->
		<a class="accept" href="{ord.esend.u}" onclick="return confirm('{es_confirm}');">{esend}</a>
		<a class="{ord.esend.nc}" href="{ord.esend.nu}">{ord.esend.nt}</a>
<!-- END esend -->
<!-- BEGIN track -->
<!-- BEGIN confirm -->
		<a class="accept" href="{ord.track.confirm.u}" onclick="return confirm('{ord.track.confirm.c}');">{ord.track.confirm.t}?</a>
<!-- END confirm -->
		<span class="deliv deliv{ord.delivery}">{ord.delivern}</span>
		<a href="{ord.track.url}" title="{ord.track.check}" class="{ord.track.cls}" target="_blank">{ord.track.info}</a>
<!-- END track -->
	</td>
	<td align="center"><a class="order-source source-{ord.uclass}" href="?wm={ord.uid}">{ord.uname}</a></td>
</tr>
<tr class="dark"><td class="cb" colspan="10"></td></tr>
<!-- END ord -->
</tbody>

</table>

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>