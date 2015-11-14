<h2>{title}</h2>
<form action="{u_save}" method="post">

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th width="40%">Тип цены</th>
	<th width="25%" colspan="2">Вебмастер</th>
	<th width="25%" colspan="2">Рекламодатель</th>
	<th width="10%">Партнёр</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="40%">Тип цены</th>
	<th width="25%" colspan="2">Вебмастер</th>
	<th width="25%" colspan="2">Рекламодатель</th>
	<th width="10%">Партнёр</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN type -->
<tr>
	<td class="sub" width="40%">{type.name}</td>
	<td class="sub" width="15%">Заказ</td>
	<td class="sub" width="10%">Апсейл</td>
	<td class="sub" width="15%">Заказ</td>
	<td class="sub" width="10%">Апсейл</td>
	<td class="sub" width="10%">Отчисления</td>
</tr>
<!-- BEGIN price -->
<tr>
	<td class="cb small">{type.price.name}</td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="{type.price.wmn}" value="{type.price.wmv}" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="{type.price.wmun}" value="{type.price.wmuv}" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="{type.price.payn}" value="{type.price.payv}" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="{type.price.pyun}" value="{type.price.pyuv}" class="intable-view" /></td>
	<td class="fld {type.price.cls}" align="center" nowrap="nowrap">
<!-- BEGIN ref -->
		<input type="text" name="{type.price.refn}" value="{type.price.refv}" class="intable-view" />
<!-- END ref -->
	</td>
</tr>
<!-- END price -->
<!-- END type -->
</tbody>

</table>

<div class="form-buttons"><input type="submit" class="form-submit" value="{save}" /></div>

</form>