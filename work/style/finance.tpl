<div class="entry">{text}</div>

<table cellspacing="0" cellpadding="5"><tr>
<!-- BEGIN canin -->
	<form action="https://merchant.webmoney.ru/lmi/payment.asp" method="post">
		<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="{pay_comment}">
		<input type="hidden" name="LMI_PAYMENT_NO" value="{pay_id}">
		<input type="hidden" name="LMI_PAYEE_PURSE" value="{pay_purse}">
		<input type="hidden" name="LMI_SIM_MODE" value="0">
		<td align="right" nowrap="nowrap"><b>{pay}</b></td>
		<td align="center"><input name="LMI_PAYMENT_AMOUNT" class="form-text" type="text" value="{toadd}" required="required" pattern="[0-9]+" /></td>
		<td align="center"><input class="form-button mini" type="submit" /></td>
	</form>
<!-- END canin -->
	<form action="{u_out}" method="post">
		<td align="right" nowrap="nowrap"><b>{out}</b></td>
		<td align="center"><input class="form-text" type="text" name="cash" value="{toout}" required="required" pattern="[0-9]+" placeholder="Минимально: 2000 руб" /></td>
		<td align="center"><input class="form-button mini" type="submit" /></td>
	</form>
</tr></table>

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th>{type}</th>
	<th width="10%">{cash}</th>
	<th width="20%">{date}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th>{type}</th>
	<th width="10%">{cash}</th>
	<th width="20%">{date}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN fin -->
<tr>
	<td><span class="fintype{fin.tid}">{fin.type} {fin.descr}</span></td>
	<td align="center" nowrap="nowrap">{fin.value}</td>
	<td class="small" align="center" nowrap="nowrap">
		{fin.time}
<!-- BEGIN action -->
		<a class="decline" href="{fin.cancel}" onclick="return confirm('{confirm}');">{cancel}</a>
<!-- END action -->
	</td>
</tr>
<!-- END fin -->
<!-- BEGIN nofin -->
<tr class="">
	<td colspan="5" align="center">{nofins}</td>
</tr>
<!-- END nofin -->
</tbody>

</table>

<div class="pages">{pages}</div>