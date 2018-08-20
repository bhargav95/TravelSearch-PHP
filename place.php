<?php
    session_start();
?>

<?php

    //var_dump($_POST);

    $API_KEY="AIzaSyD3s6OJpYDXIgCj0EZST4oOvKjVdsL8qLc";
    $MAPS_KEY = "AIzaSyD0PS6SDd-KQ0fAIiGRK10mNAsXRSwbV50";
    $GEO_API="AIzaSyAm5mFu-skF-aFf3aNNTo3K6YmZlN_-wLQ";
    $PLACES_KEY=$API_KEY;

    $Category=array("default","cafe","bakery","restaurant", "beauty salon", "casino", "movie theater", "lodging", "airport", "train station","subway station", "bus station");



    
    
    if(isset($_POST["placeid"])){
        
        $place = file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?placeid='.$_POST["placeid"].'&key='.$PLACES_KEY);
        
        $placej = json_decode($place,true);
        
        if(array_key_exists("photos",$placej["result"])){
            $photoref = $placej["result"]["photos"];
        
            for($i = 0; $i<5 && $i<count($photoref); $i++){
                //echo $i;

                $highref=file_get_contents('https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference='.$photoref[$i]["photo_reference"].'&key='.$PLACES_KEY);

                file_put_contents('image'.$i.'.jpg',$highref,LOCK_EX);
            }    
        }
        
        //$highref = file_get_contents('https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreferene='.$photoref.'&key='.$PLACES_KEY);
        
        //echo getcwd();
        echo json_encode($placej);
        
        //echo var_dump($placej["result"]["reviews"]);
        
        exit();
    }

    $google="{}";
    $cat = 0;
    $distance = "";
    $locatetext = "";
    $radiobut = "radiohere";
    $keywordspace="";

    if(isset($_POST["submit"])){

        $cat = $_POST["category"];
        $type=preg_replace('/\s+/', '+',$Category[$cat]);
        $keywordspace=$_POST["keyword"];
        $keyword=preg_replace('/\s+/', '+', $_POST["keyword"]);
        
        
        $dist=16000;
        
        $distance=$_POST["distance"];
        if($distance){
            $dist=$_POST["distance"]*1600;
        }
        
        
        $radiobut = $_POST["loc"];
        
        if($radiobut=="radiohere"){
            $lat=$_POST["latitude"];
            $lon=$_POST["longitude"];
        }
        else{
            
            $locatetext = $_POST["myloc"];
            $geo_add= preg_replace('/\s+/', '+', $locatetext);
            $geocode=file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$geo_add.'&key='.$GEO_API);
            
            $geojson=json_decode($geocode,true);
            
            
            $lat=$geojson["results"][0]["geometry"]["location"]["lat"];
            $lon=$geojson["results"][0]["geometry"]["location"]["lng"];
        }
        
        $google = file_get_contents('https://maps.googleapis.com/maps/api/place/nearbysearch/json?location='.$lat.','.$lon.'&radius='.$dist.'&type='.$type.'&keyword='.$keyword.'&key='.$API_KEY);
        
    }    
?>

