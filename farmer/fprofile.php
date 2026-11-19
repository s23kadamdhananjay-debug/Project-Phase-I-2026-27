<?php
include ('fsession.php');
ini_set('memory_limit', '-1');

if (!isset($_SESSION['farmer_login_user'])) {
    header('Location: ../index.php');
    exit();
}

$profileStmt = $conn->prepare(
    'SELECT farmer_id, farmer_name, email, phone_no, F_gender, F_birthday, F_State, F_District, F_Location
     FROM farmerlogin WHERE email = ? LIMIT 1'
);
if (!$profileStmt) {
    http_response_code(500);
    exit('Unable to load farmer profile.');
}
$profileStmt->bind_param('s', $user_check);
if (!$profileStmt->execute()) {
    $profileStmt->close();
    http_response_code(500);
    exit('Unable to load farmer profile.');
}
$profileStmt->bind_result($para1, $para2, $para5, $para6, $para7, $para8, $para9, $para10, $para11);
if (!$profileStmt->fetch()) {
    $profileStmt->close();
    http_response_code(404);
    exit('Farmer profile was not found.');
}
$profileStmt->close();

$profileError = '';
$passwordError = '';
$passwordSuccess = !empty($_SESSION['farmer_profile_password_changed']);
unset($_SESSION['farmer_profile_password_changed']);

if (isset($_POST['change_password'])) {
    $currentPassword = isset($_POST['current_password']) && is_string($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) && is_string($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $passwordError = 'Complete all password fields.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $newPassword)) {
        $passwordError = 'New password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = 'The new password and confirmation do not match.';
    } else {
        $passwordStmt = $conn->prepare('SELECT password FROM farmerlogin WHERE farmer_id = ? LIMIT 1');
        if (!$passwordStmt) {
            $passwordError = 'Unable to verify your current password right now. Please try again.';
        } else {
            $passwordStmt->bind_param('i', $para1);
            if (!$passwordStmt->execute()) {
                $passwordStmt->close();
                $passwordError = 'Unable to verify your current password right now. Please try again.';
            } else {
                $passwordStmt->bind_result($storedPassword);
                $passwordFound = $passwordStmt->fetch();
                $passwordStmt->close();

                if (!$passwordFound || !password_verify($currentPassword, $storedPassword)) {
                    $passwordError = 'The current password is incorrect.';
                } else {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    if ($newPasswordHash === false) {
                        $passwordError = 'Unable to change your password right now. Please try again.';
                    } else {
                        $updatePasswordStmt = $conn->prepare('UPDATE farmerlogin SET password = ? WHERE farmer_id = ?');
                        if ($updatePasswordStmt) {
                            $updatePasswordStmt->bind_param('si', $newPasswordHash, $para1);
                            $passwordUpdated = $updatePasswordStmt->execute();
                            $updatePasswordStmt->close();
                        } else {
                            $passwordUpdated = false;
                        }

                        if ($passwordUpdated) {
                            $_SESSION['farmer_profile_password_changed'] = true;
                            header('Location: fprofile.php');
                            exit();
                        }
                        $passwordError = 'Unable to change your password right now. Please try again.';
                    }
                }
            }
        }
    }
}

