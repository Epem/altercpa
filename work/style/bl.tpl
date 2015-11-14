<!-- BEGIN type -->
<a class="create rf" href="{type.url}">Скачать список</a>
<h3>{type.name}</h3>

<table class="post-table tablesorter" cellspacing="0" id="sourceslist">

<thead>
<tr>
	<th>Идентификатор</th>
	<th>Действие</th>
</tr>
</thead>

<tbody>
<!-- BEGIN utm -->
<tr>
	<td class="head">{type.utm.name}</td>
	<td width="20%" class="head" align="center"><a class="create" href="{type.utm.url}">Скачать</a></td>
</tr>
<!-- BEGIN item -->
<tr>
	<td>{type.utm.item.id}</td>
	<td align="center" class="blbuttons"><a id="bla{type.utm.item.bli}" href="{type.utm.item.blu}" class="decline red">{type.utm.item.blt}</a></td>
</tr>
<!-- END item -->
<!-- END utm -->
<!-- BEGIN no -->
<tr><td colspan="2" class="noitems">Чёрный список пуст</td></tr>
<!-- END no -->
</tbody>

</table>
<!-- END type -->

<script type="text/javascript">
    $(".blbuttons a").click(function(){
		var u = $(this).attr( "href" ) + '&z=ajax';
		$(this).attr( "class", "wait grey" );
		$.ajax({ type: "GET", url: u, success: function(data) {        	if ( data.status == "ok" ) {        		$("#bla"+data.id).attr( "href", data.url );
        		$("#bla"+data.id).attr( "class", data.cls );
        		$("#bla"+data.id).text( data.text );
        		$("#bla"+data.id).attr( "id", "bla"+data.newid );
        	} else alert( "Ошибка работы с чёрным списком" );
		}, dataType: "JSON" });
		return false;

    });
</script>

<div class="entry"><a name="help"></a>{helptext}</div>