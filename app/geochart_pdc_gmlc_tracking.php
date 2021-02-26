<?php
// History: Mar 24, 2020  Click Mark update chart ok. todo: multiple serials. animated chart with slider 
//          Mar 25, 2020 added charts and tables. move style to css             
// Start the session
//sep 10, passing email to get data, max zoom 11
// Sep 17, 2020, using new service connection
// table bgcolor changed from #D6EAF8 to white
    load_config();
    session_start();
    $gemail = $_POST['email'];
    $gtoken = $_POST['token'];
    // if( ! isset( $_SERVER['token_verified'] ) || $_SESSION["token_verified"] != $Configs['general']['token']){
        check_token($gtoken,$gemail);  
    // }
    
?>
<head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <title>PDC and GMLC location tracking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="css/geochart_pdc_tracking_map.css">
    <link href='https://fonts.googleapis.com/css?family=Orbitron' rel='stylesheet' type='text/css'>
</head>


<body>
  
  
 <table width="100%" bgcolor="white"> 
  <tr >
    <td>
    <a href="index.php">
    <img   style="width: 140px; height: 40px;"  type="image" id="logo" alt="logo"
       src="images/TELUS_2020_EN_Digital_RGB.png"  >  </td>
     </a>
    <td width="10%"  > <font  size=3 face=Arial color='4B286D'> <B>Location Tracking <?php // echo $gemail.'<br>'.$_SESSION["token"].'<br>'. $gtoken?> </B>
    </td>
    <td width="0%"  ><select id="select_source" name='select_source' onchange="source_change()">
  <option selected disabled>Select Source</option> 
  <option value="S">PDC/GMLC Sequential</option>
  <option  selected="selected" value="P">PDC/GMLC Parallel</option>
  <option value="PDC">PDC</option>
  <option value="GMLC">GMLC</option>
</select> </td>    
    <td width="10%"  ><select id="select_phone" name='select_phone' onchange="phone_change()">
  <option selected disabled>Choose a number</option> 
  <option value="+14168829337">+14168829337</option>
  <option value="+14388730200">+14388730200</option>
  <option value="+16042029762">+16042029762</option>
</select> </td>
    <td><input  style="width: 40px; height: 40px;" type="image" id="input_previous" alt="previous"
       src="images/previous.png" onclick="play_move(-1)" >  </td>
    <td width="30%"  align="center"  > <div class="slidecontainer">
        <input type="range" min="0" max="50" value="1" class="slider" id="myRange"></div> 
    </td> 
        <td><input  style="width: 40px; height: 40px;"  type="image" id="input_play" alt="Play"
       src="images/play.png" onclick="play_animation()" >  </td>
    <td><input  style="width: 40px; height: 40px;"  type="image" id="input_next" alt="Next"
       src="images/next.png" onclick="play_move(1)" >  </td>
    <td width="10%" align="center"  >
       <div style="font-family: 'Helvetica Neue', sans-serif;" id="country_cnt"></div></td>
    <td width="10%"><div style="font-family: 'Helvetica Neue', sans-serif; color='4B286D'" id="roamer_cnt"></div></td>
    <td width="20%"> <div style="font-family: 'Helvetica Neue', sans-serif; color='4B286D'" id="dstr"></div></td>
    <td width="42"  align="right" >
        <input type="image" id="myBtn" alt="About?" src="images/info.png" width=40 height=40>
            <!-- The Modal -->
            <div id="myModal" class="modal">

              <!-- Modal content -->
              <div class="modal-content" align="left">
                <span class="close"  >&times;</span><b>About This Page:</b><ul  >
                 <li>This is concept project to show your phone number's or some dummy number location info</li>
                 <li>show last 7 days by default</li>
                 <li>Use slider to drag drop to FF or FR</li>     
                 <li>Location is not 100% accurate guaranteed due to underline technical limitation, this location data only available after user request and consent </li>     
            </ul>
         </div>
    </td>
  </tr>
</table>
<!-- end of header table -->
<table>
 <tr > 
  <div id="map"></div>  
  <div id="legend"><h3>Legend</h3></div>
  </tr>
</table>      
<!-- end of map table -->

<!-- end of chart table -->
<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.load('current', {'packages':['table']});
// google.charts.setOnLoadCallback(draw_chart);
loadHelpModel();
  
var map;

var g_position = [40.748774, 0]; 
var g_cir_size=10;
var g_cir_color='red';
var g_markers=[];
var g_can_provs=['YT','NB','NU','PE','NT','MB','ON','NS','QC','BC','AB','SK','NF','NL'];
var g_countries=[];
var g_selected_phone=[];
var g_selected_source='GMLC';  // GMLC
var g_max_rec_seq=-9999;
var g_min_rec_seq=9999;
var g_days_2_dstr={};    
var g_country_cnt=0;
var g_radius=0;
var g_base_data_hash=[];
var g_autoplay=false;
var g_chart_hid_countries=[];    
var delay = 100; //milliseconds
var g_delay_rec_seq=g_min_rec_seq;
var g_total_in_chart=true;
var g_color_list=['red','blue','green','orange','MAROON','OLIVE','lime','AQUA','NAVY','PURPLE','teal','FUCHSIA'];
var g_hide_color='grey';
var g_total_nm='TOTAL';
var g_chart_countries=[];   
var g_max_chart_serial_allowed=6;
var g_chart_rec_seq;
var g_map_initiated=false;
var g_legend_initiated=false;

