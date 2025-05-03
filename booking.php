<?php
include "db.php";
?>

<?php
function confirm($result){
    global $con;
    if(!$result){
        die("Query failed".mysqli_error($con));
    }
}

if(isset($_GET['u'])){
	$the_room_category=$_GET['u'];
}

if(isset($_GET['cin'])){
	$check_in=$_GET['cin'];
}

if(isset($_GET['cout'])){
	$check_out=$_GET['cout'];
}

if(isset($_GET['branch'])){
	$branch_id=$_GET['branch'];
}

$query="SELECT $the_room_category.room_id FROM $the_room_category JOIN rooms ON $the_room_category.room_id=rooms.room_id WHERE (rooms.branch_id='{$branch_id}' AND $the_room_category.room_id NOT IN (SELECT forr.room_id FROM $the_room_category JOIN forr ON $the_room_category.room_id=forr.room_id WHERE (('{$check_in}' BETWEEN forr.check_in_date AND forr.check_out_date) OR ('{$check_out}' BETWEEN forr.check_in_date AND forr.check_out_date)))) LIMIT 1";
    $select_all_rooms=mysqli_query($con, $query);
    while($row=mysqli_fetch_assoc($select_all_rooms)){
        $room_id=$row['room_id'];
    }

if($the_room_category=="simple"){
    $total_amt="5000";
}

if($the_room_category=="deluxe"){
    $total_amt="10000";
}

if($the_room_category=="suite"){
    $total_amt="15000";
}

$total_amt=$total_amt*(ceil((strtotime($check_out)-strtotime($check_in))/86400));

if(isset($_POST['add_booking'])){
	$f_name=$_POST['f_name'];
	$l_name=$_POST['l_name'];
	$cust_email=$_POST['cust_email'];
	$cust_phone=$_POST['cust_phone'];
	$country=$_POST['country'];
	$dob=$_POST['dob'];
	$passport_no=$_POST['passport_no'];
	$payment_type=$_POST['payment_type'];

	if(isset($_POST['dep_name'])) {
		$dep_name=$_POST['dep_name'];
		$dep_id=$_POST['dep_id'];
		$query="SELECT * FROM dependents WHERE dep_id='{$dep_id}' OR passport_no='{$passport_no}'";
    	$duplicate=mysqli_query($con, $query);
    	if (mysqli_num_rows($duplicate) != 0){
    		$warning="Please enter the correct values";
      		echo "<script>alert('$warning')</script>";
    	}		
	}

    $booking_id = substr(str_shuffle(str_repeat("0123456789", 5)), 0, 5);
    $bill_id = substr(str_shuffle(str_repeat("0123456789", 6)), 0, 6);
    $booking_date = date('Y-m-d H:i:s');

    $query="SELECT * FROM customer WHERE f_name='{$f_name}' AND l_name='${l_name}' AND country='{$country}' AND dob='{$dob}' AND cust_email='{$cust_email}' AND cust_phone='{$cust_phone}' AND passport_no='{$passport_no}'";
    $duplicate=mysqli_query($con, $query);
    if (mysqli_num_rows($duplicate) != 0){
    	while($row=mysqli_fetch_assoc($duplicate)){
        	$the_cust_id=$row['cust_id'];
        	$cust_exists=true;
  		}
    }
    else {
    	$query="SELECT * FROM customer WHERE cust_email='{$cust_email}' OR cust_phone='{$cust_phone}' OR passport_no='{$passport_no}'";
	    $duplicate=mysqli_query($con, $query);
    	if (mysqli_num_rows($duplicate) != 0){
    		$warning="Please enter the correct values";
      		echo "<script>alert('$warning')</script>";
    	}
    }

   if(!isset($warning)){ 
   		if($cust_exists==false) {
   			$query="SELECT * FROM customer WHERE cust_id=(SELECT MAX(cust_id) FROM customer)";
			$select_cust_id=mysqli_query($con, $query);
    		while($row=mysqli_fetch_assoc($select_cust_id)){
        		$the_cust_id=$row['cust_id'];
  			}
    		$the_cust_id=$the_cust_id+1;
    		$query="INSERT INTO customer(cust_id, cust_email, cust_phone, passport_no, country, dob, f_name, l_name) VALUES('{$the_cust_id}', '{$cust_email}', '{$cust_phone}', '{$passport_no}', '{$country}', '{$dob}', '{$f_name}', '{$l_name}')";
	  		$create_query=mysqli_query($con,$query);
			confirm($create_query);
   		}

		if(isset($_POST['dep_name'])) {
			$query="INSERT INTO dependents(cust_id, dep_name, passport_no) VALUES('{$the_cust_id}', '{$dep_name}', '{$dep_id}')";
	  		$create_query=mysqli_query($con,$query);
			confirm($create_query);
		}

		$query="INSERT INTO booking(cust_id, Booking_id, room_id, branch_id) VALUES('{$the_cust_id}', '{$booking_id}', '{$room_id}', '{$branch_id}')";
	  	$create_query=mysqli_query($con,$query);
		confirm($create_query);

		$query="INSERT INTO bill(cust_id, Bill_id, Amount, Payment_type) VALUES('{$the_cust_id}', '{$bill_id}', '{$total_amt}', '{$payment_type}')";
	  	$create_query=mysqli_query($con,$query);
		confirm($create_query);

		$query="INSERT INTO forr(Booking_id, room_id, check_in_date, check_out_date) VALUES('{$booking_id}', '{$room_id}', '{$check_in}', '{$check_out}')";
	  	$create_query=mysqli_query($con,$query);
		confirm($create_query);

		$query="INSERT INTO generates(Booking_id, Bill_id, booking_date) VALUES('{$booking_id}', '{$bill_id}', '{$booking_date}')";
	  	$create_query=mysqli_query($con,$query);
		confirm($create_query);
		
		header("Location: confirmation.php?u={$booking_id}&cat={$the_room_category}");
	}
}
	
