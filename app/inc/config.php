<?php
//-------------------------------------------------------------------------------------------------------------------
// this is config file for general php in GCP
// date: Sep 2, 2020
//       Sep 16, 2020 using pgservice
//       Sep 17, 2020 remove password, enable sslmode
// author: Charlie Y
// history:
//-------------------------------------------------------------------------------------------------------------------
    /* at the top of 'check.php' */
if ( $_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST' ) {
    /* 
       Up to you which header to send, some prefer 404 even if 
       the files does exist for security
    */
    // header( 'HTTP/1.0 403 Forbidden', TRUE, 403 );

    /* choose the appropriate page to redirect users */
    // die( header( 'location: ../error.php?error=web access Forbidden' ) );
    // $myfile = fopen("/tmp/config_php_access.log", "a") or die("Unable to open file!");
    // fwrite($myfile, $_SERVER['REQUEST_METHOD']);
    // fclose($myfile);
    // error_log('REQUEST_METHOD:'.$_SERVER['REQUEST_METHOD']."\n", 3, "/var/www/html/upload/config_php_access.log");
    // error_log('SCRIPT_FILENAME:'.$_SERVER['SCRIPT_FILENAME']."\n", 3, "/var/www/html/upload/config_php_access.log");
    // error_log('file realpath:'.realpath(__FILE__) ."\n", 3, "/var/www/html/upload/config_php_access.log");
    
     if ( $_SERVER['REQUEST_METHOD']=='GET' && realpath(__FILE__) == realpath( $_SERVER['SCRIPT_FILENAME'] ) ) {
        /* 
           Up to you which header to send, some prefer 404 even if 
           the files does exist for security
        */
        header( 'HTTP/1.0 403 Forbidden', TRUE, 403 );

        /* choose the appropriate page to redirect users */
        die( header( 'location: ../error.php?error=web access Forbidden' ) ) ;

    }

}

function connectDB(){
    global $g_conn,$Configs;
    $dbname= $Configs['general']['dbname'] ;
    $host= $Configs['general']['host'] ;
    $user= $Configs['general']['user'] ;
    $paswd= $Configs['general']['paswd'] ;
    $pgservice= $Configs['general']['pgservice'] ;
    if(! empty ($pgservice)){
        $g_conn=pg_connect("sslmode=require service=$pgservice");
        if ($g_conn) {
             // echo 'Connection attempt succeeded.'.__FILE__;
        } else {
            echo 'Connection attempt failed via pgservice.';
        }
        return $g_conn;   
    }
    // service is null and no g_conn, connect by username
    if( empty ($pgservice) and !  $g_conn ) {
        if(empty($paswd)){
            echo 'Error, db passw0rd is null at ' .__FILE__;
            return;
        }
        else{
            $g_conn=pg_connect("sslmode=require host=$host dbname=$dbname  user=$user paswd=$paswd");
            if ($g_conn) {
                // echo 'Connection attempt succeeded.';
            } else {
                echo 'Connection attempt failed.';
            }
            return $g_conn;   
        }
    }
}
 
function check_token($token,$email){
    global $g_conn;
	load_config();
    connectDb();
                   
    $sql = "update session_data set email=$1, ip=$2, update_ts=now(), browser=$3 where token=$4 and logout_ts is null";
    $ip=getRealIpAddr();
    $browser=substr(getBrowser(),255);
    
    $result = pg_query_params($g_conn,$sql, array($email,$ip,$browser,trim($token)));

                                     
    $cmdtuples = pg_affected_rows($result);
    
    if ( 0 == strcmp($cmdtuples,'1' ) ) {
        $_SESSION["token_verified"] = $Configs['general']['token'];
    }
    else{
        echo "Error : $cmdtuples,'$token'";
        pg_result_error($result);
        header('location:error.php?error=check_token_failed');
        exit;        
    }
    
}

function expire_token($token){
    global $g_conn;
	load_config();
    connectDb();
                   
    $sql = "update session_data set logout_ts=now()  where token=$1 and logout_ts is null";
    $result = pg_query_params($g_conn,$sql, array( trim($token) ));
                                     
    $cmdtuples = pg_affected_rows($result);
    
    if ( 0 == strcmp($cmdtuples,'1' ) ) {
        $_SESSION["token_verified"] = $Configs['general']['token'];
    }
    else{
        echo "Error : $cmdtuples,'$token'";
        pg_result_error($result);
        header('location:error.php?error=check_token_failed');
        exit;        
    }
    
}
 
 

return array(
    // --------- general section here ----------
    'general' => array(
        'user' => getenv('DB_US'),
        'paswd' => getenv('DB_PW'),
        'pgservice' => getenv('PGSERVICE'),
        'host'=>'10.53.0.2',
        'allowed_ip'=>'142.168.202.209',
        'token'=>'1234971234623423',
        'debug'=>false, 
        'dbname'=>'telemetry-db-pr'  // for testing from commandline
    ),
      
);

?>