var g_json_cache=[]; 
var g_fence_def=[];
const g_labels = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

slider_listenr();
g_chart_countries.push(g_total_nm);

function loadHelpModel(){
    var modal = document.getElementById("myModal");
    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");
    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];
    // When the user clicks the button, open the modal 
    btn.onclick = function() {
      modal.style.display = "block";
    }
    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
      modal.style.display = "none";
    }
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
}    
function phone_change_old() {
    var listbox = document.getElementById("select_phone");
    var selIndex = listbox.selectedIndex;
    var selValue = listbox.options[selIndex].value;
    var selText = listbox.options[selIndex].text;
    
     g_selected_phone=[];
     // first prompt length less than 10
    if( selValue.length>10 ){
        g_selected_phone.push(selValue);
    }
    else{   
     
        // alert("this is not valid option:"+selValue);
        
    }
    resetMap(false);
    populate_data();
    updateMarker(0,true);
    setmapzoom(g_base_data_hash[0].cnt);
    setSummaryhtml(g_days_2_dstr[0],g_base_data_hash[0].cnt, 0 );
    setRangehtml(0);

 }
 
function phone_change() {
        var listbox = document.getElementById("select_phone");
    var selIndex = listbox.selectedIndex;
    var selValue = listbox.options[selIndex].value;
    var selText = listbox.options[selIndex].text;
    
     g_selected_phone=[];
     // first prompt length less than 10
    if( selValue.length>10 ){
        g_selected_phone.push(selValue);
    }
    else{   
     
        // alert("this is not valid option:"+selValue);
        
    }
     source_change();

 }
  
 
function source_change(){
    var listbox = document.getElementById("select_source");
    var selIndex = listbox.selectedIndex;
    var selValue = listbox.options[selIndex].value;
    var selText = listbox.options[selIndex].text;
    
     g_selected_source=selValue;
     // first prompt length less than 10
    if( selValue.length>10 ){
        // g_selected_phone.push(selValue);
    }
    else{   
     
        // alert("this is not valid option:"+selValue);
        
    }
    
    resetMap(true);
    initMap();
//    populate_data();
    // updateMarker(0,true);
    // setmapzoom(g_base_data_hash[0].cnt);
    // setSummaryhtml(g_days_2_dstr[0],g_base_data_hash[0].cnt, 0 );
    // setRangehtml(0);

}    


function hideUnwanted(){
    // document.getElementById("select_phone").style.display = "none";
    document.getElementById("select_source").style.display = "none";


}

    
function resetMap(reset_all){
    for( var country_nm in g_markers){
        var marker= g_markers[country_nm]['marker'];
        var circle= g_markers[country_nm]['circle'];
       // if selected phone then hide unselected
      //  if( g_selected_phone.length > 0 && g_selected_phone.indexOf(country_nm) < 0){
        if(true){
          marker.setMap(null);
          circle.setMap(null);
       }
       else{
           marker.setMap(map);
          circle.setMap(map);
       }
    }
    
    g_countries=[];
    g_base_data_hash=[];    
    g_days_2_dstr={};
    g_max_rec_seq=0;
    if(reset_all){
        g_markers=[];
    }
}
    