<html>

        <head>
            <title>PHP</title>
            <style>
                body h3 {
                    text-align: center;
                    font-style: italic;
                    font-size: 30px;
                    margin-bottom: 5px;
                }
                
                    
                #travelform {
                    background-color: whitesmoke;
                    border: 2px solid lightgrey;
                    padding-left: 20px;
                    padding-right: 20px;
                    margin: auto;
                    margin-bottom: 50px;
                    width: 700px;
                }

                #travelform span {
                    margin: 10px;
                    margin-left: 0px;
                }

                #subut {
                    margin-left: 20%;
                }
                
                #restable{
                    position: relative;
                }
                
                #restable td{
                    text-align:left; 
                    vertical-align:middle;
                }
                
                #showphotos td,#showrevs td, #placename{
                    
                    text-align:center; 
                    vertical-align:middle;
                    margin:auto;
                }
                
                #placename{
                    font-size: 20px;
                    font-weight: bold;
                }
                
                #revbutton, #photobutton{
                    text-align: center;
                    margin: auto;
                }
                
                #revbutton img,#photobutton img{
                    cursor: pointer;
                    height: 20px;
                    padding: 0px;
                    padding-bottom: 10px;
                }
                
                .pointy{
                    cursor: pointer;
                }
                
                .directions{
                    display:none;
                    background-color: beige;
                    padding:10px;
                    position:absolute; 
                    z-index:2;
                }
                
                table, td, th {    
                    border: 1px solid lightgrey;
                    padding-left: 20px;
                }

                table {
                    border-collapse: collapse;
                    width: 80%;
                }
                
            </style>
            <script>
                
                var goog="";
                var revb=0;
                var revp=0;
                var photob=0;
                
                function showreview(){
                    if(revb==0){
                        document.getElementById("revarrow").src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                        document.getElementById("showhiderev").innerHTML="click to hide reviews";
                        
                        document.getElementById("showrevs").style.display="block";
                        
                        document.getElementById("photoarrow").src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                        document.getElementById("showhidephoto").innerHTML="click to show images";
                        document.getElementById("showphotos").style.display="none";
                        revp=0;
                    }
                    else{
                        document.getElementById("revarrow").src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                        document.getElementById("showhiderev").innerHTML="click to show reviews";
                        
                        document.getElementById("showrevs").style.display="none";
                    }
                    
                    revb = 1 - revb;
                }
                
                function showphoto(){
                    if(revp==0){
                        document.getElementById("photoarrow").src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                        document.getElementById("showhidephoto").innerHTML="click to hide images";
                        
                        document.getElementById("showphotos").style.display="block";
                        
                        document.getElementById("revarrow").src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                        document.getElementById("showhiderev").innerHTML="click to show reviews";
                        document.getElementById("showrevs").style.display="none";
                        revb=0;
                    }
                    else{
                        document.getElementById("photoarrow").src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                        document.getElementById("showhidephoto").innerHTML="click to show images";
                        
                        document.getElementById("showphotos").style.display="none";
                    }
                    
                    revp = 1 - revp;
                }
                
                function getimagesandreviews(x){
                                        
                    var http = new XMLHttpRequest();
                    var url = "place.php";
                    var params = "placeid="+goog.results[x].place_id;
                    http.open("POST", url, true);

                    //Send the proper header information along with the request
                    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                    http.onreadystatechange = function() {//Call a function when the state changes.
                        if(http.readyState == 4 && http.status == 200) {
                            
                            console.log(http.responseText);
                            
                            
                            
                            var revphotos = JSON.parse(http.responseText);
                            
                            var revs;
                            if("reviews" in revphotos["result"]){
                                revs=revphotos["result"]["reviews"];
                            }
                            else{
                                revs=[];
                            }
                            
                            
                            var hrphotos;
                            if("photos" in revphotos["result"]){
                                hrphotos = revphotos["result"]["photos"];
                            }
                            else{
                                hrphotos = [];
                            }
                            
                            
                            document.getElementById("revbutton").style.display="block";
                            document.getElementById("photobutton").style.display="block";
                            
                            document.getElementById("restable").style.display="none";
                
                            
                            var tableofrevs ='<table border=1 style="width:60%; margin:auto;">';    
                            
                            if(revs.length==0){
                                tableofrevs+='<tr><td><strong>No Reviews Found</strong></td></tr>';   
                            }
                            else{
                                for(var i=0;i<5 && i<revs.length; ++i){
                                    tableofrevs+='<tr><td>';
                                    tableofrevs+='<img width="40px" height="40px" src="'+revs[i]["profile_photo_url"]+'">';
                                    tableofrevs+='<strong>'+revs[i]["author_name"]+'</strong>';
                                    tableofrevs+="</td></tr>";

                                    tableofrevs+='<tr><td>';
                                    tableofrevs+=revs[i]["text"];
                                    tableofrevs+="</td></tr>";
                                }    
                            }
                            tableofrevs+="</table>";
                            
                            console.log(tableofrevs);
                            
                            document.getElementById("showrevs").innerHTML=tableofrevs;
                            
                            var photos='<table border=1 style="width:60%; margin:auto;">';
                        
                            
                            if(hrphotos.length==0){
                                photos+='<tr><td><strong>No Photos found</strong></td></tr>';
                            }
                            else{
                                for(var i=0;i<5 && i<hrphotos.length; ++i){
                                        photos+='<tr><td>';
                                        photos+='<a target="_blank" href="image'+i+'.jpg" >';
                                        photos+='<img src="image'+i+'.jpg" />';
                                        photos+='</a>';
                                        photos+='</td></tr>';
                                    }
                            }
                            photos+='</table>';

                            document.getElementById("showphotos").innerHTML=photos;
                            
                            document.getElementById("placename").innerHTML=revphotos["result"]["name"];
                        }
                        
                    }
                    
                    http.send(params);
                    
                }
                
                function initMap(i) {
                    
                    var mid="map"+i;
                    
                    console.log(goog.results[i]);
                    
                    var maplat = goog.results[i]["geometry"]["location"]["lat"];
                    var maplng = goog.results[i]["geometry"]["location"]["lng"];
                    
                    console.log(maplat);
                    console.log(maplng);
                    
                    var uluru = {lat: maplat, lng: maplng};
                    var map = new google.maps.Map(document.getElementById(mid), {
                      zoom: 16,
                      center: uluru
                    });
                    var marker = new google.maps.Marker({
                      position: uluru,
                      map: map
                    });
                }
                
                function clearall() {
                    document.getElementById("keyword").value = '';
                    document.getElementById("category").selectedIndex = 0;
                    document.getElementById("distance").value = '';
                    document.getElementById("radiohere").checked = true;
                    document.getElementById("myloc").value = '';
                    document.getElementById("myloc").disabled = true;
                    
                    document.getElementById("restable").innerHTML="";
                    document.getElementById("revandphotos").innerHTML="";
                    
                }
                
                var clicked=[];
                
                function showmap(i){
                    
                    var mid="map"+i;
                    if(clicked[i]==0){

                        console.log(i);

                        initMap(i);

                        document.getElementById(mid).style.height= 400;
                        document.getElementById(mid).style.width= 400;
                        document.getElementById(mid).style.zIndex=1;
                        
                        document.getElementById(mid+mid).style.display="block";
                        
                    }
                    else{
                        
                        document.getElementById(mid).style.height=0;
                        document.getElementById(mid).style.width=0;
                        document.getElementById(mid).innerHTML="";
                        
                        document.getElementById(mid+mid).style.display="none";
                    }
                    
                    clicked[i]=1-clicked[i];
                    
                }
                
                function loadcategories(){
                    
                    categories=["default","cafe","bakery","restaurant", "beauty salon", "casino", "movie theater", "lodging", "airport", "train station","subway station", "bus station"];
                    
                    catoptions="";
                    
                    for(var i=0;i<categories.length;++i){
                        
                        catoptions+='<option name="'+categories[i]+'" value="'+i+'"';
                        if(i==<?php echo $cat;?>){
                            catoptions+=' selected';
                        }
                        catoptions+='>'+categories[i]+'</option>';
                    }
                    
                    console.log(catoptions);
                    
                    document.getElementById("category").innerHTML=catoptions;
                }

                function getlatlon() {
                    
                    loadcategories();
                    
                    var xmlhttp = new XMLHttpRequest();

                    xmlhttp.open("GET", "http://ip-api.com/json", false);
                    xmlhttp.send();

                    var obj = JSON.parse(xmlhttp.responseText);

                    var lat = obj.lat;
                    var lon = obj.lon;

                    document.getElementById("latitude").value = lat;
                    document.getElementById("longitude").value = lon;

                    document.getElementById("subut").disabled = false;

                    <?php if(isset($_POST["submit"])){ ?>
                      
                        goog = <?php echo $google; ?>;
                        
                        document.getElementById("restable").display="block";
                        
                        var tableres = '<table border=1 style="width=100%; margin:auto;"><tr>'

                        if (goog.results.length == 0) {
                            tableres += '<th>No records have been found</th></tr>';
                        } else {

                            tableres += "<th>Category</th><th>Name</th><th>Address</th></tr>"
                            for (var i = 0; i < goog.results.length; ++i) {
                                //console.log(goog.results[i]);
                                
                                clicked[i]=0;

                                tableres += "<tr>";
                                tableres += "<td><img src=\"" + goog.results[i].icon + "\"> </td>";
                                tableres += '<td><p class="pointy" onclick="getimagesandreviews ('+i+')">' + goog.results[i].name + "</p></td>";
                                var mid = "map"+i;
                                tableres += '<td style="position:relative"; ><p class="pointy" onclick=\'showmap("'+i+'")\'>' + goog.results[i].vicinity + '</p>';
                        
                                
                                tableres+='<div id="'+mid+mid+'" class="directions">Walk there<br>Bike there<br>Drive there</div>';
                                
                                tableres+='<div style="position:absolute;" id="'+mid+'"></div>';
                                
                                tableres += "</td></tr>";
                            }
                        }

                        tableres += "</table>";

                        document.getElementById("restable").innerHTML = tableres;

                    <?php } ?>
                }

                function offlocbox() {
                    document.getElementById("myloc").value = "";
                    document.getElementById("myloc").disabled = true;
                }

                function onlocbox() {
                    document.getElementById("myloc").disabled = false;
                    document.getElementById("myloc").required = true;
                }
            </script>
        </head>

        <body onload="getlatlon()">
            
            <div id="travelform">
                <h3>Travel and Entertainment Search</h3>
                <form action="" class="tform" method="POST" style="padding: 10px;border-top: 2px solid black">
                    <span>Keyword</span>
                    <input type="text" name="keyword" id="keyword" required="required" value="<?php echo $keywordspace; ?>"><br>

                    <span>Category</span>

                    <select id="category" name="category" selected>
                    </select><br>

                    <div>
                        <span style="float:left">
                        Distance (miles)
                        <input type="number" name="distance" id="distance" placeholder="10" value= "<?php echo $distance; ?>">
                    </span>
                        <span style="float:left">from</span>
                        <span style="float:left">
                        <input type=radio name="loc" id="radiohere" value="radiohere"   <?php if($radiobut=="radiohere") echo 'checked'; ?> onclick="offlocbox()">Here<br>
                        <input type=radio name="loc" id="radioloc" value="radioloc" 
                            <?php if($radiobut!="radiohere") echo 'checked'; ?> onclick="onlocbox()">
                        <input type=text name="myloc" id="myloc" placeholder="location" <?php if($radiobut=="radiohere") echo 'disabled'; ?> value= "<?php echo $locatetext; ?>">
                    </span>
                    </div>
                    <div style="clear:both">
                        <input type="submit" id="subut" name="submit" disabled=true>
                        <input type="button" value="Clear" onclick="clearall()">
                    </div>
                    <br>
                    <div>

                    </div>

                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                </form>
            </div>
            
            
            <div id="restable" style="margin:0 auto">
            </div>
            
            
            <div id="revandphotos">
                <div id="placename"></div>
                <div id="revbutton" style="display:none;">
                    <p id="showhiderev">click to show reviews</p>
                    <img id="revarrow" onclick="showreview()" src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png" />
                </div>
                <div id="showrevs" border=1 style='margin: 0 auto; display:none'></div>
                <div id="photobutton" style="display:none;">
                    <p id="showhidephoto">click to show images</p>
                    <img id="photoarrow" onclick="showphoto()" src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png" />
                </div>
                <div id="showphotos" border=1 style="margin: 0 auto; display:none"></div>
            </div>
                
            <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $MAPS_KEY; ?>">
            </script>
            
            
            
        </body>

        </html>