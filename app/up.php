<?php 
if(isset($_POST['submit'])){
 
 // Count total files
 $countfiles = count($_FILES['file']['name']);

 // Looping all files
 for($i=0;$i<$countfiles;$i++){  $filename = $_FILES['file']['name'][$i];
 
  // Upload file
  move_uploaded_file($_FILES['file']['tmp_name'][$i],'upload/'.$filename);
 
 }
} 
?>
<html>
<head>
  <meta name="google-signin-client_id" content="621656103050-idttka16m6vuo22574s472m2eacfrtsn.apps.googleusercontent.com">
</head>
<body>
<table>
<tr><td><div id="my-signin2"></div></td></tr>
<tr><td><div style="font-family: 'Orbitron', sans-serif;" id="EMAIL">Email:</div> </td></tr>
<tr><td><div style="font-family: 'Orbitron', sans-serif;" id="HOSTNAME">Host:<?php echo gethostname(); ?> </div> </td></tr>
</table>
  <script>
      document.getElementById('EMAIL').innerHTML = 'Email:'.concat('.js/platform');    
    function onSuccess(googleUser) {
      var gname=googleUser.getBasicProfile().getName();
      var email=googleUser.getBasicProfile().getEmail();
      console.log('Logged in as: ' + googleUser.getBasicProfile().getName());
      console.log('Logged in EMAIL: ' + googleUser.getBasicProfile().getEmail());
      
      document.getElementById('EMAIL').innerHTML = 'Email:'.concat(email);    
      
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

    function post(path, params, method='post') {

      // The rest of this code assumes you are not using a library.
      // It can be made less wordy if you use one.
      const form = document.createElement('form');
      form.method = method;
      form.action = path;

      for (const key in params) {
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
upload files:
<form method='post' action='' enctype='multipart/form-data'>
 <input type="file" name="file[]" id="file" multiple>

 <input type='submit' name='submit' value='Upload'>
</form>
<?php
  if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != "..") {
            echo '<p><a href="download.php?file=' . urlencode($file) . '">'.urlencode($file).'</a></p>';
        // $thelist .= '<li><a href="'.$file.'">'.$file.'</a></li>';
      }
    }
    closedir($handle);
  }
?>
<h1>List of files:</h1>
<ul><?php echo $thelist; ?></ul>
</body>
</html>