?>


<html>
	<head>
	<title>Booking</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="license" href="https://www.opensource.org/licenses/mit-license/">
		<link href="css/bootstrap.css" rel="stylesheet">
		<link rel="stylesheet" href="./css/style.css" />
		<link rel="icon" href="images/icon.jpg">

<style>
	.section {
		height: 150%;
		background-image: url('./images/background2.jpg');
		background-size:cover;
		
	}

	.section .section-center {
		position: relative;
		top: 50%;
		left: 0;
		right: 0;
		-webkit-transform: translateY(-50%);
		transform: translateY(-50%);
	}

	#booking {
		font-family: 'Raleway', sans-serif;
	}

	.booking-form {
		position: relative;
		max-width: 940px;
		width:100%;
		margin: auto;
		padding: 40px;
		overflow: hidden;
		background-image: url('./images/background.jpg');
		background-size:cover;
		border-radius: 5px;
		z-index: 20;
	}

	.booking-form::before {
		content: '';
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		top: 0;
		background: rgba(0, 0, 0, 0.7);
		z-index: -1;
	}

	.booking-form .form-header {
		text-align: center;
		position: relative;
		margin-bottom: 30px;
	}

	.booking-form .form-header h1 {
		font-weight: 700;
		text-transform: capitalize;
		font-size: 42px;
		margin: 0px;
		color: #fff;
	}

	.booking-form .form-group {
		position: relative;
		
	}

	.booking-form .form-control {
		background-color: rgba(255, 255, 255, 0.2);
		height: 60px;
		padding: 0px 25px;
		border: none;
		border-radius: 40px;
		color: #fff;
		-webkit-box-shadow: 0px 0px 0px 2px transparent;
		box-shadow: 0px 0px 0px 2px transparent;
		-webkit-transition: 0.2s;
		transition: 0.2s;
		display: inline;
	    width: 250px;
	}

	.booking-form .form-control::-webkit-input-placeholder {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form .form-control:-ms-input-placeholder {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form .form-control::placeholder {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form .form-control:focus {
		-webkit-box-shadow: 0px 0px 0px 2px #ff8846;
		box-shadow: 0px 0px 0px 2px #ff8846;
	}

	.booking-form input[type="text"].form-control {
		padding: 16px;
		width:250px; 
		float:left;
		
		margin-right:30px;
	}

	.booking-form input[type="text"].form-control:invalid {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form input[type="text"].form-control+.form-label {
		opacity: 1;
		top: 10px;
	}

	.booking-form input[type="date"].form-control {
		padding-top: 16px;
		width:250px; 
		float:left;
		
		margin-right:30px;
	}

	.booking-form input[type="date"].form-control:invalid {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form input[type="date"].form-control+.form-label {
		opacity: 1;
		top: 10px;
	}

	.booking-form input[type="email"].form-control {
		padding-top: 16px;
		width:250px; 
		float:left;
		
		margin-right:30px;
	}

	.booking-form input[type="email"].form-control:invalid {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form input[type="email"].form-control+.form-label {
		opacity: 1;
		top: 10px;
	}



	.booking-form select.form-control {
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
		width:250px;
		float:left;
		margin-right:30px;
		
		
	}

	.booking-form select.form-control:invalid {
		color: rgba(255, 255, 255, 0.5);
	}

	.booking-form select.form-control+.select-arrow {
		position: absolute;
		right: 15px;
		top: 50%;
		-webkit-transform: translateY(-50%);
		transform: translateY(-50%);
		width: 32px;
		line-height: 32px;
		height: 32px;
		text-align: center;
		pointer-events: none;
		color: rgba(255, 255, 255, 0.5);
		font-size: 14px;
	}

	.booking-form select.form-control+.select-arrow:after {
		content: '\279C';
		display: block;
		-webkit-transform: rotate(90deg);
		transform: rotate(90deg);
	}

	.booking-form select.form-control option {
		color: #000;
	}

	.booking-form .form-label {
		position: absolute;
		top: -10px;
		left: 25px;
		opacity: 0;
		color: #ff8846;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 1.3px;
		height: 15px;
		line-height: 15px;
		-webkit-transition: 0.2s all;
		transition: 0.2s all;
	}

	.booking-form .form-group.input-not-empty .form-control {
		padding-top: 16px;
	}

	.booking-form .form-group.input-not-empty .form-label {
		opacity: 1;
		top: 10px;
	}

	.booking-form .submit-btn {
		color: #fff;
		background-color: #e35e0a;
		font-weight: 700;
		height: 60px;
		padding: 10px 30px;
		width: 100%;
		border-radius: 40px;
		border: none;
		text-transform: uppercase;
		font-size: 16px;
		letter-spacing: 1.3px;
		-webkit-transition: 0.2s all;
		transition: 0.2s all;
		cursor: pointer;
	}

	.booking-form .submit-btn:hover,
	.booking-form .submit-btn:focus {
		opacity: 0.9;
	}

	.row{
		display: table;
	    width: 100%; /*Optional*/
	    table-layout: fixed; /*Optional*/
	    border-spacing: 10px; /*Optional*/
	}

	#depText {
		color: #e35e0a;
		margin-bottom: 30px;
		cursor: pointer;
		margin-left: 20px;
		font-size: 20px;
	}
	.form-btn {
		margin-top: 20px;
	}
	#removeBtn {
		margin-top: 20px;
		background: red;
		border-radius: 50%;
		outline: 0;
		color: white;
		border-width: 0;
		height: 30px;
		width: 30px;
		cursor: pointer;
	}
	#totalAmount {
		color: white;
		text-align: center;
		margin: 20px;
	}
	.timer {
		color: white;
		text-align: center;
	}
	#warning {
		color: red;
		text-align: center;
		margin: 20px;
	}
</style>


<body>
<?php include "include/navigation.php" ?>
	<div id="booking" class="section">
	
		<div class="section-center">
			<div class="container">
				<div class="row">
					<div class="booking-form">
						<div class="form-header">
							<h1>Make your reservation</h1>
						</div>
						<form action="" method="post" enctype="multipart/form-data" id="confirm_form">
							<div class="form-group">	
								<input class="form-control" type="text" placeholder="First Name" name="f_name" required>
							</div>
							<div class="form-group">
								<input class="form-control" type="text" placeholder="Last Name" name="l_name" required>	
							</div>
							<div class="row">
								<div class="form-group" style="margin-top:10px">
									<input class="form-control" type="date" name="dob" required>
									<span class="form-label">Date of birth</span>
									<input class="form-control" type="text" placeholder="Country" name="country" required>
								</div>
							</div>
							<div class="row">						
								<div class="form-group">
									<input class="form-control" type="email" placeholder="Enter your Email" name="cust_email" required>
									<span class="form-label">Email</span>				
									<input class="form-control" type="text" placeholder="Passport Number" name="passport_no" required>
								</div>
							</div>
							<div class="row">
							<div class="form-group" style="margin-top:0px">
										<input class="form-control" type="text" placeholder="Enter your Phone" name="cust_phone" minlength="10" maxlength="10"  required>
									<select class="form-control" style= "width:250px; height: 60px;" name="payment_type" required>
										<option value="" selected hidden>Payment Method</option>
										<option>Cash</option>
										<option>Card</option>
										<option>Net Banking</option>
									</select>	
								</div>
							</div>				
							<div onclick="addDep()" id="depText">Add Dependent +</div>

							<div class="form-btn">
								<input type="submit" class="submit-btn" name="add_booking" value="Book Now">
							</div>
							<h2 id="totalAmount"><?php echo "Total Amount- &#x20B9 {$total_amt}" ?></h2>
							<div class="timer">
   								<time id="countdown">Session will expire in 5:00</time>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="js/jquery.min.js"></script>
	<script>
		$('.form-control').each(function () {
			floatedLabel($(this));
		});

		$('.form-control').on('input', function () {
			floatedLabel($(this));
		});

		function floatedLabel(input) {
			var $field = input.closest('.form-group');
			if (input.val()) {
				$field.addClass('input-not-empty');
			} else {
				$field.removeClass('input-not-empty');
			}
		}
	</script>
	<?php include "include/footer.php" ?>
</body>
<html>
<script src="js/jquery.min.js"></script>
	<script>
		$('.form-control').each(function () {
			floatedLabel($(this));
		});

		$('.form-control').on('input', function () {
			floatedLabel($(this));
		});

		function floatedLabel(input) {
			var $field = input.closest('.form-group');
			if (input.val()) {
				$field.addClass('input-not-empty');
			} else {
				$field.removeClass('input-not-empty');
			}
		}
	</script>
	<script type="text/javascript">
		var seconds = 300;
	    function secondPassed() {
	        var minutes = Math.round((seconds - 30)/60),
	        remainingSeconds = seconds % 60;
	        if (remainingSeconds < 10) {
	          remainingSeconds = "0" + remainingSeconds;
	        }
	        document.getElementById('countdown').innerHTML ="Session will expire in " + minutes + ":" + remainingSeconds;
	        if (seconds == 0) {
	          clearInterval(countdownTimer);
	          window.location="search.php";
	        } 
	        else {
	          seconds--;
	        } 
	    } 
	    var countdownTimer = setInterval('secondPassed()', 1000); 

	    function addDep(e) {
    		var container = document.getElementById("confirm_form");
            var dep_name = document.createElement("input");
            var dep_id = document.createElement("input");
            dep_name.type = "text";
            dep_name.setAttribute("name", "dep_name");
            dep_id.type = "text";
            dep_id.setAttribute("name", "dep_id");
            dep_name.classList.add("form-control");
            dep_name.classList.add("dynamic_dep");
            dep_name.placeholder="Dependent Name";
            dep_id.classList.add("form-control");
            dep_id.classList.add("dynamic_dep");
            dep_id.placeholder="Passport Number";
            var remove = document.createElement('button');
			remove.setAttribute('id', 'removeBtn');
			remove.innerHTML="&#8213;"
			remove.onclick = function(e) {
  				removeElement(e)
			};
            var pos = document.getElementsByClassName("form-btn")[0];
            container.insertBefore(dep_name, pos);
            container.insertBefore(dep_id, pos);
            container.insertBefore(remove, pos);
            console.log(dep_name)
            document.getElementById("depText").style.display="none";	
		}


		function removeElement(ev) {
			var container = document.getElementById("confirm_form");
		    var remove = ev.target;
		    var dep_name = document.getElementsByName("dep_name")[0];
		    var dep_id = document.getElementsByName("dep_id")[0];
		    remove.style.display="none";
		    container.removeChild(dep_name);
		    container.removeChild(dep_id);
		    container.removeChild(remove);
		    document.getElementById("depText").style.display="block";
	  	}
	</script>




<!--
	<div id="booking" class="section">
		<div class="section-center">
		<div class="container">
				
					<div class="booking-form">
						<div class="form-header">
							<h1>Make your reservation</h1>
						</div>
<form action="" method="post" enctype="multipart/form-data">
<div class="row">
	<div class="form-group">
		<label for="f_name">First Name</label>
		<input type="text" va class="form-control" name="f_name" required>
	</div>
	<div class="form-group">
		<label for="l_name">Last Name</label>
		<input type="text" class="form-control" name="l_name" required>
	</div>
</div>
	
	<div class="form-group">
		<label for="cust_email">Email</label>
		<input type="email" class="form-control" name="cust_email" required>
	</div>
	<div class="form-group">
		<label for="cust_phone">Phone</label>
		<input type="text" class="form-control" name="cust_phone" minlength="10" maxlength="10" required>
	</div>
	<div class="form-group">
		<label for="country">Country</label>
		<input type="text" class="form-control" name="country" required>
	</div>
	<div class="form-group">
		<label for="dob">DOB</label>
		<input type="date" class="form-control" name="dob" required>
	</div>
	<div class="form-group">
		<label for="passport_no">Passport Number</label>
		<input type="text" class="form-control" name="passport_no" required>
	</div>
	<label for="payment_type">Payment Method</label>
	<select name="payment_type">
		<option value="cash">Cash</option>
		<option value="card">Card</option>
		<option value="netbanking">Netbanking</option>
	</select>


	<div class="form-btn">
								<button class="submit-btn">Book Now</button>
							</div>
-->