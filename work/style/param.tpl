<h2>{title}</h2>
<form action="{u_save}" method="post">

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th width="70%">Название</th>
	<th width="30%">Значение</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="70%">Название</th>
	<th width="30%">Значение</th>
</tr>
</tfoot>

<tbody>
<tr><td class="head" colspan="2">Основные параметры</td></tr>
<!-- BEGIN param -->
<tr>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="param[{param.id}]" value="{param.name}" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="value[{param.id}]" value="{param.val}" class="intable-view" /></td>
</tr>
<!-- END param -->
<tr>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="param[101]" value="" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="value[101]" value="" class="intable-view" /></td>
</tr>
<tr>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="param[102]" value="" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="value[102]" value="" class="intable-view" /></td>
</tr>
<tr>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="param[103]" value="" class="intable-view" /></td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="value[103]" value="" class="intable-view" /></td>
</tr>
<tr><td class="head" colspan="2">Настройки шейва</td></tr>
<tr>
	<td class="cb" nowrap="nowrap">
		Общий шейв для всех компаний
		<input type="hidden" name="param[110]" value="shave" />
	</td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="value[110]" value="{shave}" class="intable-view" /></td>
</tr>
<!-- BEGIN shave -->
<tr>
	<td class="cb" nowrap="nowrap">
		Шейв компании «{shave.name}»
		<input type="hidden" name="param[{shave.id}]" value="{shave.param}" />
	</td>
	<td class="fld" align="center" nowrap="nowrap"><input type="text" name="value[{shave.id}]" value="{shave.val}" class="intable-view" /></td>
</tr>
<!-- END shave -->
</tbody>

</table>

<div class="form-buttons"><input type="submit" class="form-submit" value="{save}" /></div>

</form>