<?php
include ('fsession.php');
ini_set('memory_limit', '-1');

if(!isset($_SESSION['farmer_login_user'])){
    header("location: ../index.php");
}

$query4 = "SELECT * from farmerlogin where email='$user_check'";
$ses_sq4 = mysqli_query($conn, $query4);
$row4 = mysqli_fetch_assoc($ses_sq4);
$para1 = $row4['farmer_id'];
$para2 = $row4['farmer_name'];

// ✅ UPDATED: Select from purchase_history
$sql = "SELECT crop_name, quantity, total_price, purchase_date FROM purchase_history WHERE farmer_id='$para1'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<?php include ('fheader.php');  ?>

<body class="bg-white" id="top">

<?php include ('fnav.php');  ?>
 	
<section class="section section-shaped section-lg">
  <div class="shape shape-style-1 shape-primary">
    <span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-md-8 mx-auto text-center">
        <span class="badge badge-danger badge-pill mb-3">Trade</span>
      </div>
    </div>
		
    <div class="row row-content">
      <div class="col-md-12 mb-3">
        <div class="card text-white bg-gradient-warning mb-3">
          <div class="card-header">
            <!-- ✅ UPDATED: Heading -->
            <span class="text-warning display-4">Purchase History</span>
          </div>

          <div class="card-body text-dark">
            <form role="form" onsubmit="return tradecrops()" id="sellcrops" action="ftradecropsScript.php" method="POST">                                            
              <table class="table table-striped table-hover table-bordered bg-gradient-white text-center display" id="myTable">
                <thead>
                  <th><center>Crop</center></th>
                  <th><center>Quantity (in KG)</center></th>
                  <th><center>Total Amount Paid (in Rs)</center></th>
                  <th><center>Date of Purchase</center></th>
                </thead>
                <tbody>

<?php  
while($row = $result->fetch_assoc()) {
    $cropname = ucfirst($row["crop_name"]);
    $cropquantity = $row["quantity"];
    $cropprice = $row["total_price"];
    $currentdate = $row['purchase_date'];

    echo "<tr>";
    echo "<td><center>$cropname</center></td>";
    echo "<td><center>$cropquantity</center></td>";
    echo "<td><center>$cropprice</center></td>";
    echo "<td><center>$currentdate</center></td>";
    echo "</tr>";
}
?>
                </tbody>
              </table> 
            </form>
          </div>
        </div>
      </div>
    </div>  
  </div>
</section>

<?php require("footer.php"); ?>
<script>
  $(document).ready(function () {
    $('#myTable').DataTable();
  });
</script>
</body>
</html>