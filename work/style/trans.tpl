<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<label title="{search}">
			<span class="icon search"></span>
			<input type="text" class="text" name="s" value="{s}" placeholder="Комментарий ..." />
		</label>
		<label title="{date}">
			<span class="icon cal"></span>
			<input type="date" class="text" name="d" value="{d}" />
		</label>
		<label title="{type}">
			<span class="icon mny"></span>
			<select name="t" class="text">
				<option value="">&mdash; {type} &mdash;</option>
	<!-- BEGIN type -->
				<option value="{type.value}" {type.select}>{type.name}</option>
	<!-- END type -->
			</select>
		</label>
	<!-- BEGIN user -->
		{user}: <a class="unfilter" href="{reset}">{u}</a>
		<input type="hidden" name="f" value="{f}" />
	<!-- END user -->
		<input type="submit" class="form-button" value="{find}" />
	</form>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{user}</th>
	<th>{type}</th>
	<th>{cash}</th>
	<th>{time}</th>
	<th>{action}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th>{user}</th>
	<th>{type}</th>
	<th>{cash}</th>
	<th>{time}</th>
	<th>{action}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN fin -->
<tr>
	<td><a href="{fin.uu}">{fin.user}</a></td>
	<td><span class="fintype{fin.tid}">{fin.type} {fin.descr}</span></td>
	<td align="center" nowrap="nowrap">{fin.value}</td>
	<td class="small" align="center" nowrap="nowrap">{fin.time}</td>
	<td class="small" align="center" nowrap="nowrap">
		<a href="{fin.del}" class="delete" onclick="return confirm('{confirm}');">{del}</a>
	</td>
</tr>
<!-- END fin -->
<!-- BEGIN nofin -->
<tr class="">
	<td colspan="6" align="center">{nofins}</td>
</tr>
<!-- END nofin -->
</tbody>

</table>

<div class="pages">{pages}</div>