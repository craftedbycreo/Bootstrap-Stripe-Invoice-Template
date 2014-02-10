<?php
//common settings
$companyName = "Creo LLC";
$address = <<<EOD
Street 1<br>
Street 2<br>
City, State Zip<br>
EOD;
$phone = "407-603-6432";
$baseURL = "http://craftedbycreo.com";
$baseEmail = "hello@craftedbycreo.com";
$icon = $baseURL."/assets/img/favicon.png";
$stripeKey = "sk_test_";
$publicKey = "pk_test_";

	$invoiceID = $_POST["i"];
	if(!isset($invoiceID))
		$invoiceID = $_GET['i'];
	if($invoiceID !== "in_") {
		$outputInvoice = true;
		require('stripe/Stripe.php');
		Stripe::setApiKey($stripeKey);

		try {
			$invoice = Stripe_Invoice::retrieve($invoiceID);
		} catch(Stripe_InvalidRequestError $e) {
			header( 'Location: '.$baseURL );
			exit;
		}
		$customer = Stripe_Customer::retrieve($invoice->customer);
		$alreadyPaid = $invoice->closed;
		$token = $_POST['stripeToken'];
		if(isset($token)) {
			try {
				$charge = Stripe_Charge::create(array(
					"amount" => $invoice->total,
					"currency" => "usd",
					"card" => $token,
					"description" => $customer->email
				));
				$invoice->closed = true;
				$invoice->save();
				$showThanks = true;
			} catch(Stripe_CardError $e) {
			// The card has been declined
				$cardError = true;
			}
		}
	}
	if($outputInvoice) {
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="utf-8">
	<title>Customer Invoice</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Customer Invoice">
	<meta name="author" content="<?= $companyName ?>">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
	<style>
		.invoice-head td {
		padding: 0 8px;
		}
		.container {
		padding-top:30px;
		}
		.invoice-body{
		background-color:transparent;
		}
		.invoice-thank{
		margin-top: 60px;
		padding: 5px;
		}
		address{
		margin-top:15px;
		}
		.already-paid * {text-decoration: line-through}
		#alreadyPaid {position:absolute;top:30%;left:30%;color:red;border:8px solid red;font-size:185px;margin-top: -50px;text-decoration: none;}
	</style>
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link rel="shortcut icon" href="<?= $icon ?>">
	</head>

	<body>
	<div class="container">
<?php
	if($cardError) {
			echo "<div id='error'>That card has been declined, double check that you entered it correctly!</div>";
	}
	if($showThanks) {
		echo "<h1 id='thanks'>Thanks!</h1>";
	}
?>
		<div class="row">
			<div class="col col-lg-4 col-lg-offset-2">
				<a href="<?= $baseURL; ?>">
					<img src="<?= $baseURL;?>/assets/img/favicon.png" title="logo"><br/>
					<strong><?= $companyName ?></strong>
				</a>
				<br/>
				<address>
					<?= $address ?>
				</address>
			</div>
			<div class="col col-lg-4 well">
				<table class="invoice-head">
					<tbody>
						<tr>
							<td class="pull-right"><strong>Customer</strong></td>
							<td><?php echo $customer->email; ?></td>
						</tr>
						<tr>
							<td class="pull-right"><strong>Invoice #</strong></td>
							<td><?php echo $invoice->id; ?></td>
						</tr>
						<tr>
							<td class="pull-right"><strong>Date</strong></td>
							<td><?php echo date('M j, Y', $invoice->date); ?></td>
						</tr>
						<tr>
							<td class="pull-right"><strong>Period</strong></td>
							<td><?php echo date('M j, Y', $invoice->period_start) .' to ' . date('M j, Y', $invoice->period_end); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col col-lg-8 col-lg-offset-2">
				<h2>Invoice</h2>
			</div>
		</div>
		<div class="row">
			<div class="col col-lg-8 col-lg-offset-2 well invoice-body<?= $alreadyPaid ? " already-paid" : "" ?>">
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Description</th>
							<th>Date</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>
				<?php
					$total = 0;
					foreach($invoice->lines->data as $line){
						echo '<tr>';
						$amount = $line->amount / 100;
						echo '<td>'.$line->description.'</td>';
						echo '<td>' . date('M j, Y', $line->period->start) .' - ' . date('M j, Y', $line->period->end). '</td>';
						echo '<td>$' . number_format($amount,2).'</td>';
						$total += $amount;
						echo '</tr>';
					}
					foreach($invoice->lines->subscriptions as $subscription){
						echo '<tr>';
						$amount = $subscription->amount / 100;
						echo '<td>'.$subscription->plan->name.' ($'.number_format($subscription->plan->amount / 100,2).'/'.$subscription->plan->interval.')</td>';
						echo '<td>' . date('M j, Y', $subscription->period->start) .' - ' . date('M j, Y', $subscription->period->end). '</td>';
						echo '<td>$' . number_format($amount,2).'</td>';
						$total += $amount;
						echo '</tr>';
					}

					if(isset($invoice->discount)){
						echo '<tr>';
						echo '<td>'.$invoice->discount->coupon->id.' ('.$invoice->discount->coupon->percent_off.'% off)</td>';
						$discount = $total * ($invoice->discount->coupon->percent_off/100);
						echo '<td>&nbsp;</td>';
						echo '<td>-$'.number_format($discount,2).'</td>';
						echo '</tr>';
					}
				?>
						<tr>
							<td>&nbsp;</td>
							<td><strong>Total</strong></td>
							<td><strong>$<?php echo number_format(($invoice->total / 100), 2); ?></strong></td>
						</tr>
					</tbody>
				</table>
				<?= $alreadyPaid ? "<h1 id='alreadyPaid'>PAID</h1>" : "" ?>
			</div>
		</div>
		<div class="row">
			<div class="col col-lg-8 col-lg-offset-2 offset1 well invoice-thank centered">
<?php if(!$alreadyPaid) { ?>
				<form method="POST" style="text-align:center;">
					<input name="i" id="i" value="<?= $invoiceID ?>" type="hidden"/>
					<script
					src="https://checkout.stripe.com/checkout.js" class="stripe-button"
					data-key="<?= $publicKey ?>"
					data-email="<?= $customer->email ?>"
					data-image="<?= $icon ?>"
					data-name="<?= $companyName ?>"
					data-description="Invoice <?= $invoiceID ?>"
					data-amount="<?= $invoice->total ?>">
					</script> securely through <a href="//stripe.com"><img src="https://stripe.com/img/navigation/logo.png?1"></a>
				</form>
<?php } ?>
				<h5 style="text-align:center;">Thank You!</h5>
			</div>
		</div>
		<div class="row">
			<div class="col col-lg-8 col-lg-offset-2">
				<div class="col col-lg-4">
					<strong>Phone:</strong><br/><a href="tel:<?= $phone ?>"><?= $phone ?></a>
				</div>
				<div class="col col-lg-4">
					<strong>Email:</strong><br/><a href="mailto:<?= $baseEmail ?>"><?= $baseEmail ?></a>
				</div>
				<div class="col col-lg-4">
					<strong>Website:</strong><br/><a href="<?= $baseURL ?>"><?= $baseURL ?></a>
				</div>
			</div>
		</div>
	<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
	</body>
</html>
<?php
//end of outputInvoice
} else {
	header( 'Location: '.$baseURL );
}
?>
