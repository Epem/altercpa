<div class="rf"><span class="status status{order_status}">{status}</span> - {date}</div>
<h2>{order} {order_id} - {offer_name}</h2>

<form action="{u_edit}" method="post" id="order" name="order">
<input type="hidden" name="r" value="{r}" />

<table class="post-table">

<thead><tr>
	<th>{name}</th>
	<th width="11%">{store}</th>
	<th width="11%">{price}</th>
	<th width="11%">{count}</th>
	<th width="11%">{total}</th>
</tr></thead>

<tbody>
<!-- BEGIN item -->
	<tr>
		<td>{item.name}</td>
		<td align="center">{item.store}</td>
		<td align="center"><span id="priceitem{item.id}" class="rur">{item.price}</span></td>
<!-- BEGIN view -->
		<td align="center">{item.count}</td>
<!-- END view -->
<!-- BEGIN edit -->
		<td class="cb" align="center"><input class="intable-count items" type="number" min="0" size="4" name="counts[{item.id}]" id="item{item.id}" value="{item.count}" pattern="^[0-9]+$" onchange="recount()" /></td>
<!-- END edit -->
		<td align="center"><span id="totalitem{item.id}" class="rur">{item.total}</span></td>
	</tr>
<!-- END item -->
	<tr><td colspan="5" class="head">{discount}</td></tr>
<!-- BEGIN dcview -->
	<tr>
		<td colspan="2">{dcview.name}</td>
		<td align="center">{dcview.price}%</td>
		<td align="center">{order_count}</td>
		<td align="center"><span class="rur">{dcview.total}</span></td>
	</tr>
<!-- END dcview -->
<!-- BEGIN dcedit -->
	<tr>
		<td colspan="2"><input class="discounts" type="radio" name="discount" required="required" value="{dcedit.id}" {dcedit.check} onchange="recount();" /> {dcedit.name}</td>
		<td align="center"><span id="discount{dcedit.id}">{dcedit.id}</span>%</td>
		<td align="center"><span class="totalcounts">{order_count}</span></td>
		<td align="center"><span class="rur" id="dctotal{dcedit.id}">{dcedit.total}</span></td>
	</tr>
<!-- END dcedit -->
<!-- BEGIN prview -->
	<tr>
		<td><b class="present">{present}</b> {prview.name}</td>
		<td colspan="3">{order_comment}</td>
		<td align="center"><span class="rur">{prview.price}</span></td>
	</tr>
<!-- END prview -->
<!-- BEGIN predit -->
	<tr>
		<td class="cb" align="center" colspan="4"><select class="intable-select" id="present" name="present" onchange="recount();">
<!-- BEGIN row -->
			<option value="{predit.row.id}" id="present{predit.row.id}" rel="{predit.row.price}" {predit.row.check}>{predit.row.name}</option>
<!-- END row -->
		</select></td>
		<td align="center"><span class="rur" id="prtotal">{predit.price}</span></td>
	</tr>
<!-- END predit -->
<!-- BEGIN delivery -->
	<tr><td colspan="5" class="head">{delivery}</td></tr>
<!-- BEGIN view -->
	<tr>
		<td colspan="2">{delivery.view.name}</td>
		<td align="center"><span class="rur">{delivery.view.price}</span></td>
		<td align="center">{order_count}</td>
		<td align="center"><span class="rur">{delivery.view.total}</span></td>
	</tr>
<!-- END view -->
<!-- BEGIN edit -->
	<tr>
		<td colspan="2"><input class="deliveries" type="radio" name="delivery" required="required" value="{delivery.edit.id}" {delivery.edit.check} onchange="recount();" /> {delivery.edit.name}</td>
		<td align="center"><span class="rur" id="delivery{delivery.edit.id}">{delivery.edit.price}</span></td>
		<td align="center"><span class="totalcounts">{order_count}</span></td>
		<td align="center"><span class="rur" id="deltotal{delivery.edit.id}">{delivery.edit.total}</span></td>
	</tr>
