<a class="edit rf" href="{u_edit}">{edit}</a>
<h2>{comp_name}</h2>

<table class="post-table">
	<tr><td class="head">{fio}</td><td>{comp_fio}</td></tr>
	<tr><td class="head">{phone}</td><td>{comp_phone}</td></tr>
	<tr><td class="head">{bank}</td><td>{comp_bank}, {bik}: {comp_bik}, {acc}: {comp_acc}, {ks}: {comp_ks}, {inn}: {comp_inn}</td></tr>
	<tr><td class="head">{addr}</td><td>{comp_index}, {comp_addr}</td></tr>
</table>

<div class="rf">
	<a class="store-in" href="{u_income}">{income}</a>
	<a class="store-edit" href="{u_store}">{sedit}</a>
</div>
<h2>{store}</h2>

<table class="post-table">
	<thead><tr>
		<th width="60%">{name}</th>
		<th width="25%">{price}</th>
		<th width="15%">{count}</th>
	</tr></thead>
	<tfoot><tr>
		<th width="60%">{name}</th>
		<th width="25%">{price}</th>
		<th width="15%">{count}</th>
	</tr></tfoot>
	<tbody>
<!-- BEGIN store -->
		<tr>
			<td>{store.name}</td>
			<td align="center"><span class="rur">{store.price}</span></td>
			<td align="center">{store.count}</td>
		</tr>
<!-- BEGIN var -->
		<tr class="dark">
			<td>&mdash; {store.var.name}</td>
			<td align="center"><span class="rur">{store.var.price}</span></td>
			<td align="center">{store.var.count}</td>
		</tr>
<!-- END var -->
<!-- END store -->
	</tbody>
</table>

<a class="update rf" href="{u_update}">{update}</a>
<h2>{users}</h2>
<table class="post-table">
	<thead><tr>
		<th>{fio}</th>
		<th>{email}</th>
		<th colspan="3">{process}</th>
		<th>{cancel}</th>
		<th>{wait}</th>
		<th>{pack}</th>
		<th>{send}</th>
		<th>{done}</th>
		<th>{level}</th>
		<th width="10%">{action}</th>
	</tr></thead>
	<tfoot><tr>
		<th>{fio}</th>
		<th>{email}</th>
		<th colspan="3">{process}</th>
		<th>{cancel}</th>
		<th>{wait}</th>
		<th>{pack}</th>
		<th>{send}</th>
		<th>{done}</th>
		<th>{level}</th>
		<th width="10%">{action}</th>
	</tr></tfoot>
	<tbody>
<!-- BEGIN user -->
		<tr>
			<td><a href="{user.edit}">{user.name}</a></td>
			<td align="center" class="small"><a href="mailto:{user.email}">{user.email}</a></td>
			<td align="center" nowrap="nowrap"><abbr title="{today}">{user.today}</abbr></td>
			<td align="center" nowrap="nowrap"><abbr title="{yest}">{user.yest}</abbr></td>
			<td align="center" nowrap="nowrap"><abbr title="{total}">{user.total}</abbr></td>
			<td align="center" nowrap="nowrap"><span class="red">{user.cancel}</span></td>
			<td align="center" nowrap="nowrap"><span class="grey">{user.wait}</span></td>
			<td align="center" nowrap="nowrap"><span class="yellow">{user.pack}</span></td>
			<td align="center" nowrap="nowrap"><span class="blue">{user.send}</span></td>
			<td align="center" nowrap="nowrap"><span class="green">{user.done}</span></td>
			<td align="center" nowrap="nowrap" class="small">{user.level}</td>
			<td align="center" nowrap="nowrap">
				<a href="{user.edit}" class="edit">{edit}</a>
			    <a href="{user.del}" class="delete" onclick="return confirm('{confirm}');">{del}</a>
			</td>
		</tr>
<!-- END user -->
	</tbody>
</table>