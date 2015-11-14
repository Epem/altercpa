<div class="dates">
	<b>{years}</b>:
<!-- BEGIN year -->
	<a href="{year.url}" class="{year.class}">{year.text}</a>
<!-- END year -->
	<b>{months}</b>:
<!-- BEGIN month -->
	<a href="{month.url}" class="{month.class}">{month.text}</a>
<!-- END month -->
</div>

<table class="post-table">
	<thead>
		<tr>
			<th width="70%">{cat}</th>
			<th width="15%">{summ}</th>
			<th width="15%">{balance}</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td class="head" align="right" colspan="2">{total}</td>
			<td class="head" align="center">{m_balance}</td>
		</tr>
		<tr>
			<th width="70%">{cat}</th>
			<th width="15%">{summ}</th>
			<th width="15%">{balance}</th>
		</tr>
	</tfoot>
	<tbody>
<!-- BEGIN cash -->
		<tr>
			<td><span class="fintype{cash.id}">{cash.name}</span></td>
			<td align="center">{cash.summ}</td>
			<td align="center">{cash.balance}</td>
		</tr>
<!-- END cash -->
	</tbody>
</table>

<h3>{debt}</h3>
<table class="post-table">
	<thead>
		<tr><th width="80%">{user}</th><th width="15%">{summ}</th></tr>
	</thead>
	<tfoot>
<!-- BEGIN dt -->
		<tr>
			<td class="head" align="right">{total}</td>
			<td class="head" align="center">{d_balance}</td>
		</tr>
<!-- END dt -->
		<tr><th width="80%">{user}</th><th width="15%">{summ}</th></tr>
	</tfoot>
	<tbody>
<!-- BEGIN debt -->
		<tr><td>{debt.name}</td><td align="center">{debt.summ}</td></tr>
<!-- END debt -->
<!-- BEGIN nodebt -->
		<tr><td align="center" colspan="2">{nodebts}</td></tr>
<!-- END nodebt -->
	</tbody>
</table>

<h3>{cred}</h3>
<table class="post-table">
	<thead><tr><th width="80%">{user}</th><th width="15%">{summ}</th></tr></thead>
	<tfoot><tr><th width="80%">{user}</th><th width="15%">{summ}</th></tr></tfoot>
	<tbody>
<!-- BEGIN ext -->
		<tr><td>{ext.name}</td><td align="center">{ext.summ}</td></tr>
<!-- END ext -->
<!-- BEGIN noext -->
		<tr><td  align="center" colspan="2">У нас нет долгов перед агентствами</td></tr>
<!-- END noext -->
<!-- BEGIN et -->
		<tr>
			<td class="head" align="right">Общая сумма долга перед агентствами</td>
			<td class="head" align="center">{c_ext}</td>
		</tr>
<!-- END et -->
<!-- BEGIN cred -->
		<tr><td>{cred.name}</td><td align="center">{cred.summ}</td></tr>
<!-- END cred -->
<!-- BEGIN nocred -->
		<tr><td  align="center" colspan="2">{nocreds}</td></tr>
<!-- END nocred -->
<!-- BEGIN ct -->
		<tr>
			<td class="head" align="right">{cred_balance}</td>
			<td class="head" align="center">{c_balance}</td>
		</tr>
<!-- END ct -->
<!-- BEGIN morecred -->
		<tr>
			<td align="right">{cred_wait}</td>
			<td align="center">{c_wait}</td>
		</tr>
		<tr>
			<td class="head" align="right">{total}</td>
			<td class="head" align="center">{c_total}</td>
		</tr>
<!-- END morecred -->
	</tbody>
</table>