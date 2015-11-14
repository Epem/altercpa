<h2>{title}</h2>
{text}

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table hl" cellspacing="0">

<thead>
<tr>
	<th width="1%">#</th>
	<th width="74%">{name}</th>
	<th width="15%">{info}</th>
    <th width="20%">{action}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="1%">#</th>
	<th width="74%">{name}</th>
	<th width="15%">{info}</th>
    <th width="20%">{action}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN item -->
<tr>
	<td align="right">{item.id}</td>
	<td><a href="{item.url}">{item.name}</a> {item.more}</td>
	<td align="center" nowrap="nowrap">{item.info}</td>
    <td align="center" nowrap="nowrap">
		<a href="{item.edit}" class="edit">{edit}</a>
	    <a href="{item.del}" class="delete" onclick="return confirm('{confirm}');">{del}</a>
    </td>
</tr>
<!-- END item -->
</tbody>

</table>

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>