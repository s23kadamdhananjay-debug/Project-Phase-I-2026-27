<?php
include ('fsession.php');
ini_set('memory_limit', '-1');

if(!isset($_SESSION['farmer_login_user'])){
header("location: ../index.php");} // Redirecting To Home Page
$query4 = "SELECT * from farmerlogin where email='$user_check'";
              $ses_sq4 = mysqli_query($conn, $query4);
              $row4 = mysqli_fetch_assoc($ses_sq4);
              $para1 = $row4['farmer_id'];
              $para2 = $row4['farmer_name'];
			  
?>

<!DOCTYPE html>
<html>
<?php include ('fheader.php');  ?>

  <body class="bg-white" id="top">
  
<?php include ('fnav.php');  ?>

 
  <section class="section section-shaped section-lg">
    <div class="shape shape-style-1 shape-primary">
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
    </div>
<!-- ======================================================================================================================================== -->

<div class="container-fluid ">
    
    	 <div class="row">
          <div class="col-md-8 mx-auto text-center">
            <span class="badge badge-danger badge-pill mb-3">Recommendation</span>
          </div>
        </div>
		
          <div class="row row-content">
            <div class="col-md-12 mb-3">

				<div class="card text-white bg-gradient-success mb-3">
				<form role="form" action="#" method="post" >  
				  <div class="card-header">
				  <span class=" text-info display-4" > Crop Recommendation  </span>	
						<span class="pull-right">
							<button type="submit" value="Recommend" name="Crop_Recommend" class="btn btn-warning btn-submit">SUBMIT</button>
						</span>		
				  
				  </div>

				  <div class="card-body text-dark">
				     <form role="form" action="#" method="post" >     
					 
				<table class="table table-striped table-hover table-bordered bg-gradient-white text-center display" id="myTable">

    <thead>
					<tr class="font-weight-bold text-default">
					<th><center> Nitrogen</center></th>
					<th><center>Phosporous</center></th>
					<th><center>Potasioum</center></th>
					<th><center>Temparature</center></th>
					<th><center>Humidity</center></th>
					<th><center>PH</center></th>
					<th><center>Rainfall</center></th>
					
        </tr>
    </thead>
 <tbody>
                                 <tr class="text-center">
                                    <td>
                                    	<div class="form-group">
											<input type = 'number' name = 'n' placeholder="Nitrogen Eg:90" required class="form-control">
											
										</div>
                                    </td>
									
									<td>
                                    	<div class="form-group">
											<input type = 'number' name = 'p' placeholder="Phosphorus Eg:42" required class="form-control">
											
										</div>
                                    </td>
									
									<td>
                                    	<div class="form-group">
											<input type = 'number' name = 'k' placeholder="Pottasium Eg:43" required class="form-control">
											
										</div>
                                    </td>
									
									<td>
                                    	<div class="form-group">
											<input type = 'number' name = 't' step =0.01 placeholder="Temperature Eg:21" required class="form-control">
											
										</div>
                                    </td>
									
									<td>
                                    	<div class="form-group">
											<input type = 'number' name = 'h' step =0.01 placeholder="Humidity Eg:82" required class="form-control">
											
										</div>
                                    </td>
									
									<td>
                                    	<div class="form-group">
											<input type = 'number' name = 'ph' step =0.01 placeholder="PH Eg:6.5" required class="form-control">
											
										</div>
                                    </td>
									
									<td>
                                    	<div class="form-group">
											 <input type = 'number' name = 'r' step =0.01 placeholder="Rainfall Eg:203" required class="form-control">
											
										</div>
                                    </td>
                                </tr>
                            </tbody>
							
					
	</table>
	</form>
</div>
</div>



<div class="card text-white bg-gradient-success mb-3">
				  <div class="card-header">
				  <span class=" text-success display-4" > Result  </span>					
				  </div>

					<h4>
					<?php 
					if(isset($_POST['Crop_Recommend'])){
					require_once('ml_runner.php');

					$n=isset($_POST['n']) ? trim($_POST['n']) : '';
					$p=isset($_POST['p']) ? trim($_POST['p']) : '';
					$k=isset($_POST['k']) ? trim($_POST['k']) : '';
					$t=isset($_POST['t']) ? trim($_POST['t']) : '';
					$h=isset($_POST['h']) ? trim($_POST['h']) : '';
					$ph=isset($_POST['ph']) ? trim($_POST['ph']) : '';
					$r=isset($_POST['r']) ? trim($_POST['r']) : '';

					$values = array($n, $p, $k, $t, $h, $ph, $r);
					$invalid = false;
					foreach ($values as $value) {
						if ($value === '' || !is_numeric($value)) {
							$invalid = true;
							break;
						}
					}

					if ($invalid) {
						echo 'Enter valid numeric values for N, P, K, temperature, humidity, pH and rainfall.';
					} else {
						echo 'Recommended Crop is: ';
						$error = null;
						$output = agri_run_ml('ML/crop_recommendation/recommend.py', $values, $error);
						if ($output === null) {
							echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
						} else {
							echo htmlspecialchars(ucfirst($output), ENT_QUOTES, 'UTF-8');
						}
					}
					}
                    ?>
					</h4>
            </div>
 
	
	
            </div>
          </div>  
       </div>
		 
</section>

    <?php require("footer.php");?>

</body>
</html>

