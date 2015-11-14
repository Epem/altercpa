<div class="lister">
	<form class="lf" action="" method="get">
		<label title="{search}">
			<span class="icon search"></span>
			<input type="text" class="text" name="s" value="{s}" placeholder="Имя или email" />
		</label>
		<input type="submit" class="form-button" value="{find}" />
	</form>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead><tr>
	<th width="1%">#</th>
	<th>{name}</th>
	<th>{email}</th>
	<th width="1%" colspan="2">{vip}</th>
	<th>{info}</th>
	<th>FLW</th>
	<th>CR</th>
	<th>EPC</th>
	<th>IP</th>
	<th>Дата</th>
	<th>Реферал</th>
    <th width="10%">{action}</th>
</tr></thead>

<tfoot><tr>
	<th width="1%">#</th>
	<th>{name}</th>
	<th>{email}</th>
	<th width="1%" colspan="2">{vip}</th>
	<th>{info}</th>
	<th>FLW</th>
	<th>CR</th>
	<th>EPC</th>
	<th>IP</th>
	<th>Дата</th>
	<th>Реферал</th>
    <th width="10%">{action}</th>
</tr></tfoot>

<tbody>
<!-- BEGIN user -->
<tr>
	<td class="small" align="center">{user.id}</td>
	<td><a href="{user.url}">{user.name}</a></td>
	<td><a href="mailto:{user.mailto}">{user.email}</a></td>
	<td align="center" nowrap="nowrap">{user.vip}</td>
	<td align="center" nowrap="nowrap"><span class="icon {user.icon}"></span></td>
	<td align="center" nowrap="nowrap">{user.cash}</td>
	<td align="center" nowrap="nowrap" class="small"><span class="green" title="Активные потоки">{user.flwa}</span> / {user.flw}</td>
	<td align="center" nowrap="nowrap" class="small"><span class="{user.crc}">{user.cr}%</span></td>
	<td align="center" nowrap="nowrap" class="small">{user.epc}</td>
	<td align="center" nowrap="nowrap" class="small"><a href="http://ipgeobase.ru/?address={user.ip}" target="_blank">{user.ip}</a></td>
	<td align="center" nowrap="nowrap" class="small">{user.date}</td>
	<td align="center" nowrap="nowrap" class="small">{user.ref}</td>
    <td align="center" nowrap="nowrap" class="small">
		<a href="{user.enter}" class="pass">Вход</a>
		<a href="{user.url}" class="stats">Заказы</a>
		<a href="{user.edit}" class="edit">{edit}</a>
    </td>
</tr>
<!-- END user -->
</tbody>

</table>

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>