<?php 
session_start();
// sep 10, 2020  select fence only status great than 0.  as don't show fence to user for now
// Sep 17, 2020   hide fence data
if(!isset($_SERVER['HTTP_REFERER'])){
//    redirect them to your desired location
    header('location:index.php');
    exit;
}



$g_conn; 
$Configs;
$Debug;
$g_host="btlp002578";                
load_config();


// if( ! isset( $_SERVER['token_verified'] ) || $_SESSION["token_verified"] != $Configs['general']['token']){
    // echo('location:error.php?error=no_session_or_incorrect_session:'.$_SESSION["token_verified"]." from $0" );
    // exit;        
// }

 
                
connectDB();   
return_json_str();

// the main function to run query and return in json array format 
function return_json_str(){
    global $g_conn;
    $phone = getParameter("phone");
    $phone = empty(getParameter('phone'))? '1':getParameter('phone') ;  
    $start_yyyymmdd = 'trunc(sysdate)-7';
    $para_ymd=getParameter('start_yyyymmdd');
     mydumpvar($para_ymd, 'para_ymd');
    if (!   empty(getParameter('start_yyyymmdd')) ){
        $start_yyyymmdd="to_date('".getParameter('start_yyyymmdd')."','yyyymmdd')";
    }
    
    $bypass_david= $phone == '+14168829337'? "": "and   MSISDN != '+14168829337'";
    
    // before query modify with status > 0 to no show fence on purpose
    $sql = "SELECT 
     case when msisdn AS msisdn,
    latitude as latitude,
    longitude as longitude ,
     radius 
FROM
    pdc_t_fence_def
    where status>0";

    mydumpvar($sql, 'Query');
    
    $result = pg_exec($g_conn, $sql);
    if ($result) {
        $jstr="[";
        for ($row = 0; $row < pg_numrows($result); $row++) {
            $lat=trim(sprintf("%10.6f",pg_result($result, $row, 'latitude') ));
            $lng=trim(sprintf("%10.6f", pg_result($result, $row, 'longitude') ) );  
            $msisdn=pg_result($result, $row, 'msisdn') ;
            $radius=pg_result($result, $row, 'radius') ;
            if(is_numeric($lat) and is_numeric($lng)){
                $jstr .= "{\"lat\":$lat,\"lng\":$lng,\"radius\":$radius,\"msisdn\":\"$msisdn\"},". PHP_EOL;
            }
        }
        $jstr= rtrim(rtrim($jstr), ",");  // remove last comma and space
        $jstr .="]". PHP_EOL;                
        echo $jstr;      
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

 