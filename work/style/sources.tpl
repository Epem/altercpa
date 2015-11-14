<div class="lister">
	<form class="lf" action="{u_search}" method="get">
		<span class="icon cal"></span>
		<input title="Дата начала отчёта" type="date" class="text" name="from" value="{from}" />
		<input title="Дата окончания отчёта" type="date" class="text" name="to" value="{to}" />
		<label title="{offer}">
			<span class="icon offer"></span>
			<select name="o" class="text">
				<option value="">&mdash; {offer} &mdash;</option>
<!-- BEGIN offer -->
				<option value="{offer.value}" {offer.select}>{offer.name}</option>
<!-- END offer -->
			</select>
		</label>
		<label title="{flow}">
			<span class="icon flow"></span>
			<select name="f" class="text">
				<option value="">&mdash; {flow} &mdash;</option>
<!-- BEGIN flow -->
				<option value="{flow.value}" {flow.select}>{flow.name}</option>
<!-- END flow -->
			</select>
		</label>
		<label title="{source}">
			<span class="icon site"></span>
			<select name="q" class="text">
				<option value="">&mdash; {source} &mdash;</option>
<!-- BEGIN source -->
				<option value="{source.value}" {source.select}>{source.name}</option>
<!-- END source -->
			</select>
		</label>
		<label title="{group}">
			<span class="icon istatus"></span>
			<select name="g" class="text">
<!-- BEGIN group -->
				<option value="{group.value}" {group.select}>{group.name}</option>
<!-- END group -->
			</select>
		</label>
		<label title="{cutoff}">
			<span class="icon filter"></span>
			<select name="c" class="text">
<!-- BEGIN cutoff -->
				<option value="{cutoff.value}" {cutoff.select}>{cutoff.name}</option>
<!-- END cutoff -->
			</select>
		</label>
		<input type="submit" class="form-button" value="{show}" />
<!-- BEGIN alls -->
		<label><input type="checkbox" name="a" value="1" {all} /> {showall}</label>
<!-- END alls -->
	</form>
	<div class="shown">
		<a href="{u_csv}" class="excel">CSV</a>
<!-- BEGIN help -->
		<a href="/help/#sources" class="help">Помощь</a>
<!-- END help -->
		<a href="{u_today}" class="date">Сегодня</a>
	</div>
	<div class="clear"></div>
</div>

<table class="post-table tablesorter" cellspacing="0" id="sourceslist">

<thead>
<tr>
	<th colspan="2" class="headerSortDown">{source}</th>
	<th>{spaces}</th>
	<th>{unique}</th>
	<th>Лэндинг</th>
	<th>{unique}</th>
	<th>{accept}</th>
	<th>{wait}</th>
	<th>{cancel}</th>
	<th>{total}</th>
	<th>Чёрный список</th>
</tr>
</thead>

<tbody>
<!-- BEGIN stat -->
<tr class="{stat.class}">
	<td align="center">{stat.id}</td>
	<td align="center"><a href="{stat.u}" class="{stat.block}">{stat.src}</a></td>
	<td align="center">{stat.spaces}</td>
	<td align="center">{stat.suni}</td>
	<td align="center">{stat.clicks}</td>
	<td align="center">{stat.unique}</td>
	<td align="center">{stat.ca}</td>
	<td align="center">{stat.cw}</td>
	<td align="center">{stat.cc}</td>
	<td align="center">{stat.ct}</td>
	<td align="center" class="blbuttons"><a id="bla{stat.bli}" href="{stat.blu}" class="{stat.blc}">{stat.blt}</a></td>
</tr>
<!-- END stat -->
<!-- BEGIN nostat -->
<tr><td colspan="11" class="noitems">{nostats}</td></tr>
<!-- END nostat -->
</tbody>

</table>

<script type="text/javascript" src="/style/jquery.tablesorter.js"></script>
<script type="text/javascript">
	$("#sourceslist").tablesorter();
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