<div class="entry">{text}</div>

<div class="lister">
	<form class="lf" action="{u_add}" method="post">
		<label title="Добавить новый домен">
			<span class="icon plus"></span>
			<input type="text" class="text" name="url" placeholder="Адрес ..." />
		</label>
		<input type="submit" class="form-button" value="Добавить новый домен" />
	</form>
	<div class="clear"></div>
</div>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th width="80%">{url}</th>
	<th width="20%">{action}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="80%">{url}</th>
	<th width="20%">{action}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN domain -->
<tr>
	<td>{domain.url}</td>
	<td align="center" nowrap="nowrap">
		<a href="{domain.check}" class="browse">{check}</a>
		<a href="{domain.del}" class="delete" onclick="return confirm('{confirm}');">{del}</a>
	</td>
</tr>
<!-- END domain -->
<!-- BEGIN nodoms -->
<tr class="">
	<td colspan="2" class="noitems">{nodomain}</td>
</tr>
<!-- END nodoms -->
</tbody>

</table>