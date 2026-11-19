<?php
include ('csession.php');
ini_set('memory_limit', '-1');

$customerId = (int)$_SESSION['customer_id'];
$profileError = '';
$profileSuccess = isset($_GET['updated']) && $_GET['updated'] === '1';

$profileStmt = $conn->prepare(
    'SELECT cust_name, email, phone_no, state, city, address, pincode
     FROM custlogin WHERE cust_id = ? LIMIT 1'
);
if (!$profileStmt) {
    http_response_code(500);
    exit('Unable to load customer profile.');
}
$profileStmt->bind_param('i', $customerId);
$profileStmt->execute();
$profileStmt->bind_result($para2, $para5, $para6, $para7, $para8, $para9, $customerId0);
if (!$profileStmt->fetch()) {
    $profileStmt->close();
    http_response_code(404);
    exit('Customer profile was not found.');
}
$profileStmt->close();

if (isset($_POST['custupdate'])) {
    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) && is_string($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $state = isset($_POST['state']) && is_string($_POST['state']) ? trim($_POST['state']) : '';
    $city = isset($_POST['city']) && is_string($_POST['city']) ? trim($_POST['city']) : '';
    $address = isset($_POST['address']) && is_string($_POST['address']) ? trim($_POST['address']) : '';
    $pincode = isset($_POST['pincode']) && is_string($_POST['pincode']) ? trim($_POST['pincode']) : '';
    $newPassword = isset($_POST['new_password']) && is_string($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($name === '' || !preg_match('/^[a-zA-Z\s.]{2,}$/', $name)) {
        $profileError = 'Enter a valid name (letters and spaces only).';
    } elseif (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $profileError = 'Enter a valid 10 digit mobile number.';
    } elseif (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
        $profileError = 'Enter a valid 6 digit pincode.';
    } elseif ($address === '' || strlen($address) < 5) {
        $profileError = 'Enter a complete address.';
    } elseif (!ctype_digit($state)) {
        $profileError = 'Select a valid state.';
    } elseif ($city === '') {
        $profileError = 'City / district is required.';
    } elseif ($newPassword !== '' && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
        $profileError = 'The new password must be at least 8 characters and include upper case, lower case, a number and a special character.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $profileError = 'The new password and confirmation do not match.';
    }

    if ($profileError === '') {
        $stateCode = (int)$state;
        $stateStmt = $conn->prepare('SELECT StateName FROM state WHERE StCode = ? LIMIT 1');
        $stateName = null;
        if ($stateStmt) {
            $stateStmt->bind_param('i', $stateCode);
            $stateStmt->execute();
            $stateStmt->bind_result($stateNameRow);
            if ($stateStmt->fetch()) {
                $stateName = $stateNameRow;
            }
            $stateStmt->close();
        }

        if ($stateName === null) {
            $profileError = 'Select a valid state.';
        } else {
            $update = $conn->prepare(
                'UPDATE custlogin
                 SET cust_name = ?, phone_no = ?, state = ?, city = ?, address = ?, pincode = ?
                 WHERE cust_id = ?'
            );
            if (!$update) {
                $profileError = 'Unable to update your profile right now.';
            } else {
                $update->bind_param('ssssssi', $name, $mobile, $stateName, $city, $address, $pincode, $customerId);
                $updated = $update->execute();
                $update->close();

                if ($updated && $newPassword !== '') {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    if ($hash === false) {
                        $profileError = 'Unable to update your password right now.';
                    } else {
                        $passStmt = $conn->prepare('UPDATE custlogin SET password = ? WHERE cust_id = ?');
                        if ($passStmt) {
                            $passStmt->bind_param('si', $hash, $customerId);
                            $passStmt->execute();
                            $passStmt->close();
                        }
                    }
                }

                if ($updated && $profileError === '') {
                    header('Location: cprofile.php?updated=1');
                    exit();
                }
                if ($profileError === '') {
                    $profileError = 'Unable to update your profile right now.';
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<?php include ('cheader.php');  ?>

  <body class="bg-white" id="top">
  
<?php include ('cnav.php');  ?>
 	
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

<div class="container ">
    
    	 <div class="row">
          <div class="col-md-8 mx-auto text-center">
            <span class="badge badge-danger badge-pill mb-3">Profile</span>
            <?php if ($profileSuccess): ?>
              <div class="alert alert-success" role="status">Profile updated successfully.</div>
            <?php endif; ?>
            <?php if ($profileError !== ''): ?>
              <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
          </div>
        </div>
		
          <div class="row row-content">
            <div class="col-md-4 mb-3">
			
			
				<div class="card">
                <div class="card-body bg-gradient-warning">
                  <div class="d-flex flex-column align-items-center text-center">
                    <img src="../assets/img/customers.png" alt="student" class="rounded-circle img-fluid" width="202px">
                    <div class="mt-3">
                      <h4>                Welcome     <?php echo $login_session ?></h4>
                      		  <button data-toggle="modal" data-target="#edit" class="btn btn-danger">Edit Profile</button>


                    </div>
                  </div>
                </div>
              </div>			 		  
            </div>
			
			
                <div class="col-md-8">
              <div class="card mb-3">
                <div class="card-body bg-gradient-success">
				
                  <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Customer ID</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $customerId ?>
                    </div>
                  </div>
				
                  
				  
                  <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Customer Name</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $para2 ?>
                    </div>
                  </div>
              
				  
                  <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Email Address</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $para5 ?>
                    </div>
                  </div>
                 
				   
				    <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Mobile No</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $para6 ?>
                    </div>
                  </div>
               
				   
				       <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">State</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $para7 ?>
                    </div>
                  </div>
			  
				       <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">City</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $para8 ?>
                    </div>
                  </div>
				  
				     <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Address</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $para9 ?>
                    </div>
                  </div>
				  
				     <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Pincode</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      <?php echo $customerId0 ?>
                    </div>
                  </div>
				  
				  
				     <div class="row mb-1">
                    <div class="col-sm-3">
                      <h6 class="mb-0 font-weight-bold">Password</h6>
                    </div>
                    <div class="col-sm-9 text-dark">
                      ******** 
                    </div>
                  </div>
                   

				  
                </div>
              </div>
            </div>				

<!-- Edit Profile  Modal -->

    <div id="edit" class="modal fade" role="dialog">
      <div class="modal-dialog modal-lg" role="content">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Edit Profile</h4>
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
          </div>
          <div class="modal-body bg-gradient-danger">
		  
		  
            <form
              class="col s12 l5 white-text"
              method="POST"
              autocomplete="new-password"
			 
            >
			
              <div class="form-group row">
                <label
                  for="name"
                  class="col-md-3 col-form-label text-white"
                  >Customer ID</label
                >
                <div class="col-md-9">
                  <input
				  name="id"
                    class="form-control "
                    value="<?php echo "$customerId"?>"
                    readonly
                  />
                </div>
              </div>
			  
			   <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Customer Name </label>
                <div class="col-md-9">
                  <input class="form-control" type="text" name="name" value="<?php echo "$para2"?>" />
                </div>				
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Email Address </label>
                <div class="col-md-9">
                  <input class="form-control" type="email" name="email" value="<?php echo "$para5"?>" readonly />
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Mobile No </label>
                <div class="col-md-9">
                  <input class="form-control" type="number" min="0" name="mobile" value="<?php echo "$para6"?>" />
                </div>
              </div>
			  
			  
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > State</label>
                <div class="col-md-9">
                  <select onChange="getdistrict(this.value);"  name="state" id="state" class="form-control" >
                    <option value=""><?php echo "$para7"?></option>
                   	<?php $query =mysqli_query($conn,"SELECT * FROM state");
					while($row=mysqli_fetch_array($query))
					{ ?>
					<option value="<?php echo $row['StCode'];?>"><?php echo $row['StateName'];?></option>
					<?php
					}
					?>
                    </select>
                </div>
              </div>
			  
			    <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > City </label>
                <div class="col-md-9">
					<select  name="city" id="district-list" class="form-control">
				<option value=""><?php echo "$para8"?></option>
				</select>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Address </label>
                <div class="col-md-9">
                  <input class="form-control" type="text" name="address" value="<?php echo "$para9"?>" />
                </div>
              </div>
			  
			 <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Pincode </label>
                <div class="col-md-9">
                  <input class="form-control" type="number" min="0" min="6" name="pincode" value="<?php echo htmlspecialchars($para10, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
              </div>
			  
			  
			  		 <div class="form-group row">
                <label class="col-md-3 col-form-label text-white">New Password</label>
			   <div class="col-9">
            <div class="input-group mb-2">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1"><i class="fas fa-lock"></i></span>
              </div>
              <input name="new_password" type="password" class="input form-control" id="password" placeholder="Leave blank to keep current password" autocomplete="new-password" aria-label="new password" aria-describedby="basic-addon1" />
              <div class="input-group-append">
                <span class="input-group-text" onclick="password_show_hide();">
                  <i class="fas fa-eye" id="show_eye"></i>
                  <i class="fas fa-eye-slash d-none" id="hide_eye"></i>
                </span>
              </div>
            </div>
          </div>
		   </div>

		   <div class="form-group row">
                <label class="col-md-3 col-form-label text-white">Confirm Password</label>
			   <div class="col-9">
              <input name="confirm_password" type="password" class="input form-control" placeholder="Repeat new password" autocomplete="new-password" aria-label="confirm password" />
          </div>
		   </div>
			  
			  
			   <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" >  </label>
                <div class="col-md-9">
            <button name="custupdate" class=" btn-block btn btn-success"><span class="glyphicon glyphicon-edit"></span> Submit</button>		
                </div>		
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
 
  
			
          </div>
        </div>
		 
</section>
    <?php require("footer.php");?>
	
</body>
  <script>
  function password_show_hide() {
  var x = document.getElementById("password");
  var show_eye = document.getElementById("show_eye");
  var hide_eye = document.getElementById("hide_eye");
  hide_eye.classList.remove("d-none");
  if (x.type === "password") {
    x.type = "text";
    show_eye.style.display = "none";
    hide_eye.style.display = "block";
  } else {
    x.type = "password";
    show_eye.style.display = "block";
    hide_eye.style.display = "none";
  }
}
</script>

		  <script>
function getdistrict(val) {
	$.ajax({
	type: "POST",
	url: "cget_district.php",
	data:'state_id='+val,
	success: function(data){
		$("#district-list").html(data);
	}
	});
}

</script>	
</html>