function get_infowindow_content(seq){
 var contentString =
    '<div id="content'+seq+'">' +
    '<h1>'+g_base_data_hash[seq].country +'</h1>' +
    "<p><b>Time:</b>" + g_days_2_dstr[seq]+
    "<p><b>Location:</b>" + g_base_data_hash[seq].position.toString()+ 
    "<p><b>Radius:</b>" + g_base_data_hash[seq].cnt + 
    "<p><b>Source:</b>" + g_base_data_hash[seq].source_cd  
    "</div>";
  return contentString;
}

    
function initMap() {
    populate_data();
    pupulate_select_options();
    hideUnwanted();

    if( !g_map_initiated){
        map = new google.maps.Map(
        document.getElementById('map'),
        {center: new google.maps.LatLng(44, -70), zoom: 11,  mapTypeId: 'terrain', scaleControl: true});
        g_map_initiated=true;
    }
    g_radius=0;
    g_country_cnt=0;
    var latlng = new google.maps.LatLng(g_position[0], g_position[1]);
    var str_days_ago='<?php echo getParameter('date'); ?>';
    var arr_days_ago_keys= Object.keys(g_days_2_dstr);
    g_chart_rec_seq=g_max_rec_seq;
    for(var i=0; i< arr_days_ago_keys.length; i++){
        if(g_days_2_dstr[i] ==  str_days_ago){
            g_chart_rec_seq=i;
            break;
        }        
    }

    var radius=0;
    var i = 0;
    
    for( i=0; i < g_base_data_hash.length; i++){
        var country_nm= g_base_data_hash[i].country;
        
        //marker already created
        if( g_markers.hasOwnProperty(country_nm)) {
            updateMarker(i,false);
            // break;
        }
        else {
            draw_fence(country_nm); 
            g_country_cnt++;
            var marker;
            var icon=get_icon(i);          
             marker = new google.maps.Marker({
                position: g_base_data_hash[i].position ,  
                url:g_base_data_hash[i].country,
                map: map,
                title: g_base_data_hash[i].country ,
                icon: icon,
                label: g_labels[(g_country_cnt-1) % g_labels.length]
               // icon: {
                  // url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                // }
            });
        
            radius = g_base_data_hash[i].cnt;
                    // Add circle overlay and bind to marker
            var circle = new google.maps.Circle({
              map: map,
              strokeOpacity: 0.2,
              strokeColor:g_base_data_hash[i].source_cd =='GMLC' ?  '#ffb833' : '#ff8080',
              radius: g_base_data_hash[i].cnt,    // 10 miles in metres
              // fillColor: i %2 == 0 ? '#AA0000' : '#00AA00'
              fillColor:   g_base_data_hash[i].source_cd =='GMLC' ?  '#ffb833' : '#ff8080'
            });
            circle.bindTo('center', marker, 'position');
            
            var infowindow = new google.maps.InfoWindow({
                content: get_infowindow_content(i)
            });
                      
            marker.addListener('click', function() {
                var country_nm=this.url;
                    //draw_chart1(this.url);
                    g_markers[country_nm]['info'].open(map, g_markers[country_nm]['marker']);
            });
            
          // marker.addListener("click", () => {
            // infowindow.open(map, marker);
          // });            
    
            g_markers[country_nm]={};   
            g_markers[country_nm]['marker']= marker ;
            g_markers[country_nm]['circle']= circle ;
            g_markers[country_nm]['info']= infowindow ;
            // g_markers[country_nm]['marker'].addListener("click", () => {
                // var title=this.title;
                // g_markers[country_nm]['info'].open(map, g_markers[country_nm]['marker']);
          // });        
            var latLng = marker.getPosition(); // returns LatLng object
            map.setCenter(latLng); // setCenter takes a LatLng object
            g_radius=g_base_data_hash[i].cnt;
            // setmapzoom(radius);
        }
        setSummaryhtml(g_days_2_dstr[i],g_radius, i );
        setRangehtml(i);
        g_delay_rec_seq=i;
        if( g_country_cnt ==g_countries.length){
            setmapzoom(radius);
            break;
        }
    }// end for
    
    // if( !g_legend_initiated){
        create_legends();
        // g_legend_initiated=true;
    // }
        
    google.maps.event.addListener(map, "rightclick", function(event) {
        var lat = event.latLng.lat();
        var lng = event.latLng.lng();
        // populate yor box/field with lat, lng
        alert("Lat=" + lat + "; Lng=" + lng);
    });
    
}// end of initMap

function get_infowindow_fence(msisdn){
    return "get_infowindow_fence"+msisdn;
}

function draw_fence(msidsn){
    //remove postfixed GMLC or PDC
    msidsn=msidsn.replace(/[A-Z]+$/g, "");
    
    if( g_fence_def.hasOwnProperty(msidsn)  ) {
       if ( g_fence_def[msidsn].draw < 1) {
            var circle3 = new google.maps.Circle({
              map: map,
              strokeOpacity: 0.5,
              strokeColor:'black' ,
              radius: g_fence_def[msidsn].radius,   
              center: new google.maps.LatLng( g_fence_def[msidsn].lat, g_fence_def[msidsn].lng),
              fillColor:    'white'  });
              
                        
            var infowindow = new google.maps.InfoWindow({
                content: get_infowindow_fence(msidsn)
            });  
            g_fence_def[msidsn].info= infowindow;    
            circle3.addListener('click', function() {
                    //draw_chart1(this.url);
                   var msg="this is fence for "+msidsn+"("+ g_fence_def[msidsn]['lat'] +","+ g_fence_def[msidsn]['lng'] +"), radius:" +g_fence_def[msidsn]['radius'];
                   alert(msg);
                   // g_fence_def[msidsn]['info'].open(map, g_fence_def[msidsn]['fence']);
            });              
            g_fence_def[msidsn].fence= circle3;    
            g_fence_def[msidsn].draw=1;            
            
       }
     }
    
}



