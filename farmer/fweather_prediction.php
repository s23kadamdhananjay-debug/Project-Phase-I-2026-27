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

<?php

$display_district_name ="";

$display_district="Select F_District from farmerlogin WHERE email='$user_check'";
$display_district_result=mysqli_query($conn,$display_district);
$display_district_name = mysqli_fetch_array($display_district_result);
$District_name_farmer=$display_district_name[0];


// WeatherApi City_Id Code : 
ini_set('memory_limit', '-1');
$url = 'static/citylist.json'; // path to your JSON file
$data = file_get_contents($url); // put the contents of the file into a variable
$district= json_decode($data); // decode the JSON feed

$district_weather_id=0;

foreach ($district as $district) {
        
    if ($district->name == trim($District_name_farmer)) {
        $district_weather_id=$district->id;
    }
}
if($district_weather_id<=0){
    $district_weather_id=1253952;
	// Mangalore - 1263780   , Mysore - 1262321 , Udupi - 1253952
}
$city_weather_id=strval($district_weather_id);




date_default_timezone_set(agri_env('APP_TIMEZONE', 'Asia/Kolkata'));
$apiKey = agri_env('OPENWEATHER_API_KEY', '');
$cityId = $city_weather_id;


// https://api.openweathermap.org/data/2.5/forecast?id=1275339&lang=en&units=metric&APPID=...

$forecast = array();
$weatherError = '';
if ($apiKey === '') {
    $weatherError = 'Weather is not configured. Add OPENWEATHER_API_KEY to your .env file.';
} else {
    $googleApiUrl = "https://api.openweathermap.org/data/2.5/forecast?id=" . rawurlencode($cityId) . "&lang=en&units=metric&APPID=" . rawurlencode($apiKey);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    $data = $response === false ? null : json_decode($response);

    if (!is_object($data) || !isset($data->list) || !is_array($data->list)) {
        $weatherError = 'The weather service is unavailable right now. Please try again later.';
        if ($curlError !== '') {
            error_log('OpenWeatherMap request failed: ' . $curlError);
        }
    } else {
        $forecast = $data->list;
    }
}
$currentTime = time();
?>




<!DOCTYPE html>
<html>

<?php $weatherReportPageStyles = true; include ('fheader.php'); ?>

  <body class="bg-white" id="top">
  
<?php include ('fnav.php');  ?>

  <main class="weather-report-page">
    <div class="container weather-content">
      <header class="weather-page-heading">
        <h1><span class="weather-eyebrow">WEATHER FORECAST</span></h1>
      </header>

      <section class="weather-forecast-card" aria-labelledby="weather-location">
        <header class="weather-location-header">
          <span class="weather-card-eyebrow">Local forecast</span>
          <h2 id="weather-location"><?php echo htmlspecialchars($District_name_farmer, ENT_QUOTES, 'UTF-8'); ?></h2>
        </header>

        <div class="weather-card-content">
          <div class="weather-notice" role="status">
            Current weather detected from your browser location.
          </div>

          <div class="weather-table-scroll">
            <table class="display weather-forecast-table" id="myTable">
              <thead>
                <tr>
                  <th scope="col">Date</th>
                  <th scope="col">Current Time</th>
                  <th scope="col">Temperature</th>
                  <th scope="col">Weather Description</th>
                  <th scope="col">Humidity</th>
                  <th scope="col">Wind Speed</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($forecast as $f) {
                    $date = substr($f->dt_txt, 0, 10);
                    $time = substr($f->dt_txt, 11);
                ?>
                  <tr>
                    <td class="weather-date"><?php echo $date; ?></td>
                    <td class="weather-time"><?php echo $time; ?></td>
                    <td>
                      <span class="weather-temperature-values">
                        <span class="weather-temperature-high"><?php echo $f->main->temp_max; ?> &#8451;</span>
                        <span class="weather-temperature-divider">/</span>
                        <span class="weather-temperature-low"><?php echo $f->main->temp_min; ?> &#8451;</span>
                      </span>
                    </td>
                    <td>
                      <div class="weather-description-cell">
                        <img src="https://openweathermap.org/img/w/<?php echo $f->weather[0]->icon; ?>.png" class="weather-icon" alt="" />
                        <div>
                          <span class="weather-condition-main"><?php echo $f->weather[0]->main; ?></span>
                          <span class="weather-condition-detail"><?php echo $f->weather[0]->description; ?></span>
                        </div>
                      </div>
                    </td>
                    <td class="weather-humidity"><?php echo $f->main->humidity; ?> &#37;</td>
                    <td class="weather-wind"><?php echo $f->wind->speed; ?> KM/H</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>

          <details class="weather-current-details">
            <summary>Open current weather widget</summary>
            <div class="weather-widget-panel">
              <span id="openweathermap-widget-9"></span>
            </div>
          </details>
          <script>window.myWidgetParam ? window.myWidgetParam : window.myWidgetParam = [];  window.myWidgetParam.push({id: 9,cityid: '<?php echo $cityId ?>',appid: '<?php echo htmlspecialchars($apiKey, ENT_QUOTES, 'UTF-8'); ?>',units: 'metric',containerid: 'openweathermap-widget-9',  });  (function() {var script = document.createElement('script');script.async = true;script.charset = "utf-8";script.src = "//openweathermap.org/themes/openweathermap/assets/vendor/owm/js/weather-widget-generator.js";var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(script, s);  })();</script>
        </div>
      </section>
    </div>
  </main>

    <?php require("footer.php");?>
   <script>
      $(document).ready(function () {
        $('#myTable').DataTable({
          autoWidth: false,
          pageLength: 10,
          lengthMenu: [10, 25, 50, 100],
          dom: '<"weather-table-controls"lf>rt<"weather-table-footer"ip>',
          language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            zeroRecords: 'No matching entries'
          }
        });
      });
   </script>
</body>
</html>
