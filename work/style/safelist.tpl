<h2>{title}</h2>
{text}

<script type="text/javascript">
function safedel ( url ) {	if ( prompt('{confirm}') == 'delete' ) {   		location.href = url;
   		return true;
	} else return false;
}
</script>

<div class="lister">
	<div class="shown">{shown}</div>
	<div class="pages">{pages}</div>
	<div class="clear"></div>
</div>

<table class="post-table hl" cellspacing="0">

<thead>
<tr>
	<th width="1%">#</th>
	<th width="49%">{name}</th>
	<th width="30%">{info}</th>
    <th width="20%">{action}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="1%">#</th>
	<th width="49%">{name}</th>
	<th width="30%">{info}</th>
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
	    <a href="#" class="delete" onclick="safedel('{item.del}');">{del}</a>
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