function create_legends(){
    var gmlc=getColorCircle('orange',g_cir_size) ;
     var icons = {
      GMLC: {
        name: 'GMLC',
        icon:  'images/orange_icon.png'
      } 
    };
    
    var legend = document.getElementById('legend');
    while( legend.hasChildNodes()){
        legend.removeChild(legend.childNodes[0]);   
    }
    
    for (var key in icons) {
      var type = icons[key];
      var name = type.name;
      var icon = type.icon;
      var div = document.createElement('div');
      div.innerHTML = '<img src="' + icon + '"> ' + name;
      legend.appendChild(div);
    }
    
    var sorted_list=g_countries.sort();
    
      for( var i = 0; i < sorted_list.length; i++){
                  // g_markers[country_nm]['marker'].setPosition(g_base_data_hash[no].position);
              var marker= g_markers[sorted_list[i]]['marker'];
            var name = marker.getTitle();
            var alias = marker.getLabel();          
         var div = document.createElement('div');
        div.innerHTML = '<B>' + alias + '</B>:' + name;
        legend.appendChild(div);        
              
     }
     
    if(!g_legend_initiated){
        map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);    
        g_legend_initiated=true;
    }

}
      
function get_icon(no){
    var source_cd=g_base_data_hash[no].source_cd;
        if(source_cd =='GMLC'  ){
             return getColorCircle('orange',g_cir_size) ;
        }
        else{
             return getColorCircle('red',g_cir_size) ;
        }
 

}    

function updateMarker(no, recenter){
     var country_nm= g_base_data_hash[no].country;
     var source_cd=g_base_data_hash[no].source_cd;
        g_markers[country_nm]['marker'].setPosition(g_base_data_hash[no].position);
        g_markers[country_nm]['circle'].setRadius(g_base_data_hash[no].cnt);
        var icon=get_icon(no);
        g_markers[country_nm]['marker'].setIcon(icon);
        
           g_markers[country_nm]['marker'].setMap(map);
          g_markers[country_nm]['circle'].setMap(map);        
          g_markers[country_nm]['info'].setContent(get_infowindow_content(no));        
        

        
     if(recenter){   
        var latLng = g_markers[country_nm]['marker'].getPosition(); // returns LatLng object
        map.setCenter(latLng); // setCenter takes a LatLng object
     }
        // radius= g_base_data_hash[country].cnt[v_days_ago] > radius ? g_base_data_hash[country].cnt[v_days_ago]:radius;
    // setmapzoom(radius);
    // radius_str = radius_str.slice(0, -1); 
     setSummaryhtml(g_days_2_dstr[no],g_base_data_hash[no].cnt, no );
         
    
    
}
      
      
function add_infowindow(map){
    var contentString = '<div id="content">'+
          '<div id="siteNotice">'+
          '</div>'+
          '<h1 id="firstHeading" class="firstHeading">Uluru</h1>'+
          '<div id="bodyContent">'+
          'https://en.wikipedia.org/w/index.php?title=Uluru</a> '+
          '(last visited June 22, 2009).</p>'+
          '</div>'+
          '</div>';
    var hudsonbay = new google.maps.LatLng(57.761322, -84.527044);
    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });
    var marker = new google.maps.Marker({
      position: hudsonbay,
      map: map,
      title: 'hudsonbay (Ayers Rock)'
    });
    marker.addListener('click', function() {
      infowindow.open(map, marker);
    });
}       

function setSummaryhtml(dstr, r_cnt,c_cnt){
    document.getElementById('dstr').innerHTML = 'Local Time<b><br>'.concat(dstr,'</b>');    
    document.getElementById('country_cnt').innerHTML = 'Seq<b><br>'.concat(formatNumber(c_cnt),'</b>');    
    document.getElementById('roamer_cnt').innerHTML = 'Radius<b><br>'.concat(r_cnt,'</b>');    
}

function setRangehtml(val){
    var slider = document.getElementById("myRange");
    slider.value=val;
    // document.getElementById("demo").innerHTML=slider.value;
}      

function setRangeMax(val){
    var slider = document.getElementById("myRange");
    slider.max=val;
}      

// input 1 - 100,000k - return 0
function get_circle_size(cnt){
    if(cnt > 0){
        return 5+Math.log(cnt)*2;
        
    }
    return 0;
}

function draw_chart(){
    var countries='<?php echo getParameter('countries'); ?>';
    countries=countries.length<2?'USA':countries;
    draw_chart1(countries);
 
   
}

function draw_chart1(v_countries){
    // it not in list, add it 
    var v_arr=v_countries.split(",");
    for(var i=0; i<v_arr.length; i++){
        var v_ctry=formatCountryNm(v_arr[i]);
        if( g_countries.indexOf(v_ctry)>=0){
            if(g_chart_countries.indexOf(v_ctry) < 0){
                g_chart_countries.push(v_ctry);
                if( g_chart_countries.length > g_max_chart_serial_allowed){  // move oldest one, and reordering color
                    g_chart_countries.splice(1,1);
                    g_color_list.push(g_color_list.splice(1,1)[0]);    
                }
            }     
            else{ // remove from chart
                if(g_chart_countries.length>1){
                    var idx=g_chart_countries.indexOf(v_ctry);
                    g_chart_countries.splice(g_chart_countries.indexOf(v_ctry),1);
                    g_color_list.push(g_color_list.splice(idx+1,1)[0]);   // resync color   
                    // g_total_in_chart=true;
                    // document.getElementById("checkbox_total").checked = true;
                }
            }
        } // end if valid country
    }
    draw_line_chart(false);
}

