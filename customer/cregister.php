<?php
include('cregisterScript.php'); // Includes Login Script
require_once("../sql.php");
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="../assets/img/logo.png" />
    <title>Agriculture Portal</title>

  <!--     Fonts and icons     -->
  
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
	integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" 
	integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
	
	<link rel="stylesheet" href=" https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/5.1.1/bootstrap-social.min.css "/>
	
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
  <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">
  
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />

 
  <link rel="stylesheet" href="../assets/css/creativetim.min.css" type="text/css">
  <script src="../assets/js/i18n.js" defer></script>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>


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
</head>

  <body class="bg-white" id="top" >
    <!-- Navbar -->
    <nav
      id="navbar-main"
      class="
        navbar navbar-main navbar-expand-lg
        bg-default
        navbar-light
        position-sticky
        top-0
        shadow
        py-0
      "
    >
      <div class="container">
        <ul class="navbar-nav navbar-nav-hover align-items-lg-center">
          <li class="nav-item dropdown">
            <a href="../index.php" class="navbar-brand mr-lg-5 text-white">
                               <img src="../assets/img/nav.png" />
            </a>
          </li>
        </ul>

        <button
          class="navbar-toggler bg-white"
          type="button"
          data-toggle="collapse"
          data-target="#navbar_global"
          aria-controls="navbar_global"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon text-white"></span>
        </button>
        <div class="navbar-collapse collapse bg-default" id="navbar_global">
          <div class="navbar-collapse-header">
            <div class="row">
              <div class="col-10 collapse-brand">
                <a href="../index.html">
                  <img src="../assets/img/nav.png" />
                </a>
              </div>
              <div class="col-2 collapse-close bg-danger">
                <button
                  type="button"
                  class="navbar-toggler"
                  data-toggle="collapse"
                  data-target="#navbar_global"
                  aria-controls="navbar_global"
                  aria-expanded="false"
                  aria-label="Toggle navigation"
                >
                  <span></span>
                  <span></span>
                </button>
              </div>
            </div>
          </div>

          <ul class="navbar-nav align-items-lg-center ml-auto">
		  
		   <li class="nav-item">
              <a href="../contact.php" class="nav-link">
                <span class="text-white nav-link-inner--text"
                  ><i class="text-white fas fa-address-card"></i> Contact</span
                >
              </a>
            </li>
			
			  <li class="nav-item">
			   <div class="dropdown show ">
		  <a class="nav-link dropdown-toggle text-success " href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                  <span class="text-success nav-link-inner--text"
                  ><i class="text-success fas fa-user-plus"></i> Sign Up</span
                >
		  </a>

		  <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
			<a class="dropdown-item" href="../farmer/fregister.php">Farmer</a>
			<a class="dropdown-item" href="cregister.php">Customer</a>
		  </div>
		</div>
			</li>
			
		  
		  	  <li class="nav-item">
			   <div class="dropdown show ">
		  <a class="nav-link dropdown-toggle text-white " href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                  <span class="text-white nav-link-inner--text"
                  ><i class="text-white fas fa-sign-in-alt"></i> Login</span
                >
		  </a>

		   <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
			<a class="dropdown-item" href="flogin.php">Farmer</a>
			<a class="dropdown-item" href="../customer/clogin.php">Customer</a>
			<a class="dropdown-item" href="../admin/alogin.php">Admin </a>
		  </div>
		</div>
			</li>
           

          
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->
 	
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

<div class="container">

 <div class="row">
          <div class="col-md-8 mx-auto text-center">
            <span class="badge badge-info badge-pill mb-3">Register</span>
          </div>
        </div> 
      
<div class="row">
<div class="col-sm-12 mb-3">  
			  
  <div class="nav nav-tabs nav-fill bg-gradient-default" id="nav-tab" role="tablist">
    <a class="nav-item nav-link active font-weight-bold text-warning" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">User Singup</a>

  </div>
                  
                		  
      <div class="tab-content py-3 px-3 px-sm-0 bg-gradient-inf" id="nav-tabContent">

 
         <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
           <div class="card card-body bg-gradient-warning">
 


