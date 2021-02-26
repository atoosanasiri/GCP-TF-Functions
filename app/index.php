<!DOCTYPE html>
<?php 

session_start();
    

$g_conn;
$Configs;
$Debug;
$g_host="btlp002578";                
$g_token='';
load_config();
                
gen_token();

// the main function to run query and return in json array format 

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function gen_token(){
    global $g_conn,$g_token;
    $g_token=generateRandomString(16);
    
    $sql = "insert into session_data(token,create_ts) values ('$g_token',now())";
    $result = pg_exec($g_conn, $sql);
    $cmdtuples = pg_affected_rows($result);
    
    if ( 0 == strcmp($cmdtuples,'1' ) ) {
        // echo "Ok : $cmdtuples".__FILE__;
    }
    else{
        echo  pg_result_error($result). '<p>';
        echo "Error : $cmdtuples".__FILE__.'<p>';
    }
    pg_close($g_conn);
     
}

function load_config(){
    global $Configs,$Debug ;
    //loads configs from configuration php template
    $Configs= include_once('inc/config.php');
    //construct custom report url
    // $Debug=empty(getParameter('debug'))?$Configs['general']['debug']:true;  //parameter high priority
    // mydumpvar($Configs['general']['default_page'],'Report_config-default_page');
    return connectDB();
}

                 
                 
function getParameter($para){
    $rtn="";
    $para=strtolower($para);
    global $g_host;
    if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
        $url="http://".$g_host."/raid_apache_home/googlechart/raid/chart_from_table.php";
    }
    else{
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    $parts = parse_url(strtolower($url)); 
    parse_str($parts['query'], $query);     
    $rtn=isset($query["$para"])? $query["$para"]: "";
    // mydumpvar($rtn ,"$para");
    return  $rtn;
}

function mydumpvar($var,$name){
    global $Debug;
    if($Debug){
        echo "<br>---- dump '$name' begin -- <br>";
        if(is_array($var)){
            var_export($var);
            echo "<br>";
        }
        else{
            var_dump("$var <br>");
        }
        echo "---- dump '$name' end -- <br>";
    }
}             

?>
<html>
<head>
  <meta name="google-signin-client_id" content="621656103050-idttka16m6vuo22574s472m2eacfrtsn.apps.googleusercontent.com">
</head>
<body>
  <div id="my-signin2"></div>
  <script>
    function onSuccess(googleUser) {
      var gname=googleUser.getBasicProfile().getName();
      var email=googleUser.getBasicProfile().getEmail();
      console.log('Logged in as: ' + googleUser.getBasicProfile().getName());
      console.log('Logged in EMAIL: ' + googleUser.getBasicProfile().getEmail());
      post('geochart_pdc_gmlc_tracking.php', {'email': email, 'token': '<?php echo  $g_token ?>' },'post');
    }
    function onFailure(error) {
      console.log(error);
    }
    function renderButton() {
       gapi.load('auth2', function() {
        gapi.auth2.init();
      });
           gapi.signin2.render('my-signin2', {
            'scope': 'profile email',
            'width': 240,
            'height': 50,
            'longtitle': true,
            'theme': 'dark',
            'onsuccess': onSuccess,
            'onfailure': onFailure
          });
    }
    
        /**
     * sends a request to the specified url from a form. this will change the window location.
     * @param {string} path the path to send the post request to
     * @param {object} params the paramiters to add to the url
     * @param {string} [method=post] the method to use on the form
     */

    function post(path, params, method) {

      // The rest of this code assumes you are not using a library.
      // It can be made less wordy if you use one.
      const form = document.createElement('form');
      form.method = method;
      form.action = path;

      for (var key in params) {
        if (params.hasOwnProperty(key)) {
          const hiddenField = document.createElement('input');
          hiddenField.type = 'hidden';
          hiddenField.name = key;
          hiddenField.value = params[key];

          form.appendChild(hiddenField);
        }
      }

      document.body.appendChild(form);
      form.submit();
    }

  </script>

  <script


  src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>
  
    <a href="#" onclick="signOut();">Sign out</a>
<script>
  function signOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
      console.log('User signed out.');
    });
  }
</script>


</body>
</html>