if (isset($_POST['farmerupdate'])) {
    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) && is_string($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $gender = isset($_POST['gender']) && is_string($_POST['gender']) ? trim($_POST['gender']) : '';
    $dob = isset($_POST['dob']) && is_string($_POST['dob']) ? trim($_POST['dob']) : '';
    $state = isset($_POST['state']) && is_string($_POST['state']) ? trim($_POST['state']) : '';
    $district = isset($_POST['district']) && is_string($_POST['district']) ? trim($_POST['district']) : '';
    $city = isset($_POST['city']) && is_string($_POST['city']) ? trim($_POST['city']) : '';
    if (ctype_digit($state)) {
        $stateCode = (int)$state;
        $stateStmt = $conn->prepare('SELECT StateName FROM state WHERE StCode = ? LIMIT 1');
        if ($stateStmt) {
            $stateStmt->bind_param('i', $stateCode);
            if ($stateStmt->execute()) {
                $stateStmt->bind_result($statename);
                if (!$stateStmt->fetch()) {
                    $statename = null;
                }
            } else {
                $statename = null;
            }
            $stateStmt->close();
        } else {
            $statename = null;
        }

        if ($statename === null) {
            $profileError = 'Please select a valid state.';
        } else {
            $updateStmt = $conn->prepare(
                'UPDATE farmerlogin
                 SET farmer_name = ?, email = ?, phone_no = ?, F_gender = ?, F_birthday = ?,
                     F_State = ?, F_District = ?, F_Location = ?
                 WHERE farmer_id = ?'
            );
            if ($updateStmt) {
                $updateStmt->bind_param(
                    'ssssssssi',
                    $name,
                    $email,
                    $mobile,
                    $gender,
                    $dob,
                    $statename,
                    $district,
                    $city,
                    $para1
                );
                $updated = $updateStmt->execute();
                $updateStmt->close();
            } else {
                $updated = false;
            }

            if ($updated) {
                $_SESSION['farmer_login_user'] = $email;
                header('Location: fprofile.php');
                exit();
            }
            $profileError = 'Unable to update your profile right now. Please try again.';
        }
    } elseif ($profileError === '') {
        $profileError = 'Please select a valid state.';
    }
}
?>