//draw without change chart countries
function draw_line_chart(b_draw_all){
    var data = new google.visualization.DataTable();
    // headers
    var color_option=[];

    data.addColumn('date', 'EVENT_DATE');  
    for(var i=0; i < g_chart_countries.length; i++){
       data.addColumn('number', g_chart_countries[i]);
       color_option.push(  g_chart_hid_countries.indexOf(g_chart_countries[i]) < 0 ? g_color_list[i] :g_hide_color ) ;
    }
    
    // data row
    var basedate=g_days_2_dstr[g_max_rec_seq];
    var max_vaxis=0;
    var max_val=0;
    var min_val=9999999;
    for(var days_ago= g_max_rec_seq; days_ago >= g_min_rec_seq; days_ago --){
        var dstr_in_comma=g_days_2_dstr[days_ago].replace("-", ",");
        var dt=new Date(dstr_in_comma);
        var row1=[dt];
        for(var i=0; i < g_chart_countries.length; i++){
            var cnt=g_base_data_hash[g_chart_countries[i]].cnt[days_ago];
            cnt = g_chart_hid_countries.indexOf(g_chart_countries[i]) < 0 ? cnt: null; 
            if(cnt != null){
                max_vaxis=cnt>max_vaxis?cnt:max_vaxis;
                max_val=cnt>max_val? cnt:max_val;
                min_val=cnt<min_val? cnt:min_val;
            }
            row1.push(! b_draw_all && days_ago < g_delay_rec_seq? null:cnt) ;
        }
        data.addRows([row1]);
    }
    
    v_logscale= Math.abs(max_val)/(Math.abs(min_val)+0.0001) > 100;
    // options 
    var c_title="Daily Telus Roamers Count by Countries/Provinces";
    var options = {
        title: c_title,
        curveType: 'none',
        colors:color_option,
        height:$(window).width()*0.16,
        width:$(window).width()*0.41,
        focusTarget: 'category',
        hAxis: {  minValue: new Date(g_days_2_dstr[g_max_rec_seq]),maxValue: new Date(g_days_2_dstr[g_min_rec_seq]) },         
        vAxis: { title:v_logscale?'logscale is ON':'', minValue:0, maxValue:max_vaxis*1.1,logScale:v_logscale},    
        legend: { position: 'bottom' }
    };
    
    // dataView = new google.visualization.DataView(data);

    var chart = new google.visualization.LineChart(document.getElementById('chart1'));
    google.visualization.events.addListener(chart, 'click', function (target) {
      if (target.targetID.match(/^legendentry#\d+$/)) {    
        var index = parseInt(target.targetID.slice(12)) ;
        var country= index < g_chart_countries.length ?  g_chart_countries[index] :'TOTAL';
        var hid_idx=g_chart_hid_countries.indexOf(country);
        if( hid_idx<0){
            g_chart_hid_countries.push(country);
        }
        else{
            g_chart_hid_countries.splice(hid_idx,1);
        }
        draw_line_chart(b_draw_all);
      }
    });
    chart.draw(data, options);
  
}// end of draw_line_chart
      
       
// given 2020-02-21, return Feb 21      
function format_YMD2Mon(yyyy_mm_dd){
    yyyy_mm_dd=yyyy_mm_dd.replace("-", ",");  // using comma, the day will not impacted by timezone
    const d = new Date(yyyy_mm_dd);
    const ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d)
    const mo = new Intl.DateTimeFormat('en', { month: 'short' }).format(d)
    const da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d)
    return mo.concat(' ',da);  // return 'Feb 21'
}    
       
function pupulate_select_options(){
    removeAllOptions();
    
    phonelist=[];
    
    for( var i = 0; i < g_countries.length; i++){
        // delete unselected markers
        var country_nm=g_countries[i];
        // country_nm=country_nm.replace(/[A-Z]+$/g, "");
        if(phonelist.indexOf(country_nm) < 0){
            phonelist.push(country_nm);
        }
    }
    appendOptionToSelect('Choose a number','null',false);
    
    phonelist=phonelist.sort();
    for( var i = 0; i < phonelist.length; i++){
        // delete unselected markers
        var country_nm=phonelist[i];
        if(phonelist.length == 1){
            appendOptionToSelect(country_nm,country_nm,true);
        }
        else{
            appendOptionToSelect(country_nm,country_nm,false);
        }
    }

}    



