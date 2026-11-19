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
      <div class="container-fluid">
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

          <ul class="navbar-nav align-items-lg-center ml-auto topnav" id="nav">
		  
		  	 
		  
	 <!--<li class="nav-item" id="prediction">
			   <div class="dropdown show ">
		  <a class="nav-link dropdown-toggle text-white " href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                  <span class="text-white nav-link-inner--text"
                  ><i class="text-white fas fa-magic"></i> Prediction</span
                >
		  </a>

		   <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
		   <a class="dropdown-item" href="fcrop_prediction.php">Crop Prediction​</a>
		   <a class="dropdown-item" href="fyield_prediction.php">Yield Prediction​</a>
			<a class="dropdown-item" href="frainfall_prediction.php">Rainfall Prediction​</a>
		  </div>
		</div>
			</li>
			
			
			
			 <li class="nav-item" id="recommendation">
			   <div class="dropdown show ">
		  <a class="nav-link dropdown-toggle text-white " href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                  <span class="text-white nav-link-inner--text"
                  ><i class="text-white fas fa-gavel"></i> Recommendation</span
                >
		  </a>

		   <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
			<a class="dropdown-item" href="fcrop_recommendation.php">Crop Recommendation​</a>
			<a class="dropdown-item" href="ffertilizer_recommendation.php">Fertilizer Recommendation​</a>
		  </div>
		</div>
			</li>-->
			

		  
			 <li class="nav-item" id="trade" style="position:relative;">
		  <a class="nav-link dropdown-toggle text-white" href="#" onclick="toggleFarmerDropdown('tradeMenu', event)">
		    <span class="text-white nav-link-inner--text"><i class="text-white fas fa-shopping-cart"></i> Trade</span>
		  </a>
		  <div id="tradeMenu" style="display:none; position:absolute; top:100%; right:0; background:#fff; min-width:160px; box-shadow:0 2px 8px rgba(0,0,0,0.15); border-radius:4px; z-index:9999;">
			<a class="dropdown-item" href="ftradecrops.php" style="display:block; padding:8px 16px; color:#333; text-decoration:none;">Trade Crops</a>
			<a class="dropdown-item" href="fstock_crop.php" style="display:block; padding:8px 16px; color:#333; text-decoration:none;">Crop Stocks</a>
			<a class="dropdown-item" href="fselling_history.php" style="display:block; padding:8px 16px; color:#333; text-decoration:none;">Selling History</a>
		  </div>
			</li>
			

			 
			
			<li class="nav-item" id="tools" style="position:relative;">
		  <a class="nav-link dropdown-toggle text-white" href="#" onclick="toggleFarmerDropdown('toolsMenu', event)">
		    <span class="text-white nav-link-inner--text"><i class="text-white fas fa-gear"></i> Tools</span>
		  </a>
		  <div id="toolsMenu" style="display:none; position:absolute; top:100%; right:0; background:#fff; min-width:160px; box-shadow:0 2px 8px rgba(0,0,0,0.15); border-radius:4px; z-index:9999;">
			<a class="dropdown-item" href="fchatgpt.php" style="display:block; padding:8px 16px; color:#333; text-decoration:none;"><i class="text-dark fad fa-robot"></i> Chat Bot</a>
			<a class="dropdown-item" href="fweather_prediction.php" style="display:block; padding:8px 16px; color:#333; text-decoration:none;"><i class="text-dark fas fa-cloud"></i> Weather Forecast</a>
			<a class="dropdown-item" href="fnewsfeed.php" style="display:block; padding:8px 16px; color:#333; text-decoration:none;"><i class="text-dark fas fa-newspaper"></i> News Feed</a>
		  </div>
			</li>
			
		
		
		   <li class="nav-item" id="profile">
              <a href="fprofile.php" class="nav-link">
                <span class="text-white nav-link-inner--text font-weight-bold"
                  ><i class="text-white fas fa-user"></i> <?php echo $para2 ?> </span
                >
              </a>
            </li>
			
			
		  
		   <li class="nav-item">
              <a href="flogout.php" class="nav-link">
                <span class="text-white nav-link-inner--text font-weight-bold"
                  ><i class="text-danger fas fa-power-off"></i> Logout </span
                >
              </a>
            </li>



          </ul>
        </div>
      </div>
    </nav>
	
	

<style>
.topnav a {
  border-bottom: 3px solid transparent;
}

.topnav a:hover {
  border-bottom: 3px solid red;
}

.topnav a.activa {
  border-bottom: 3px solid red;
}

</style>	
	
  <script>
function toggleFarmerDropdown(id, e) {
  e.preventDefault();
  var menus = ['tradeMenu', 'toolsMenu'];
  menus.forEach(function(m) {
    if (m !== id) document.getElementById(m).style.display = 'none';
  });
  var menu = document.getElementById(id);
  menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.nav-item')) {
    ['tradeMenu', 'toolsMenu'].forEach(function(m) {
      document.getElementById(m).style.display = 'none';
    });
  }
});

document.querySelectorAll("#nav li a").forEach(function(link) {
    if (link.href === window.location.href) {
        link.classList.add("activaa");
    }
});
  </script>