	</div>

    <div id="footer">
    	<div id="footer-copyright">{copyright}</div>
        <div id="footer-altervision">
        	{altervision}
<!-- BEGIN debugga -->
        	[ {pr_time}s : {pr_mem} : {pr_sql} sql : {pr_date} ]
<!-- END debugga -->
        </div>
    </div>

</div>
</div>

<script type="text/javascript">
    var datefield=document.createElement("input")
    datefield.setAttribute("type", "date")
    if (datefield.type!="date"){
        document.write('<link href="/style/ui/jquery-ui.css" rel="stylesheet" type="text/css" />\n')
        document.write('<script src="/style/ui/jquery-ui.js"><\/script>\n')
		$(function() { $('input[type="date"]').datepicker({ dateFormat: "yy-mm-dd" }); });
    }
</script>

</body>
</html>