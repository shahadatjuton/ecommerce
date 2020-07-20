<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<?php
//include 'includes/session.php';

if(isset($_SESSION['user'])){
    $conn = $pdo->open();

    $stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products on products.id=cart.product_id WHERE user_id=:user_id");
    $stmt->execute(['user_id'=>$user['id']]);

    $total = 0;
    foreach($stmt as $row){
        $subtotal = $row['price'] * $row['quantity'];
        $total += $subtotal;
    }

    $pdo->close();


}
?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<h1 class="page-header">YOUR CART</h1>
	        		<div class="box box-solid">
	        			<div class="box-body">
		        		<table class="table table-bordered">
		        			<thead>
		        				<th></th>
		        				<th>Photo</th>
		        				<th>Name</th>
		        				<th>Price</th>
		        				<th width="20%">Quantity</th>
		        				<th>Subtotal</th>
		        			</thead>
		        			<tbody id="tbody">
		        			</tbody>
		        		</table>
	        			</div>
	        		</div>
	        		<?php
	        			if(isset($_SESSION['user'])){
	        			    ?>
                            <div class="container">

                                <div class="row">
                                    <div class="col-md-12"><div id="token_response"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <button class="btn btn-primary btn-block" onclick="pay(<?= $total*1000 ?> )">Pay With Stripe</button>
                                    </div>
                                </div>
                            </div>
	        			<?php
	        			}
	        			else{
	        				echo "
	        					<h4>You need to <a href='login.php'>Login</a> to checkout.</h4>
	        				";
	        			}
	        		?>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
	      </section>
	     
	    </div>
	  </div>
  	<?php $pdo->close(); ?>
  	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
var total = 0;
$(function(){
	$(document).on('click', '.cart_delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: 'cart_delete.php',
			data: {id:id},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.minus', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		if(qty>1){
			qty--;
		}
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.add', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		qty++;
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	getDetails();
	getTotal();

});

function getDetails(){
	$.ajax({
		type: 'POST',
		url: 'cart_details.php',
		dataType: 'json',
		success: function(response){
			$('#tbody').html(response);
			getCart();
		}
	});
}

function getTotal(){
	$.ajax({
		type: 'POST',
		url: 'cart_total.php',
		dataType: 'json',
		success:function(response){
			total = response;
		}
	});
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<script src="https://checkout.stripe.com/checkout.js"></script>

<script type="text/javascript">

    function pay(amount) {
        var handler = StripeCheckout.configure({
            key: 'pk_test_51H5Pc4K0PVU97suHRBLTVqtQaGy5jcTeFE7w3fF5kuveULhdPBeKNjfqt5Zh94q4ha3A9B5MvaEaPViuhhpW4TTY00yIm1AMi7', // your publisher key id
            locale: 'auto',
            token: function (token) {
                // You can access the token ID with `token.id`.
                // Get the token ID to your server-side code for use.
                // console.log('Token Created!!');
                // console.log(token)
                // $('#token_response').html(JSON.stringify(token));
                // append
                $.ajax({
                    url:"payment.php",
                    method: 'post',
                    data: { tokenId: token.id, amount: amount },
                    dataType: "json",
                    success: function( response ) {
                        console.log(response.data);
                        $('#token_response').append( '<br />' + JSON.stringify(response.data));
                    }
                })
            }
        });

        handler.open({
            name: 'Clear the payment',

            amount: amount * 1
        });
    }
</script>

<!-- Paypal Express -->
<!--<script>-->
<!--paypal.Button.render({-->
<!--    env: 'sandbox', // change for production if app is live,-->
<!---->
<!--	client: {-->
<!--        sandbox:    'ASb1ZbVxG5ZFzCWLdYLi_d1-k5rmSjvBZhxP2etCxBKXaJHxPba13JJD_D3dTNriRbAv3Kp_72cgDvaZ',-->
<!--        //production: 'AaBHKJFEej4V6yaArjzSx9cuf-UYesQYKqynQVCdBlKuZKawDDzFyuQdidPOBSGEhWaNQnnvfzuFB9SM'-->
<!--    },-->
<!---->
<!--    commit: true, // Show a 'Pay Now' button-->
<!---->
<!--    style: {-->
<!--    	color: 'gold',-->
<!--    	size: 'large'-->
<!--    },-->
<!---->
<!--    payment: function(data, actions) {-->
<!--        return actions.payment.create({-->
<!--            payment: {-->
<!--                transactions: [-->
<!--                    {-->
<!--                    	//total purchase-->
<!--                        amount: {-->
<!--                        	total: total,-->
<!--                        	currency: 'USD'-->
<!--                        }-->
<!--                    }-->
<!--                ]-->
<!--            }-->
<!--        });-->
<!--    },-->
<!--    onAuthorize: function(data, actions) {-->
<!--        return actions.payment.execute().then(function(payment) {-->
<!--			window.location = 'sales.php?pay='+payment.id;-->
<!--        });-->
<!--    },-->
<!---->
<!--}, '#paypal-button');-->
<!--</script>-->
</body>
</html>