function populate_fence_def(){
    if(g_fence_def.length < 1){
        var jsonData = $.ajax({
                  url: "get_pdc_gmlc_fence_data.php<?php echo "?".$g_query;  ?>",
                  dataType: "json",
                  async: false
                  }).responseText;
                  
        const fence_jsn = JSON.parse(jsonData);
        
         for(var i = 0; i < fence_jsn.length; i++){
             var msisdn= fence_jsn[i].msisdn;
             g_fence_def[msisdn]={};
             //"lat":43.956167,"lng":-79.266823,"radius":1000,"msisdn":"1416-882-93**"}
              g_fence_def[msisdn].lat=fence_jsn[i].lat;
              g_fence_def[msisdn].lng=fence_jsn[i].lng;
              g_fence_def[msisdn].radius=fence_jsn[i].radius;
              g_fence_def[msisdn].draw=0;
         }
        
    }
}

            
function populate_data(){
    // populate_fence_def();
    if(g_json_cache.length < 1){
        var jsonData = $.ajax({
                  url: "get_pdc_gmlc_tracking_map_data.php<?php echo "?email=".$gemail."&token=".$gtoken."&".$g_query;  ?>",
                  dataType: "json",
                  async: false
                  }).responseText;
                  
        const features = JSON.parse(jsonData);
        g_json_cache=features;
    }
    
    
   
    // [{"lat":43.977302,"lng":-79.279164,"cnt":3509,"country":"+14168829337_NEW","date_str":"2020-07-14 02:05 EST | SRC:PDC","days_ago":2}
    g_min_rec_seq=0;
    var seq=g_min_rec_seq;
    for(var i = g_min_rec_seq; i < g_json_cache.length; i++){
      
        var country_nm=g_json_cache[i].msisdn;
        
        // choose phone number
        if(  g_selected_phone.length > 0 && g_selected_phone.indexOf(country_nm) < 0  ){
            continue; 
        }
        
        
        // choose source 
        if( ( g_selected_source == 'PDC' || g_selected_source == 'GMLC') 
              &&  g_json_cache[i].source_cd != g_selected_source ) {
            continue;
        }
        else if( g_selected_source == 'P' ){
            country_nm = country_nm + g_json_cache[i].source_cd ;
        }        
        
        
        
        
        if(seq > g_max_rec_seq ){
            g_max_rec_seq=seq;
        }
        
        if(g_countries.indexOf(country_nm) < 0){
            g_countries.push(country_nm);
        }
        if(! g_base_data_hash.hasOwnProperty(seq)){
            g_base_data_hash[seq]={cnt:{}, position:{},country:{}};
        }
        if(! g_days_2_dstr.hasOwnProperty(i)){
            g_days_2_dstr[seq]= utc2localtimestr( g_json_cache[i].date_str);
        }

        g_base_data_hash[seq].position = new google.maps.LatLng(g_json_cache[i].lat,g_json_cache[i].lng);        
        g_base_data_hash[seq].cnt =g_json_cache[i].event_radius;
        g_base_data_hash[seq].country =country_nm;
        g_base_data_hash[seq].source_cd =g_json_cache[i].source_cd;
        // save total count by day for chart
        seq++;
    } ;


    setRangeMax(g_max_rec_seq-g_min_rec_seq);
    return;
  
}
  

function utc2localtimestr(utc_str){
    var d = new Date(utc_str);
    const year = d.getFullYear(); // 2019
    var month=d.getMonth()+1;
    month = month>= 10 ? month: "0"+month;
    const date = d.getDate() >= 10 ? d.getDate() : "0"+d.getDate(); // 23    
    const hour = d.getHours()>= 10 ? d.getHours() : "0"+d.getHours(); 
    const minutes=d.getMinutes()>= 10 ? d.getMinutes() : "0"+d.getMinutes(); 
    const rtn=year+"-"+month+"-"+date+" "+hour+":"+minutes;
    return rtn;
}  
  

function play_animation(){
    if(!g_autoplay){
        g_autoplay=true;
        if(g_delay_rec_seq > g_max_rec_seq ){
            g_delay_rec_seq=g_min_rec_seq;
        }
        document.getElementById("input_play").src="images/pause.png";
        moveMarker();
    }
    else{
        g_autoplay=false;
        document.getElementById("input_play").src="images/play.png";
    }
    
}

function is_domestic(str){
    if(g_can_provs.indexOf(str) < 0){
        return false;
    }
    return true;
}

function play_move(diff){
    g_delay_rec_seq=g_delay_rec_seq+diff;
    show_by_delay_days_ago(g_delay_rec_seq);
}

function show_by_delay_days_ago(v_days_ago){
    updateMarker(v_days_ago,false);
    // if one or more marker out of bound, rezoom
    if(countVisibleMarkers() < g_countries.length){
        setmapzoom(0);
    }
    return;
     
}



function moveMarker(){
    show_by_delay_days_ago(g_delay_rec_seq);
//    setRangehtml(g_max_rec_seq-g_delay_rec_seq);
    setRangehtml(g_delay_rec_seq);
    g_delay_rec_seq++;
    if(g_delay_rec_seq > g_max_rec_seq){
      // document.getElementById("play_button").disabled = false;
      g_autoplay=false;
    }
    if(g_delay_rec_seq <= g_max_rec_seq && g_autoplay){
        setTimeout(moveMarker, delay);
    }              
}