<!-- END edit -->
	<!-- BEGIN moreview -->
	<tr>
		<td colspan="4">{more_price}</td>
		<td class="cb" align="center"><span class="rur">{order_more}</span></td>
	</tr>
	<!-- END moreview -->
	<!-- BEGIN moreedit -->
	<tr>
		<td colspan="4">{more_price}</td>
		<td class="cb" align="center"><span class="rur">
			<input class="intable-count" type="text" size="4" name="more" id="delmore" value="{order_more}" pattern="^[0-9]+$" onchange="recount()" />
		</span></td>
	</tr>
	<!-- END moreedit -->
<!-- END delivery -->
</tbody>

<tfoot>
	<tr>
		<td class="head" colspan="3" align="right">{total}</td>
		<td class="head" align="center"><span id="totalcount" class="totalcounts">{order_count}</span></td>
		<td class="head" align="center"><span class="rur" id="totalprice">{order_price}</span></td>
	</tr>
<!-- BEGIN edit -->
	<tr><td class="cb" align="center" colspan="5"><input class="intable-select" type="text" name="comment" value="{order_comment}" placeholder="Комментарий к заказу" maxlength="50" /></td></tr>
<!-- END edit -->
<!-- BEGIN comment -->
	<tr><td colspan="5">{order_comment}</td></tr>
<!-- END comment -->
</tfoot>

</table>

<!-- BEGIN form -->
{form}
<!-- END form -->

<table class="form">
	<tr>
		<td class="form-label">
			<span class="openfraudicon">OpenFraud</span><br />
			<i class="xsmall grey">Служба безопасности</i>
		</td>
		<td class="form-descr" colspan="2">
			<div class="openfraud">IP-адрес - <span id="openfraud_ip"><a href="#" onclick="return openfraud_check( 'ip', '{order_ip}' );">проверить</a></span> <span id="openfraud_ip_more" class="hidden">(<a href="#" onclick="return openfraud_info( 'ip', '{order_ip}' );">подробнее</a>)</span></div>
			<div class="openfraud">Телефон - <span id="openfraud_phone" rel="{order_phone}"><a href="#" onclick="return openfraud_check( 'phone', $('#openfraud_phone').attr('rel') );">проверить</a></span> <span id="openfraud_phone_more" class="hidden">(<a href="#" onclick="return openfraud_info( 'phone', $('#openfraud_phone').attr('rel') );">подробнее</a>)</span></div>
<!-- BEGIN ofm -->
			<div class="openfraud">Вебмастер - <span id="openfraud_email"><a href="#" onclick="return openfraud_check( 'email', '{ofm.v}' );">проверить</a></span> <span id="openfraud_email_more" class="hidden">(<a href="#" onclick="return openfraud_info( 'email', '{ofm.v}' );">подробнее</a>)</span></div>
<!-- END ofm -->
<!-- BEGIN ofw -->
			<div class="openfraud">WMID - <span id="openfraud_wm"><a href="#" onclick="return openfraud_check( 'wm', '{ofw.v}' );">проверить</a></span> <span id="openfraud_wm_more" class="hidden">(<a href="#" onclick="return openfraud_info( 'wm', '{ofw.v}' );">подробнее</a>)</span></div>
<!-- END ofw -->
		</td>
	</tr>
<!-- BEGIN file -->
	<tr>
		<td class="form-label">Файл</td>
		<td class="form-show"><a title="Скачать прикреплённый к заказу файл" class="create" download="{order_file}" href="/data/files/{order_file}"> {order_file}</a></td>
	</tr>
