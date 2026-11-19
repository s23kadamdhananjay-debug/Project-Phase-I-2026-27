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
<?php require ('fheader.php');  ?>

  <body class="bg-white" id="top">
  
 <script>
   // News is rendered server-side in fnewsfeed.php. Keeping this page free of
   // a second client-side NewsAPI request avoids exposing the API key.
 </script> 

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
            <span class="badge badge-danger badge-pill mb-3">News</span>
          </div>
        </div>
		
                    <div class="row row-content">
            <div class="col-md-12 mb-3">
    <div class="row">
        <div class="col-md-12">
				<div class="card text-white bg-gradient-secondary mb-3">
				  <div class="card-header">
				  <span class=" text-warning display-4" > News List  </span> 					
				  </div>
				  
				  <div class="card-body text-dark">
				<table class="table table-striped table-hover table-responsive table-bordered bg-gradient-white text-center display" id="myTable">

    <thead>
					<tr class="font-weight-bold text-default">
					<th><center>Image</center></th>
					<th><center>Title</center></th>				
					<th><center>Author</center></th>
					<th><center>Published</center></th>
					<th><center>Visit</center></th>
					

        </tr>
    </thead>
    <tbody>


    <?php
    $newsApiKey = agri_env('NEWS_API_KEY', '');
    $newsArticles = array();
    $newsError = '';

    if ($newsApiKey === '') {
        $newsError = 'The news feed is not configured. Add NEWS_API_KEY to your .env file.';
    } else {
        $url = 'https://newsapi.org/v2/everything?q=agriculture%20OR%20farmers&language=en&sortBy=publishedAt&pageSize=30&apiKey=' . urlencode($newsApiKey);
        $context = stream_context_create(array('http' => array('timeout' => 15)));
        $response = @file_get_contents($url, false, $context);
        $newsdata = $response === false ? null : json_decode($response, true);

        if (!is_array($newsdata) || !isset($newsdata['articles']) || !is_array($newsdata['articles'])) {
            $newsError = 'The news service is unavailable right now. Please try again later.';
        } else {
            $newsArticles = array_slice($newsdata['articles'], 0, 30);
        }
    }
    ?>

    <?php if ($newsError !== ''): ?>
        <tr><td colspan="5" class="text-danger font-weight-bold"><?php echo htmlspecialchars($newsError, ENT_QUOTES, 'UTF-8'); ?></td></tr>
    <?php else: ?>
        <?php foreach ($newsArticles as $news): ?>
            <tr class="text-center">
                <td>
                    <?php if (!empty($news['urlToImage'])): ?>
                        <img class="img img-thumbnail" src="<?php echo htmlspecialchars($news['urlToImage'], ENT_QUOTES, 'UTF-8'); ?>" alt="News thumbnail" width="100px" loading="lazy">
                    <?php endif; ?>
                </td>
                <td class="text-wrap text-justify"><?php echo htmlspecialchars((string)($news['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-wrap text-justify"><?php echo htmlspecialchars((string)($news['author'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-justify"><?php echo htmlspecialchars((string)($news['publishedAt'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <?php if (!empty($news['url'])): ?>
                        <a class="btn btn-sm btn-info text-white" href="<?php echo htmlspecialchars($news['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Visit</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

</div>	
	
	
  </div>
    </div>
       </div>
	</div>	 
</section>

    <?php require("footer.php");?>
   <script>
				$(document).ready( function () {
    $('#myTable').DataTable();
} );
</script>
</body>
</html>