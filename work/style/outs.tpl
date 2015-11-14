<div class="pages">{pages}</div>

<form action="{u_bulk}" method="post">

<table class="post-table" cellspacing="0">

<thead>
<tr>
	<th width="1%" class="cb"><input type="checkbox" class="allcb allselect user{user.id}" /></th>
	<th>{user}</th>
	<th>{cash}</th>
	<th>{time}</th>
	<th width="10%">{action}</th>
</tr>
</thead>

<tfoot>
<tr>
	<th width="1%" class="cb"><input type="checkbox" class="allcb allselect user{user.id}" /></th>
	<th>{user}</th>
	<th>{cash}</th>
	<th>{time}</th>
	<th width="10%">{action}</th>
</tr>
</tfoot>

<tbody>
<!-- BEGIN user -->
<tr class="dark">
	<td width="1%" class="cb"><input type="checkbox" class="allcb usersel user{user.id}" id="user{user.id}" /></td>
	<td>
		<a href="{user.uu}">{user.user}</a>
<!-- BEGIN bad -->
		<small class="red warn" title="Подозрительные заказы">{user.orders}</small>
<!-- END bad -->
	</td>
	<td align="center"><span class="rur">{user.value}</span></td>
	<td align="center" class="small">Выбрано: <span id="total{user.id}" class="rur usertotal">0</span></td>
	<td class="cb" align="center" nowrap="nowrap"><input type="submit" class="intable-button paybtn" onclick="return confirm('{confirma}');" value="{accept}" /><input type="submit" class="intable-button paybtn" onclick="return confirm('{confirmd}');" value="{cancel}" name="decline" /></td>
</tr>
<!-- BEGIN fin -->
<tr>
	<td width="1%" class="cb"><input type="checkbox" name="ids[]" id="fin{user.fin.id}" class="allcb fins user{user.id}" rel="total{user.id}" value="{user.fin.id}" /></td>
	<td>{user.fin.wmr}</td>
	<td align="center" nowrap="nowrap"><span class="rur" id="fin{user.fin.id}v">{user.fin.value}</span></td>
	<td class="small" align="center" nowrap="nowrap">{user.fin.time}</td>
	<td class="small" align="center" nowrap="nowrap">
		<a class="accept" href="{user.fin.accept}" onclick="return confirm('{confirma}');">{accept}</a>
		<a class="decline" href="{user.fin.decline}" onclick="return confirm('{confirmd}');">{cancel}</a>
	</td>
</tr>
<!-- END fin -->
<!-- END user -->
<!-- BEGIN nofin -->
<tr class="">
	<td colspan="5" align="center">{nofins}</td>
</tr>
<!-- END nofin -->
</tbody>

</table>

</form>

<script type="text/javascript">

	function recount () {    	$(".usertotal").text( '0' );
		$(".fins").each(function() {
			if ( $(this).prop( "checked" ) ) {
				var fid = '#' + $(this).attr( "id" ) + 'v';
				var uid = '#' + $(this).attr( "rel" );
				var smv = parseInt( $(fid).text() );
				var ttl = parseInt( $(uid).text() );
				ttl = ttl + smv;
				$(uid).text( ttl );
			}
		});
	}

	$(function() {
		$(".fins").change(function(){ recount(); });

		$(".allselect").change( function() {
			if( $(this).prop('checked') ) {
				$(".allcb").prop( 'checked', true );
			} else $(".allcb").prop( 'checked', false );
			recount();
		});

		$(".usersel").change( function() {			var ccc = "." + $(this).attr('id');
			if( $(this).prop('checked') ) {
				$(ccc).prop( 'checked', true );
			} else $(ccc).prop( 'checked', false );
			recount();
		});

	});


</script>