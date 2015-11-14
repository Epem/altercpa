<!-- control module : start -->

<h2>{title}</h2>
{text}

<div class="pages">{pages}</div>

<table class="post-table">

<thead><tr>
	<th width="20%">{name}</th>
	<th width="15%">{status}</th>
	<th width="15%">{user}</th>
	<th width="15%">{time}</th>
</tr></thead>

<tfoot><tr>
	<th width="20%">{name}</th>
	<th width="15%">{status}</th>
	<th width="15%">{user}</th>
	<th width="15%">{time}</th>
</tr></tfoot>

<tbody>
<!-- BEGIN supp -->
	<tr>
		<td><a href="{supp.link}">{supp.name}</a></td>
		<td align="center"><span class="suppst-{supp.sclass}">{supp.status}</span></td>
		<td align="center"><span class="{supp.uclass}"><a href="{supp.link}">{supp.user}</a></span></td>
		<td align="center">{supp.time}</td>
	</tr>
<!-- END site -->
</tbody>

</table>

<div class="pages">{pages}</div>

<!-- control module : end -->