<?php 
session_start();
//
// Sep 10, removed match msisdn pattern
// Sep 17, send UTC as time, let browser conver to local time to adapter different time zone
#$string = file_get_contents("sampleDataColumn.json");
#echo $string;
if(!isset($_SERVER['HTTP_REFERER'])){
    // redirect them to your desired location
    header('location:index.php');
    exit;
}


$gtoken=getParameter('token');
$gemail=getParameter('email');

error_log('gtoken:'.$gtoken.__FILE__ ."\n", 3, "/var/www/html/upload/config_php_access.log");
error_log('gemail:'.$gemail.__FILE__ ."\n", 3, "/var/www/html/upload/config_php_access.log");
    

// check_token($gtoken,$gemail);  

$g_conn; 
$Configs;
$Debug;
$g_host="btlp002578";                
               
               
load_config();


// if( ! isset( $_SERVER['token_verified'] ) || $_SESSION["token_verified"] != $Configs['general']['token']){
    // echo('location:error.php?error=no_session_or_incorrect_session:'.$_SESSION["token_verified"]." from $0" );
    // exit;        
// }


// connectDB();   
return_json_str();

// the main function to run query and return in json array format 
function return_json_str(){
    global $g_conn;
    $phone = getParameter("phone");
    $email = getParameter("email");
    $phone = empty(getParameter('phone'))? '1':getParameter('phone') ;  
    $start_yyyymmdd = "now()- INTERVAL '7 day'";
    $para_ymd=getParameter('start_yyyymmdd');
     mydumpvar($para_ymd, 'para_ymd');
    if (!   empty(getParameter('start_yyyymmdd')) ){
        $start_yyyymmdd="to_date('".getParameter('start_yyyymmdd')."','yyyymmdd')";
    }
    
    
    $sql = "with src as ( select  msisdn, to_char( event_ts_utc ,'yyyy-mm-ddThh24:mi:ss')||'.000Z' as date_str, latitude, longitude, case when RADIUS_IN > RADIUS_OUT then RADIUS_IN else RADIUS_OUT end as event_radius, null AS m_lattitude, null AS m_longitude, null AS m_radius, 'GMLC' as source_cd , lev_conf from gmlc_t_location_trackingv2 g where EVENT_TS_UTC >=now()- INTERVAL '7 day'  and SOURCE_CD='GMLC' and event_type='ACTIVE_GEOLOC' order by date_str ) select msisdn , date_str, latitude as latitude, longitude,event_radius, source_cd,lev_conf from src where LATITUDE is not null order by date_str,msisdn";

    //to_char( event_ts_utc - INTERVAL '4 hour' ,'yyyy-mm-dd hh24:mi')||' EST' as date_str
    if(! empty($email)){
        $sql = "select msisdn , to_char( event_ts_utc ,'yyyy-mm-ddThh24:mi:ss')||'.000Z' as date_str, latitude as latitude, longitude,case when RADIUS_IN > RADIUS_OUT then RADIUS_IN else RADIUS_OUT end as event_radius , source_cd,lev_conf from
  gmlc_t_location_trackingv2 g where EVENT_TS_UTC >=now()- INTERVAL '7 day'  and SOURCE_CD='GMLC' and event_type='ACTIVE_GEOLOC' 
  and msisdn in (select msidsn_alias  from gmlc_r_alias_email_mapping    where email ='".$email."'
   or allow_emails like '%".$email."')
  order by date_str ";
    }
    
    mydumpvar($sql, 'Query');
    $result = pg_exec($g_conn, $sql);
    if ($result) {
        $jstr="[";
        for ($row = 0; $row < pg_numrows($result); $row++) {
            $lat=trim(sprintf("%10.6f",pg_result($result, $row, 'latitude') ));
            $lng=trim(sprintf("%10.6f", pg_result($result, $row, 'longitude') ) );  
            $msisdn=pg_result($result, $row, 'msisdn') ;
            $date_str=pg_result($result, $row, 'date_str') ;
            $event_radius=pg_result($result, $row, 'event_radius') ;
            $lev_conf=pg_result($result, $row, 'lev_conf') ;
            $source_cd=pg_result($result, $row, 'source_cd') ;
            if(is_numeric($lat) and is_numeric($lng)){
                $jstr .= "{\"lat\":$lat,\"lng\":$lng,\"event_radius\":$event_radius,\"msisdn\":\"$msisdn\",\"date_str\":\"$date_str\",\"lev_conf\":$lev_conf, \"source_cd\":\"$source_cd\"},". PHP_EOL;
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