<!-- END file -->
<!-- BEGIN edit -->
	<tr>
		<td class="form-label">{fio}</td>
		<td class="form-field"><input type="text" class="form-text" name="name" value="{order_name}" /></td>
	</tr>
	<tr>
		<td class="form-label">{phone}</td>
		<td class="form-field"><input id="phone" type="text" class="form-text" name="phone" value="{order_phone}" /></td>
		<td class="form-descr">
			<span id="phoneok" class="{phone_ok_c}">{phone_ok_t}</span>
			<a id="skypeto" class="skype" href="{phone_call}">{call}</a>
			<a class="checkit" href="#" onclick="return checkphone($('#phone').val());">Проверить</a>
			<div id="phoneinfo" class="phone-info">{phone_info}</div>
			{phwarn}
		</td>
	</tr>
	<tr>
		<td class="form-label">{index}</td>
		<td class="form-field"><input id="oindex" type="text" class="form-text" name="index" value="{order_index}" /></td>
		<td class="form-descr">
			<a id="checkaddr" class="checkaddr" href="#" onclick="return checkaddress();">{checkaddr}</a>
			<a id="showmap" class="order-country" href="#" onclick="onthemap()" target="_blank">На карте</a>
		</td>
	</tr>
	<tr>
		<td class="form-label">{area}</td>
		<td class="form-field"><input id="oarea" type="text" class="form-text" name="area" value="{order_area}" /></td>
		<td class="form-descr"><b class="red">Важно!</b> Цена доставки считается <u>только</u> при заполненных области и городе!</td>
	</tr>
	<tr>
		<td class="form-label">{city}</td>
		<td class="form-field"><input id="ocity" type="text" class="form-text" name="city" value="{order_city}" /></td>
		<td class="form-descr"><span id="spsr2city"><span class="deliv deliv2">СПСР</span> &mdash; <a href="#" onclick="return spsr();">посчитать стоимость доставки</a></span></td>
	</tr>
	<tr>
		<td class="form-label">{street}</td>
		<td class="form-field"><input id="ostreet" type="text" class="form-text" name="street" value="{order_street}" /></td>
		<td class="form-descr"><span id="rupost2city"><span class="deliv deliv1">Почта</span> &mdash; <a href="#" onclick="return rupost();">посчитать сроки доставки</a></span></td>
	</tr>
	<tr>
		<td class="form-label">{address}</td>
		<td class="form-field"><input id="oaddr" type="text" class="form-text" name="addr" value="{order_addr}" /></td>
		<td class="form-descr">Указывается только: дом, строение, корпус, квартира, … Остальное вписать в поля выше или нажать "<a href="#" onclick="return checkaddress();">Проверить адрес</a>" для автоматического заполнения.</td>
	</tr>
<!-- END edit -->
<!-- BEGIN view -->
	<tr>
		<td class="form-label">{name}</td>
		<td class="form-show">{order_name}</td>
	</tr>
	<tr>
		<td class="form-label">{phone}</td>
		<td class="form-show">
			<a class="{phone_ok_c}" href="{phone_call}">+{order_phone}</a>
			<a class="checkit" href="#" onclick="return checkphone('{order_phone}');">Проверить</a>
			<div class="phone-info" id="phoneinfo">{phone_info}</div>
			{phwarn}
		</td>
	</tr>
	<tr>
		<td class="form-label">{index}</td>
		<td class="form-show">{order_index}</td>
	</tr>
	<tr>
		<td class="form-label">{address}</td>
		<td class="form-show">{fulladdr}</td>
	</tr>
<!-- END view -->
	<tr>
		<td class="form-label">IP</td>
		<td class="form-show"><img src="/data/flag/{country}.png" alt="{country}" title="{country}" align="absmiddle" height="11" width="16" /> <a href="http://ipgeobase.ru/?address={order_ip}" target="_blank">{order_ip}</a> <span class="order-country">{order_country}</span> {ipwarn}</td>
	</tr>
	<tr>
		<td class="form-label">{source}</td>
		<td class="form-show"><a href="/?wm={wm_id}" class="order-sources source-{wm_class}">{wm_name}</a> {wm_src}</td>
	</tr>
<!-- BEGIN site -->
	<tr>
		<td class="form-label">{site}</td>
		<td class="form-show"><a href="http://{site_url}/" target="_blank">{site_url}</a></td>
	</tr>
<!-- END site -->
<!-- BEGIN space -->
	<tr>
		<td class="form-label">{space}</td>
		<td class="form-show"><a href="http://{space_url}/" target="_blank">{space_url}</a></td>
	</tr>
<!-- END space -->
<!-- BEGIN paid -->
	<tr>
		<td class="form-label">Оплачено</td>
		<td class="form-show">
			<div class="status paid{paid_ok}">{paid_type} - {paid_date}</div>
			<div class="small grey">{paid_info}</div>
		</td>
	</tr>
<!-- END paid -->
<!-- BEGIN docs -->
	<tr>
		<td class="form-label"></td>
		<td class="form-show"><a class="excel" href="{docs.u}">{packdocs}</a></td>
	</tr>
<!-- END docs -->
<!-- BEGIN track -->
	<tr>
		<td class="form-label">{track}</td>
		<td class="form-field"><input type="text" class="form-text" name="track" value="{track_code}" /></td>
	</tr>
