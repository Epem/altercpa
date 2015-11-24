	<div id="main"><div class="container mini">

		<div id="midlogo"><a href="/"><h1 id="logo"><span>MyCPA</span></h1></a></div>

<!-- BEGIN neworder -->
		<div class="panel panel-success">
			<div class="panel-heading">{success}</div>
			<div class="panel-body">
				<p class="text-success"><b>Спасибо за заказ!</b> Мы перезвоним Вам для уточнения деталей доставки и состава заказа.</p>
				<div class="small text-muted"><b>Важно</b>: мы работаем с 9:00 до 21:00 (МСК), если Вы оставили заказ позднее 21:00, мы позвоним Вам утром.</div>
			</div>
		</div>
<!-- END neworder -->
<!-- BEGIN payok -->
		<div class="alert alert-success" role="alert"><strong>Оплата за заказ поступила!</strong> Менеджер свяжется с Вами в течение часа</div>
<!-- END payok -->
<!-- BEGIN payfail -->
		<div class="alert alert-danger" role="alert"><strong>Ошибка оплаты заказа!</strong> Повторите попытку позже ...</div>
<!-- END payfail -->


		<div class="formblock">
			<h2>{order} №{order_id}</h2>
			<h3>{offer_name}</h3>
			<p class="lead"><b>{status}</b>: {order_status}</p>
			<p class="text-muted">Заказ получен {order_time}</p>
		</div>

<!-- BEGIN pay -->
		<div class="panel panel-primary">
			<div class="panel-heading">Оплата заказа</div>
			<div class="panel-body">
				<p>{pay.type}</p>
				<h4>Информация о получателе платежа:</h4>
				{pay.info}
				<h4>Оплатить {order_price} рублей с помощью:</h4>
				<div class="text-center">
<!-- BEGIN wm -->
				<form class="col-sm-4" action="https://merchant.webmoney.ru/lmi/payment.asp" method="post">
					<input type="hidden" name="LMI_PAYMENT_DESC" value="MyCPA ID {order_id}">
					<input type="hidden" name="LMI_PAYMENT_NO" value="{order_id}">
					<input type="hidden" name="LMI_PAYEE_PURSE" value="{pay.wm.to}">
					<input type="hidden" name="LMI_SIM_MODE" value="0">
					<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="{order_price}" />
					<input type="submit" class="btn btn-lg btn-block btn-primary" value="WebMoney" />
				</form>
<!-- END wm -->
<!-- BEGIN ym -->
				<form class="col-sm-4" action="https://money.yandex.ru/quickpay/confirm.xml" method="post">
					<input type="hidden" name="receiver" value="{pay.ym.to}" />
					<input type="hidden" name="formcomment" value="Оплата заказа {order_id} на MyCPA" />
					<input type="hidden" name="short-dest" value="Оплата заказа {order_id} на MyCPA" />
					<input type="hidden" name="label" value="{order_id}" />
					<input type="hidden" name="quickpay-form" value="shop" />
					<input type="hidden" name="targets" value="{order_id}" />
					<input type="hidden" name="sum" value="{order_price}" data-type="number" />
					<input type="hidden" name="paymentType" value="PC" />
					<input type="submit" class="btn btn-lg btn-block btn-warning" value="Яндекс.Деньги" />
				</form>
				<form class="col-sm-4" action="https://money.yandex.ru/quickpay/confirm.xml" method="post">
					<input type="hidden" name="receiver" value="{pay.ym.to}" />
					<input type="hidden" name="formcomment" value="Оплата заказа {order_id} на MyCPA" />
					<input type="hidden" name="short-dest" value="Оплата заказа {order_id} на MyCPA" />
					<input type="hidden" name="label" value="{order_id}" />
					<input type="hidden" name="quickpay-form" value="shop" />
					<input type="hidden" name="targets" value="{order_id}" />
					<input type="hidden" name="sum" value="{order_price}" data-type="number" />
					<input type="hidden" name="paymentType" value="AC" />
					<input type="submit" class="btn btn-lg btn-block btn-success" value="Visa / MasterCard" />
				</form>
<!-- END ym -->
				</div>
			</div>
		</div>
<!-- END pay -->

<!-- BEGIN delivery -->
		<div class="panel panel-primary">
			<div class="panel-heading">{delivery}</div>
			<div class="panel-body"><a href="{track_url}" class="btn btn-block btn-primary" target="_blank">{manual}</a></div>
			<table class="table">
				<tr><th nowrap="nowrap">{track}</th><td>{track_code}</td></tr>
				<tr><th nowrap="nowrap">{type}</th><td>{track_type}</td></tr>
				<tr><th nowrap="nowrap">{status}</th><td>{track_status}</td></tr>
			</table>
		</div>
<!-- END delivery -->

	</div></div>
