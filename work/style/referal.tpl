<div class="entry">{text}</div>

<table class="post-table" cellspacing="0">

<thead><tr>
	<th>{name}</th>
	<th width="15%">Потоки</th>
	<th width="15%">Заработок</th>
</tr></thead>

<tfoot><tr>
	<th>{name}</th>
	<th width="15%">Потоки</th>
	<th width="15%">Заработок</th>
</tr></tfoot>

<tbody>
<!-- BEGIN user -->
<tr>
	<td>{user.name}</td>
	<td align="center">{user.flwa}</td>
	<td align="center">{user.cash}</td>
</tr>
<!-- END user -->
<!-- BEGIN nouser -->
<tr><td colspan="3" class="noitems">{nousers}</td></tr>
<!-- END nouser -->
</tbody>

</table>

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>