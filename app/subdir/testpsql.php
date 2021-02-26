<?php 
// for test
$Configs;
$g_conn; 
load_config();
if ($g_conn) {
    echo 'Connection attempt succeeded.';
} else {
    echo 'Connection attempt failed.' . __FILE__ ;
    echo `hostname`;
}
$query="select msisdn,event_ts_utc,latitude,longitude,radius_in,radius_out,start_angle,stop_angle,distanceunit,lev_conf,shape,create_ts,source_cd,event_type from gmlc_t_location_tracking_tmp";
$result = pg_exec($g_conn, $query);
if ($result) {
    echo "$query<br>";
    for ($row = 0; $row < pg_numrows($result); $row++) {
        $firstname = pg_result($result, $row, 'msisdn');
        echo $firstname ." ";
        $lastname = pg_result($result, $row, 'event_ts_utc');
        echo $lastname ." ";

        echo pg_result($result, $row, 'latitude') ." ";
        echo pg_result($result, $row, 'longitude') ." ";
        echo pg_result($result, $row, 'radius_in') ." ";
        echo pg_result($result, $row, 'distanceunit') ." ";
        echo pg_result($result, $row, 'event_type') ."<br>";
    }
}
pg_close($g_conn);


function load_config(){
    global $Configs,$Debug ;
    //loads configs from configuration php template
    $Configs= include_once('../inc/config.php');
    //construct custom report url
    // $Debug=empty(getParameter('debug'))?$Configs['general']['debug']:true;  //parameter high priority
    // mydumpvar($Configs['general']['default_page'],'Report_config-default_page');
    return connectDB();
}


?>
