<html>

<body>
<?php
if(isset($_REQUEST["error"])){
    // Get parameters
    $error = urldecode($_REQUEST["error"]); // Decode URL-encoded string
 
    echo $error;
}
?>
<h3> Something wrong, please relogin </h3>
  <p><a href="index.php">login</a></p>
</body>
</html>