<!DOCTYPE html>
<html>
<?php $farmerProfilePageStyles = true; include ('fheader.php'); ?>


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

    <div class="container">
      <div class="row">
        <div class="col-md-8 mx-auto text-center">
          <span class="badge badge-danger badge-pill mb-3">Profile</span>
        </div>
      </div>

      <?php if ($profileError !== ''): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($passwordSuccess): ?>
        <div class="alert alert-success" role="status">Your password has been changed.</div>
      <?php endif; ?>
      <?php if ($passwordError !== ''): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($passwordError, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <div class="row row-content">
        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body bg-gradient-warning">
              <div class="d-flex flex-column align-items-center text-center">
                <img src="../assets/img/farmers.png" alt="Farmer" class="rounded-circle img-fluid" width="202">
                <div class="mt-3">
                  <h4>Welcome <?php echo htmlspecialchars((string)$para2, ENT_QUOTES, 'UTF-8'); ?></h4>
                  <button type="button" data-toggle="modal" data-target="#edit" class="btn btn-danger">Edit Profile</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="card mb-3">
            <div class="card-body bg-gradient-success">
              <dl class="farmer-profile-info">
                <div class="farmer-profile-row">
                  <dt>Farmer ID</dt>
                  <dd><?php echo htmlspecialchars((string)$para1, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>Farmer Name</dt>
                  <dd><?php echo htmlspecialchars((string)$para2, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>Email Address</dt>
                  <dd><?php echo htmlspecialchars((string)$para5, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>Mobile No</dt>
                  <dd><?php echo htmlspecialchars((string)$para6, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>Gender</dt>
                  <dd><?php echo htmlspecialchars((string)$para7, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>DOB</dt>
                  <dd><?php echo htmlspecialchars((string)$para8, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>State</dt>
                  <dd><?php echo htmlspecialchars((string)$para9, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>District</dt>
                  <dd><?php echo htmlspecialchars((string)$para10, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div class="farmer-profile-row">
                  <dt>City</dt>
                  <dd><?php echo htmlspecialchars((string)$para11, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
              </dl>
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
              <?php if ($profileError !== ''): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($profileError, ENT_QUOTES, 'UTF-8'); ?></div>
              <?php endif; ?>
			
              <div class="form-group row">
                <label
                  for="name"
                  class="col-md-3 col-form-label text-white"
                  >Farmer ID</label
                >
                <div class="col-md-9">
                  <input
				  name="id"
                    class="form-control "
                    value="<?php echo htmlspecialchars((string)$para1, ENT_QUOTES, 'UTF-8'); ?>"
                    readonly
                  />
                </div>
              </div>
			  
			   <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Farmer Name </label>
                <div class="col-md-9">
                  <input class="form-control" type="text" name="name" value="<?php echo htmlspecialchars((string)$para2, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>				
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Email Address </label>
                <div class="col-md-9">
                  <input class="form-control" type="email" name="email" value="<?php echo htmlspecialchars((string)$para5, ENT_QUOTES, 'UTF-8'); ?>" readonly />
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > Mobile No </label>
                <div class="col-md-9">
                  <input class="form-control" type="tel" name="mobile" value="<?php echo htmlspecialchars((string)$para6, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
              </div>
			  
			  
			    <div class="form-group row">
                <label class="col-md-3 col-form-label text-white" for="email"
                  >Gender </label
                >
                <div class=" col-md-9">
                  <select class=" col-md-12 font-weight-bold form-control" name="gender"  >
               <option value="<?php echo htmlspecialchars((string)$para7, ENT_QUOTES, 'UTF-8'); ?>" selected hidden><?php echo htmlspecialchars((string)$para7, ENT_QUOTES, 'UTF-8'); ?></option>
			   <option value="Male">Male</option>
			   <option value="Female">Female</option>
			   </select>  
			   </div>
              </div>
			  
			  
			  
			  				  
			      <div class="form-group row">
                <label class="col-md-3 col-form-label text-white" for="dob"
                  >DOB</label
                >
                <div class="col-md-9">
                  <input
                    class=" form-control"
                    name="dob"
                    type="date"
                    value="<?php echo htmlspecialchars((string)$para8, ENT_QUOTES, 'UTF-8'); ?>"
                  />
                </div>
              </div>
			  
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > State</label>
                <div class="col-md-9">
				<select onChange="getdistrict(this.value);"  name="state" id="state" class="form-control" >
                    <option value=""><?php echo htmlspecialchars((string)$para9, ENT_QUOTES, 'UTF-8'); ?></option>
                   	<?php $query =mysqli_query($conn, "SELECT StCode, StateName FROM state");
					while($row=mysqli_fetch_assoc($query))
					{ ?>
					<option value="<?php echo (int)$row['StCode'];?>"><?php echo htmlspecialchars($row['StateName'], ENT_QUOTES, 'UTF-8');?></option>
					<?php
					}
					?>
                    </select>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > District </label>
                <div class="col-md-9">
                 <select  name="district" id="district-list" class="form-control">
				<option value=""><?php echo htmlspecialchars((string)$para10, ENT_QUOTES, 'UTF-8'); ?></option>
				</select>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" > City </label>
                <div class="col-md-9">
                  <input class="form-control" type="text" name="city" value="<?php echo htmlspecialchars((string)$para11, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
              </div>
			  
			   <div class="form-group row">
                <label for="staffid" class="col-md-3 col-form-label text-white" >  </label>
                <div class="col-md-9">
            <button name="farmerupdate" class=" btn-block btn btn-success"><span class="glyphicon glyphicon-edit"></span> Submit</button>		
                </div>		
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
 
  
			
  <div id="changePassword" class="modal fade" role="dialog" aria-labelledby="change-password-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="change-password-title">Change Password</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <form method="POST" autocomplete="off">
            <?php if ($passwordError !== ''): ?>
              <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($passwordError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <div class="form-group">
              <label for="current-password">Current password</label>
              <input id="current-password" class="form-control" type="password" name="current_password" autocomplete="current-password" required />
            </div>
            <div class="form-group">
              <label for="new-password">New password</label>
              <input id="new-password" class="form-control" type="password" name="new_password" autocomplete="new-password" minlength="8" required />
              <small class="form-text text-muted">Use at least 8 characters with uppercase, lowercase, a number, and a special character.</small>
            </div>
            <div class="form-group">
              <label for="confirm-password">Confirm new password</label>
              <input id="confirm-password" class="form-control" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required />
            </div>
            <button name="change_password" value="1" class="btn btn-success btn-block" type="submit">Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

   
<?php include ('footer.php');  ?>

</body>
  <script>
  $(function () {
    <?php if ($profileError !== ''): ?>
      $('#edit').modal('show');
    <?php endif; ?>
    <?php if ($passwordError !== ''): ?>
      $('#changePassword').modal('show');
    <?php endif; ?>
  });
</script>



		  <script>
function getdistrict(val) {
	$.ajax({
	type: "POST",
	url: "fget_district.php",
	data:'state_id='+val,
	success: function(data){
		$("#district-list").html(data);
	}
	});
}

</script>	
</html>