<form name="insert" id="customerRegisterForm" action="" method="post" novalidate>	
		
		  <div id="success"> <?php echo $error ?? ''; ?>    </div>
		 <script>		
			$("#success").fadeTo(2000, 500).slideUp(500, function(){
    $("#success").slideUp(500);
});
 </script>

			   <div class="form-group row">
                <label for="custName" class="col-md-3 col-form-label text-white">Customer Name <strong class="text-default">*</strong></label>
                <div class="col-md-9">
                  <input class="form-control" type="text" id="custName" name="name" placeholder="Enter your full name" />
                  <div class="invalid-feedback" id="nameError"></div>
                </div>				
              </div>
			  
			  <div class="form-group row">
                <label for="custEmail" class="col-md-3 col-form-label text-white">Email Address <strong class="text-default">*</strong></label>
                <div class="col-md-9">
                  <input class="form-control" type="email" id="custEmail" name="email" placeholder="Enter your email address" />
                  <div class="invalid-feedback" id="emailError"></div>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="custMobile" class="col-md-3 col-form-label text-white">Mobile No <strong class="text-default">*</strong></label>
                <div class="col-md-9">
                  <input class="form-control" type="text" id="custMobile" name="mobile" placeholder="10-digit mobile number" maxlength="10" />
                  <div class="invalid-feedback" id="mobileError"></div>
                </div>
              </div>
			  
			  
			  
			  <div class="form-group row">
                <label for="state" class="col-md-3 col-form-label text-white">State <strong class="text-default">*</strong></label>
                <div class="col-md-9">
                 <select onChange="getdistrict(this.value);" name="state" id="state" class="form-control">
                    <option value="">Select State</option>
                   	<?php $query =mysqli_query($conn,"SELECT * FROM state");
					while($row=mysqli_fetch_array($query))
					{ ?>
					<option value="<?php echo $row['StCode'];?>"><?php echo $row['StateName'];?></option>
					<?php } ?>
                    </select>
                  <div class="invalid-feedback" id="stateError"></div>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="district-list" class="col-md-3 col-form-label text-white">City <strong class="text-default">*</strong></label>
                <div class="col-md-9">
	              <select name="city" id="district-list" class="form-control">
                    <option value="">Select City</option>
                  </select>
                  <div class="invalid-feedback" id="cityError"></div>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="custAddress" class="col-md-3 col-form-label text-white">Address <strong class="text-default">*</strong></label>
                <div class="col-md-9">
                  <input class="form-control" type="text" id="custAddress" name="address" placeholder="Enter your full address" />
                  <div class="invalid-feedback" id="addressError"></div>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="custPincode" class="col-md-3 col-form-label text-white">Pincode <strong class="text-default">*</strong></label>
                <div class="col-md-9">
                  <input class="form-control" type="text" id="custPincode" name="pincode" placeholder="6-digit pincode" maxlength="6" />
                  <div class="invalid-feedback" id="pincodeError"></div>
                </div>
              </div>
			  
			  <div class="form-group row">
                <label for="password" class="col-md-3 col-form-label text-white">Password <strong class="text-default">*</strong></label>
			    <div class="col-9">
                  <div class="input-group mb-1">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input name="password" type="password" class="form-control" id="password" placeholder="Enter password" aria-label="password" />
                    <div class="input-group-append">
                      <span class="input-group-text" onclick="password_show_hide();" style="cursor:pointer;">
                        <i class="fas fa-eye" id="show_eye"></i>
                        <i class="fas fa-eye-slash d-none" id="hide_eye"></i>
                      </span>
                    </div>
                  </div>
                  <div class="invalid-feedback d-block" id="passwordError"></div>
                  <!-- Password strength bar -->
                  <div class="progress mt-1" style="height:6px;">
                    <div class="progress-bar" id="strengthBar" role="progressbar" style="width:0%"></div>
                  </div>
                  <small class="text-white d-block mt-1" id="strengthText"></small>
                  <small class="text-white d-block">Min 8 characters with uppercase, lowercase, number &amp; special character.</small>
			    </div>
		      </div>
	
			  <div class="form-group row">
                <label for="cpassword" class="col-md-3 col-form-label text-white">Confirm Password <strong class="text-default">*</strong></label>
			    <div class="col-9">
                  <div class="input-group mb-1">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input name="confirmpassword" type="password" class="form-control" id="cpassword" placeholder="Re-enter password" aria-label="confirmpassword" />
                    <div class="input-group-append">
                      <span class="input-group-text" onclick="password_show_hide2();" style="cursor:pointer;">
                        <i class="fas fa-eye" id="show_eye2"></i>
                        <i class="fas fa-eye-slash d-none" id="hide_eye2"></i>
                      </span>
                    </div>
                  </div>
                  <div class="invalid-feedback d-block" id="cpasswordError"></div>
			    </div>
		      </div>
		   
			  
			   <div class="form-group row">
                <label class="col-md-3 col-form-label text-white"></label>
                <div class="col-md-9">
                  <button type="submit" name="customerregister" class="btn-block btn btn-success">
                    <i class="fas fa-user-plus"></i> Register
                  </button>
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

	 <footer class="footer">
        <div class="container">
                    <div class="row">         
                <div class="col-4 col-md-2   align-self-center">				
                    <img class="align-text-center img-fluid"  src="../assets/img/footer-logo.png" />               
                </div>				
                <div class="offset-sm-2 col-5 ">
                    <h5>Our Address</h5>
                    <address>
		              Bramhanand Nagar, Kalhali<br>
		              Nanded, Maharashtra<br>		              
		              <div class="btn-group text-center" role="group">
                    <a role="button" class="btn btn-primary" href="tel:+7420962566"><i class="fa fa-phone"></i> Call</a>
                    <a role="button" class="btn btn-info" href="tel:+918233294006"> <i class="fa fa-fax"></i> Fax</a>
                    <a role="button" class="btn btn-success" href="mailto:info@sode-edu.in"> <i class="fa fa-envelope-o"></i> Email</a>
                </div>
		           </address>
                </div>				
                <div class="col-12 col-md-3 align-self-center">
            <div class="social-network  text-center">
				           
       <button target="_blank" href="/" rel="nofollow" class="btn btn-icon-only btn-linkedin rounded-circle" data-toggle="tooltip" data-original-title="Mail Us">
            <span class="btn-inner--icon"><i class="fas fa-envelope"></i></span>
          </button>
          <button target="_blank" href="/" rel="nofollow" class="btn btn-icon-only btn-twitter rounded-circle" data-toggle="tooltip" data-original-title="Follow us">
            <span class="btn-inner--icon"><i class="fab fa-twitter"></i></span>
          </button>
          <button target="_blank" href="/" rel="nofollow" class="btn-icon-only rounded-circle btn btn-facebook" data-toggle="tooltip" data-original-title="Like us">
            <span class="btn-inner--icon"><i class="fab fa-facebook"></i></span>
          </button>
          <button target="_blank" href="/" rel="nofollow" class="btn btn-icon-only btn-instagram rounded-circle" data-toggle="tooltip" data-original-title="Follow us">
            <span class="btn-inner--icon"><i class="fab fa-instagram "></i></span>
          </button>
          <button target="_blank" href="/" rel="nofollow" class="btn btn-icon-only btn-github rounded-circle" data-toggle="tooltip" data-original-title="Star on Github">
            <span class="btn-inner--icon"><i class="fab fa-github"></i></span>
          </button>
             
           			
				</div>				
				</div>
           </div>		    
		   <hr>
           <div class="row justify-content-center">             
                     <div class="col-auto">					
                    <p>&copy; Copyright 2025 AGRICULTURE PORTAL, All Rights Reserved </p>
                </div>
           </div>
        </div>
    </footer>