<!-- END track -->
<!-- BEGIN delpro -->
	<tr>
		<td class="form-label">{delivery}</td>
		<td colspan="2" class="form-show">
        	{track_code}: <a href="{delpro.url}" title="{delpro.check}" class="{delpro.cls}" target="_blank">{delpro.info}</a>
		</td>
	</tr>
<!-- END delpro -->
<!-- BEGIN actions -->
	<tr><td class="form-headline" colspan="3">{action}</td></tr>
<!-- BEGIN block -->
	<tr>
		<td class="form-label">{actions.block.name}</td>
		<td class="form-descr" colspan="2">
<!-- BEGIN a -->
			<label><input type="radio" required="required" name="act" value="{actions.block.a.v}"> {actions.block.a.n}</label>
<!-- END a -->
		</td>
	</tr>
<!-- END block -->
<!-- END actions -->
<!-- BEGIN marks -->
	<tr><td class="form-headline" colspan="3">{mark}</td></tr>
	<tr>
		<td class="form-label"></td>
		<td>
<!-- BEGIN mk -->
			<p><label><input type="checkbox" name="{marks.mk.v}" value="1" /> {marks.mk.n}</label></p>
<!-- END mk -->
		</td>
	</tr>
<!-- END marks -->
</table>

<!-- BEGIN buttons -->
<div class="form-buttons">
	<input type="submit" class="form-submit" name="save" value="{save}" />
	<input type="submit" name="next" value="{next}" />
</div>
<!-- END buttons -->
<!-- BEGIN pickup -->
<div class="form-buttons">
	<a class="form-button form-submit" href="{pickup.u}" onclick="return confirm('{pickup.c}')">{pickup.t}</a>
</div>
<!-- END pickup -->

</form>

<script type="text/javascript">

<!-- BEGIN edit -->
// Phone auto check
$("#phone").change(function() {
	var sc = '{callscheme}';	var ph = $("#phone").val();
	if ( ph.substr(0,2) == "79" ) {		$("#phoneok").attr( "class", "phone-ok" );
		$("#phoneok").text( "ok" );
	} else {		$("#phoneok").attr( "class", "phone-bad" );
		$("#phoneok").text( "!!" );
	}
	$("#skypeto").attr( "href", sc.replace( "%s", ph ) );
	$("#openfraud_phone").attr( "rel", ph );
	checkphone( ph );
});
// Address manual check
function checkaddress () {   	$("#checkaddr").attr( "class", "checkprogress" );
   	var addr = $("#oaddr").val();
   	var street = $("#ostreet").val()
   	var area = $("#oarea").val()
   	var city = $("#ocity").val()
	if ( street != '' ) addr = street + ", " + addr;
	if ( city != '' ) addr = city + ", " + addr;
	if ( area != '' ) addr = area + ", " + addr;
	$.ajax({		type:		"POST",
		url:		"{u_addr}",
		dataType:	"json",
		data:		{ "addr" : addr },
		success:	function ( data ) {			if ( data.status == "ok" ) {				if ( confirm( data.text ) ) {					$("#oindex").val( data.ind );
					$("#oaddr").val( data.house );
					$("#oarea").val( data.area );
					$("#ocity").val( data.city );
					$("#ostreet").val( data.street );
					spsr ();
					rupost ();
				}
			} else alert( data.text );
         	$("#checkaddr").attr( "class", "checkaddr" );
		},
		error:		function () {         	alert( "Ошибка проверки адреса" );
         	$("#checkaddr").attr( "class", "checkaddr" );
		}
	});
	return false;
}
// Recount order information
var price = {order_price};
var counts = {order_count};
function recount () {
	price = 0;
	counts = 0;

	$(".items").each( function( id, el ) {    	var uid = $(el).attr( "id" );
    	var cnt = parseInt( $(el).val() );
    	var prc = $("#price"+uid).text();
    	prc = prc * cnt;
		$("#total"+uid).text( prc );
    	counts = counts + cnt;
    	price = price + prc ;
	});

	$(".discounts").each( function( id, el ) {
		var uid = $(el).val();
		var prc = $("#discount"+uid).text();
		$("#dctotal"+uid).text( Math.ceil( price * ( ( 100 - prc ) / 100 ) ) );
	});

	var discount = $("#order").find('input[name=discount]:checked').val();
	if ( discount > 0 && discount < 100 ) price = price * ( ( 100 - discount ) / 100 );
	price = Math.ceil( price );

	var present = $("#present").val();
	if ( present > 0 ) {
		var presentprice = parseInt( $("#present"+present).attr("rel") );
		price = price + presentprice;
		$("#prtotal").text( presentprice );
	} else $("#prtotal").text( "0" );

<!-- BEGIN delivery -->
	$(".deliveries").each( function( id, el ) {		var uid = $(el).val();
		var prc = $("#delivery"+uid).text();
		$("#deltotal"+uid).text( prc );
	});

	var delivery = $("#order").find('input[name=delivery]:checked').val();
	var delprice = parseInt( $("#delivery" + delivery).text() );
	var delmore = parseInt( $("#delmore").val() );

	price = price + delprice + delmore;

<!-- END delivery -->
	$(".totalcounts").text( counts );
	$("#totalprice").text( price );

}
<!-- END edit -->