function slider_listenr(){
    var slider = document.getElementById("myRange");
    // var output = document.getElementById("demo");
    // output.innerHTML = slider.value;

    slider.oninput = function() {
      // output.innerHTML = this.value;
      g_autoplay=false;  // stop autoplay if going
      // document.getElementById("play_button").disabled = false; // enable play button
      //g_delay_rec_seq=g_max_rec_seq-Number(this.value);
      g_delay_rec_seq=Number(this.value);
      show_by_delay_days_ago(g_delay_rec_seq);
    }
}
      
function slider_ev(){
  var slider = document.getElementById("myRange");
  var val_num=Number(slider.value);
  slider.value=val_num;
  
}

function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
}  

//bosnia and herzegovin -> Bosnia and Herzegovin 
function formatCountryNm(country) { 
    if(is_domestic(country) || country=="USA" ){
        return country;
    }
    else{
        var words=country.toLowerCase().split(" ");
        for(var i=0; i<words.length; i++){
            if(words[i]!="and"){
                words[i]= words[i].charAt(0).toUpperCase() + words[i].substring(1);
            }
        }
        var ret=words.join(" ");
        return ret;
    }
}  

function setmapzoom(cnt){
 
    
        // var markers = //some array;
        var bounds = new google.maps.LatLngBounds();
          for( var i = 0; i < g_countries.length; i++){
                  // g_markers[country_nm]['marker'].setPosition(g_base_data_hash[no].position);
              var marker= g_markers[g_countries[i]]['marker'];
              bounds.extend(marker.getPosition());
              
          }
     

        //center the map to a specific spot (city)
        // map.setCenter(center); 

        //center the map to the geometric center of all markers
        map.setCenter(bounds.getCenter());

        map.fitBounds(bounds);

        var listener = google.maps.event.addListener(map, "idle", function() { 
          if (map.getZoom() > 11) map.setZoom(11); 
          google.maps.event.removeListener(listener); 
        });
        
        // set zoom when one marker
        if( g_countries.length <=1){
            if( cnt  <=1000) {
                   map.setZoom(11);
            }
            else if(cnt > 1000 && cnt  <=10*1000) {
                 map.setZoom(10);
            }
            else if(cnt > 10*1000 && cnt  <=20*1000) {
                 map.setZoom(9);
            }
            else if(cnt > 20*1000 && cnt  <=35*1000) {
                 map.setZoom(8);
            }
            else if(cnt > 35*1000 && cnt  <=50*1000) {
                 map.setZoom(7);
            }
            else  {
                 map.setZoom(6);
            }
        }
        return ;    
   
}



function countVisibleMarkers() {
    var bounds = map.getBounds(),
        count = 0;
                                
    for( var i = 0; i < g_countries.length; i++){
              // g_markers[country_nm]['marker'].setPosition(g_base_data_hash[no].position);
          var marker= g_markers[g_countries[i]]['marker'];
        if(bounds.contains(marker.getPosition())===true) {
            count++;
        }          
    }
    return count;
}



function showVisibleMarkers() {
    var bounds = map.getBounds(),
        count = 0;
                                   
    for (var i = 0; i < markers.length; i++) {
        var marker = markers[i],
            infoPanel = $('.info-' + (i+1) ); // array indexes start at zero, but not our class names :)
                                           
        if(bounds.contains(marker.getPosition())===true) {
            infoPanel.show();
            count++;
        }
        else {
            infoPanel.hide();
        }
    }
    
    $('#infos h2 span').html(count);
}

      // get global color and size for circles
function cal_circle_color_size(cnt, country){
    if( cnt  <=10) {
         g_cir_color=is_domestic(country)?'#E9F7EF': '#F4D03F';
    }
    else if(cnt > 10 && cnt  <=100) {
        g_cir_color=is_domestic(country)?'#D4EFDF':'#F1C40F';
    }
    else if(cnt > 100  && cnt <=1000) {
         g_cir_color=is_domestic(country)?'#A9DFBF':'#F39C12';
    }
    else if(cnt > 1000  && cnt <=1000*3) {
        g_cir_color=is_domestic(country)?'#7DCEA0':'#BA4A00';
    }
    else if(cnt > 1000*3  && cnt <=1000*10) {
        g_cir_color=is_domestic(country)?'#52BE80':'#873600';
    }
    else if(cnt > 1000*10  && cnt <=1000*100) {
          g_cir_color=is_domestic(country)?'#27AE60':'#F1948A';
    }
    else  {
        g_cir_color=is_domestic(country)?'green':'red';
    }
   if(cnt > 0){
          g_cir_size= 5+Math.log(cnt)*2;
    }
    else if( cnt  < 0){
        // this if few small domestic prov temp has defict
        g_cir_size = 5+Math.log(cnt*-1)*2;
        g_cir_color= '#F39C12';
    }
    else{
        g_cir_size=0;
    }
    
    return ;
    // todo 
}
           
      
function getColorCircle(color,magnitude) {
    return {
      path: google.maps.SymbolPath.CIRCLE,
      fillColor: color,
      fillOpacity: .8,
      scale: magnitude,
      strokeColor: 'white',
      strokeWeight: .5
    };
}   


 //removes all option elements in select box 