</body>

  <script>
  /* ── Password show/hide ─────────────────────────────────────── */
  function password_show_hide() {
    var x = document.getElementById("password");
    var show_eye = document.getElementById("show_eye");
    var hide_eye = document.getElementById("hide_eye");
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

  function password_show_hide2() {
    var x = document.getElementById("cpassword");
    var show_eye = document.getElementById("show_eye2");
    var hide_eye = document.getElementById("hide_eye2");
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

  /* ── Helpers ────────────────────────────────────────────────── */
  function showError(fieldId, errorId, msg) {
    var field = document.getElementById(fieldId);
    var errEl = document.getElementById(errorId);
    if (field) field.classList.add('is-invalid');
    if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
  }

  function clearError(fieldId, errorId) {
    var field = document.getElementById(fieldId);
    var errEl = document.getElementById(errorId);
    if (field) { field.classList.remove('is-invalid'); field.classList.add('is-valid'); }
    if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
  }

  /* ── Password strength ─────────────────────────────────────── */
  function checkStrength(password) {
    var strength = 0;
    if (password.length >= 8)             strength++;
    if (/[A-Z]/.test(password))           strength++;
    if (/[a-z]/.test(password))           strength++;
    if (/[0-9]/.test(password))           strength++;
    if (/[\W_]/.test(password))           strength++;
    return strength;
  }

  document.getElementById('password').addEventListener('input', function () {
    var val      = this.value;
    var strength = checkStrength(val);
    var bar      = document.getElementById('strengthBar');
    var txt      = document.getElementById('strengthText');
    var pct      = (strength / 5) * 100;
    bar.style.width = pct + '%';
    bar.className = 'progress-bar';
    if (strength <= 1) { bar.classList.add('bg-danger');  txt.textContent = 'Weak'; }
    else if (strength <= 3) { bar.classList.add('bg-warning'); txt.textContent = 'Moderate'; }
    else if (strength === 4) { bar.classList.add('bg-info');    txt.textContent = 'Strong'; }
    else                    { bar.classList.add('bg-success'); txt.textContent = 'Very Strong'; }
  });

  /* ── Real-time field validation ────────────────────────────── */
  document.getElementById('custName').addEventListener('blur', function () {
    var v = this.value.trim();
    if (!v) showError('custName','nameError','Customer name is required.');
    else if (v.length < 2) showError('custName','nameError','Name must be at least 2 characters.');
    else if (!/^[a-zA-Z\s.]+$/.test(v)) showError('custName','nameError','Name can only contain letters and spaces.');
    else clearError('custName','nameError');
  });

  document.getElementById('custEmail').addEventListener('blur', function () {
    var v = this.value.trim();
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!v) showError('custEmail','emailError','Email address is required.');
    else if (!re.test(v)) showError('custEmail','emailError','Please enter a valid email address.');
    else clearError('custEmail','emailError');
  });

  document.getElementById('custMobile').addEventListener('blur', function () {
    var v = this.value.trim();
    if (!v) showError('custMobile','mobileError','Mobile number is required.');
    else if (!/^[6-9][0-9]{9}$/.test(v)) showError('custMobile','mobileError','Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.');
    else clearError('custMobile','mobileError');
  });

  // Allow only digits in mobile field
  document.getElementById('custMobile').addEventListener('keypress', function(e) {
    if (!/[0-9]/.test(e.key)) e.preventDefault();
  });

  document.getElementById('state').addEventListener('change', function () {
    if (!this.value) showError('state','stateError','Please select your state.');
    else clearError('state','stateError');
  });

  document.getElementById('district-list').addEventListener('change', function () {
    if (!this.value) showError('district-list','cityError','Please select your city.');
    else clearError('district-list','cityError');
  });

  document.getElementById('custAddress').addEventListener('blur', function () {
    var v = this.value.trim();
    if (!v) showError('custAddress','addressError','Address is required.');
    else if (v.length < 5) showError('custAddress','addressError','Please enter a complete address (at least 5 characters).');
    else clearError('custAddress','addressError');
  });

  document.getElementById('custPincode').addEventListener('blur', function () {
    var v = this.value.trim();
    if (!v) showError('custPincode','pincodeError','Pincode is required.');
    else if (!/^[1-9][0-9]{5}$/.test(v)) showError('custPincode','pincodeError','Enter a valid 6-digit pincode.');
    else clearError('custPincode','pincodeError');
  });

  // Allow only digits in pincode field
  document.getElementById('custPincode').addEventListener('keypress', function(e) {
    if (!/[0-9]/.test(e.key)) e.preventDefault();
  });

  document.getElementById('password').addEventListener('blur', function () {
    var v = this.value;
    var re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (!v) showError('password','passwordError','Password is required.');
    else if (!re.test(v)) showError('password','passwordError','Password must be 8+ characters with uppercase, lowercase, number & special character.');
    else clearError('password','passwordError');
  });

  document.getElementById('cpassword').addEventListener('blur', function () {
    var pw  = document.getElementById('password').value;
    var cpw = this.value;
    if (!cpw) showError('cpassword','cpasswordError','Please confirm your password.');
    else if (pw !== cpw) showError('cpassword','cpasswordError','Passwords do not match.');
    else clearError('cpassword','cpasswordError');
  });

  /* ── Form submit validation ────────────────────────────────── */
  document.getElementById('customerRegisterForm').addEventListener('submit', function (e) {
    var valid = true;

    var name    = document.getElementById('custName').value.trim();
    var email   = document.getElementById('custEmail').value.trim();
    var mobile  = document.getElementById('custMobile').value.trim();
    var state   = document.getElementById('state').value;
    var city    = document.getElementById('district-list').value;
    var address = document.getElementById('custAddress').value.trim();
    var pincode = document.getElementById('custPincode').value.trim();
    var pw      = document.getElementById('password').value;
    var cpw     = document.getElementById('cpassword').value;
    var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var pwRe    = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    if (!name) { showError('custName','nameError','Customer name is required.'); valid=false; }
    else if (name.length < 2) { showError('custName','nameError','Name must be at least 2 characters.'); valid=false; }
    else if (!/^[a-zA-Z\s.]+$/.test(name)) { showError('custName','nameError','Name can only contain letters and spaces.'); valid=false; }
    else clearError('custName','nameError');

    if (!email) { showError('custEmail','emailError','Email address is required.'); valid=false; }
    else if (!emailRe.test(email)) { showError('custEmail','emailError','Please enter a valid email address.'); valid=false; }
    else clearError('custEmail','emailError');

    if (!mobile) { showError('custMobile','mobileError','Mobile number is required.'); valid=false; }
    else if (!/^[6-9][0-9]{9}$/.test(mobile)) { showError('custMobile','mobileError','Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.'); valid=false; }
    else clearError('custMobile','mobileError');

    if (!state) { showError('state','stateError','Please select your state.'); valid=false; }
    else clearError('state','stateError');

    if (!city) { showError('district-list','cityError','Please select your city.'); valid=false; }
    else clearError('district-list','cityError');

    if (!address) { showError('custAddress','addressError','Address is required.'); valid=false; }
    else if (address.length < 5) { showError('custAddress','addressError','Please enter a complete address (at least 5 characters).'); valid=false; }
    else clearError('custAddress','addressError');

    if (!pincode) { showError('custPincode','pincodeError','Pincode is required.'); valid=false; }
    else if (!/^[1-9][0-9]{5}$/.test(pincode)) { showError('custPincode','pincodeError','Enter a valid 6-digit pincode.'); valid=false; }
    else clearError('custPincode','pincodeError');

    if (!pw) { showError('password','passwordError','Password is required.'); valid=false; }
    else if (!pwRe.test(pw)) { showError('password','passwordError','Password must be 8+ characters with uppercase, lowercase, number & special character.'); valid=false; }
    else clearError('password','passwordError');

    if (!cpw) { showError('cpassword','cpasswordError','Please confirm your password.'); valid=false; }
    else if (pw !== cpw) { showError('cpassword','cpasswordError','Passwords do not match.'); valid=false; }
    else clearError('cpassword','cpasswordError');

    if (!valid) {
      e.preventDefault();
      var firstInvalid = document.querySelector('.is-invalid');
      if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
  </script>

</html>