function onthemap ( ) {
	var city = $("#ocity").val();
	var area = $("#oarea").val();
	var street = $("#ostreet").val();
	var flat = $("#oaddr").val();

	var addr = area ? area : "";
	if ( city ) addr += ( addr ? ( ", " + city ) : city );
	if ( street ) addr += ( addr ? ( ", " + street ) : street );
	if ( flat ) addr += ( addr ? ( ", " + flat ) : flat );
	$("#showmap").attr( "href", "http://maps.yandex.ru/?text="+addr );

}

function spsr ( ) {

	var city = $("#ocity").val();
	var area = $("#oarea").val();
	var price = $("#totalprice").text();

	$.ajax({
		type:		"POST",
		url:		"{u_spsr}",
		dataType:	"json",
		data:		{ "to" : city, "area" : area, "price" : price },
		success:	function ( data ) {
			if ( data && typeof( data.delivery_mode ) != "undefined" ) {
				$("#spsr2city").html( '<span class="deliv deliv2">СПСР</span> ' + data.date_min + '-' + data.date_max + ' дней - <b class="rur">' + data.cost + '</b> <a class="update" href="#" onclick="return spsr();">Пересчитать</a>' );
			} else $("#spsr2city").html( '<span class="deliv deliv2">СПСР</span> - нет экспресс-доставки <a class="update" href="#" onclick="return spsr();">Пересчитать</a>' );
		}
	});

	return false;

}


function rupost ( ) {

	var index = $("#oindex").val();
	var price = $("#totalprice").text();

	$.ajax({
		type:		"GET",
		url:		"{u_rupost}?to="+index+"&price="+price,
		dataType:	"json",
		success:	function ( data ) {
			if ( data && typeof( data.ok ) != "undefined" ) {
				$("#rupost2city").html( '<span class="deliv deliv1">Почта</span> ' + data.dd + ' дней - <b class="rur">' + data.cost + '</b> <a class="update" href="#" onclick="return rupost();">Пересчитать</a>' );
			} else $("#rupost2city").html( '<span class="deliv deliv1">Почта</span> - ошибка адреса <a class="update" href="#" onclick="return rupost();">Пересчитать</a>' );
		}
	});

	return false;

}

function checkphone( ph ) {	$("#phoneinfo").load( '{u_phone}' + ph );
	return false;
}

// OpenFraud
function openfraud_check ( type, value ) {
	$.ajax( "{ofc}?type="+type+"&value="+value, {
		dataType:	"json",
		context:	$("#openfraud_"+type),
		success:	function( data ) {
			if ( data.status ) {				var uid = $(this).attr("id");
				$(this).html( '<b class="red">уровень опасности: '+data.warn+'</b>' );
				$("#"+uid+"_more").show();
			} else $(this).html( '<b class="green">предупреждений нет</b>' );
		},
		error:		function() { alert( "Ошибка при проверке в базе OpenFraud" ); }
	});	return false;

}

function openfraud_info ( type, value ) {
	$.ajax( "{ofi}?type="+type+"&value="+value, {		dataType:	"text",
		success:	function( data ) { alert( data); },
		error:		function() { alert( "Ошибка при проверке в базе OpenFraud" ); }
	}); return false;

}

</script>