// removeGrp (optional) boolean to remove optgroups
function removeAllOptions() {
    var sel =  document.getElementById("select_phone");
    var len, groups, par;
    len = sel.options.length;
    for (var i=len; i; i--) {
        par = sel.options[i-1].parentNode;
        par.removeChild( sel.options[i-1] );
    }
    
}

function appendDataToSelect(sel, obj) {
    var f = document.createDocumentFragment();
    var labels = [], group, opts;
    
    function addOptions(obj) {
        var f = document.createDocumentFragment();
        var o;
        
        for (var i=0, len=obj.text.length; i<len; i++) {
            o = document.createElement('option');
            o.appendChild( document.createTextNode( obj.text[i] ) );
            
            if ( obj.value ) {
                o.value = obj.value[i];
            }
            
            f.appendChild(o);
        }
        return f;
    }
    
    if ( obj.text ) {
        opts = addOptions(obj);
        f.appendChild(opts);
    } else {
        for ( var prop in obj ) {
            if ( obj.hasOwnProperty(prop) ) {
                labels.push(prop);
            }
        }
        
        for (var i=0, len=labels.length; i<len; i++) {
            group = document.createElement('optgroup');
            group.label = labels[i];
            f.appendChild(group);
            opts = addOptions(obj[ labels[i] ] );
            group.appendChild(opts);
        }
    }
    sel.appendChild(f);
}


function appendOptionToSelect(text, value, selected) {
     // var sel =  document.getElementById("select_phone");
     
      var daySelect = document.getElementById('select_phone');
     daySelect.options[daySelect.options.length] = new Option(text, value);
      if( selected){
           daySelect.value = value;
      }
  
}

</script>
 <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAqIrZpo5haENVX9SwFc28yRwkyxubc2Hs&callback=initMap">
</script>
    
<?php 
$g_conn;
$g_query;
$g_query_b;
$g_is_diff_chart;
$g_option;
$g_charttype;
$g_column_name_types;
$Configs;
$Debug;


     
 

function populateChartOptions(){
    global $g_conn,$g_query,$g_option,$g_charttype;
    echo $g_option;
}        

function getParameter($para){
    global $g_conn,$g_query;
    $rtn="";
    global $g_host;
    if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
        $url="http://".$g_host."/raid_apache_home/googlechart/raid/chart_from_table.php";
    }
    else{
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    $parts = parse_url($url);
    $g_query=$parts['query'];
    parse_str($parts['query'], $query);     
    $rtn=isset($query["$para"])? $query["$para"]: "";
    // mydumpvar($rtn ,"$para");
    return  $rtn;
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

         

function access_control(){
    global $Configs,$Debug;
    if ( isset( $_SERVER['HTTP_HOST'] ) ) {
        mydumpvar("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",'url');
    }
    
    if( check_login() < 1){
        display_remote_ip();
        exit('<hr><p><font  size=4  face=verdana color=red> Access denied, if you believe you have access, please try logout and login again! Please contact dlocskpiadmin@telus.com if issue persists<hr>');
    }
}

function display_remote_ip(){
    echo "<p><font  size=1  face=verdana color=grey>IP:".getRealIpAddr()."</font></p>";
}

  
/**
 * return: 1-OK, have access.  0-No access
 */
function check_login(){
    global $Configs,$g_conn;
    $ip=getRealIpAddr();
    $group=str_replace(',',"','",$Configs['general']['google_chart_allowed_group']);
    // $group=$Configs['general']['allowed_group'];
    
    // Connects to the XE service (i.e. database) on the "localhost" machine
    connectDb();
    if (!$g_conn) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }

    $query="select * from PORTALADM.user_ where LOGINIP='".$ip."'
    and screenname in (
    select screenname from portaladm.USER_
    where userid in (
    select distinct userid From portaladm.USERS_USERGROUPS
    where usergroupid in (select usergroupid from  
    portaladm.USERGROUP where upper(name) in ('".$group."'))))";
        
    mydumpvar($query,'check_login query');
    
    $stid = oci_parse($g_conn, $query);
    if (!$stid) {
        $e = oci_error($g_conn);  // For oci_parse errors pass the connection handle
        trigger_error(htmlentities($e['message']), E_USER_ERROR);
    }

    oci_execute($stid);

    $rowcnt=0;
    while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
        $rowcnt++;
    }
    return $rowcnt;
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


function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
    }
    return $ip;
}

function getBrowser(){
    $browser = get_browser(null, true);
    return implode(",", $browser); 
}
 

        
?>  





</body>
</html> 
