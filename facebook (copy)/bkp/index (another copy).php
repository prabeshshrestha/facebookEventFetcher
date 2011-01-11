<?php

require 'src/facebook.php';

// Create our Application instance.
$facebook = new Facebook(array(
  'appId'  => '150265945018990',
  'secret' => 'c5393ab40b6db03ae013689a5376e370',
  'cookie' => true,
));

// We may or may not have this data based on a $_GET or $_COOKIE based session.
//
// If we get a session here, it means we found a correctly signed session using
// the Application Secret only Facebook and the Application know. We dont know
// if it is still valid until we make an API call using the session. A session
// can become invalid if it has already expired (should not be getting the
// session back in this case) or if the user logged out of Facebook.
$session = $facebook->getSession();

$me = null;
// Session based API call.
if ($session) {
  try {
    $uid = $facebook->getUser();
    $me = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
  }
}

// login or logout url will be needed depending on current user state.
if ($me) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

// This call will always work since we are fetching public data.
$naitik = $facebook->api('/naitik');

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <!--
      We use the JS SDK to provide a richer user experience. For more info,
      look here: http://github.com/facebook/connect-js
    -->
    <div id="fb-root"></div>
    <script>
      window.fbAsyncInit = function() {
        FB.init({
          appId   : '<?php echo $facebook->getAppId(); ?>',
          session : <?php echo json_encode($session); ?>, // don't refetch the session when PHP already has it
          status  : true, // check login status
          cookie  : true, // enable cookies to allow the server to access the session
          xfbml   : true, // parse XFBML
        });

        // whenever the user logs in, we refresh the page
        FB.Event.subscribe('auth.login', function() {
          window.location.reload();
        });
      };

      (function() {
        var e = document.createElement('script');
        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>


    <h1><a href="example.php">php-sdk</a></h1>

    <?php if ($me): ?>
    <a href="<?php echo $logoutUrl; ?>">
      <img src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif">
    </a>
    <?php else: ?>
    <div>
    <a href="https://graph.facebook.com/oauth/authorize?
    client_id=150265945018990&
    redirect_uri=http://www.dhaubaji.info/facebook/index.php&
    scope=user_photos,user_videos,publish_stream,user_events&
    type=user_agent&
    display=popup">Click me</a>
    </div>
    <div>
      Using JavaScript &amp; XFBML: 
<fb:login-button perms="email,offline_access,user_events"
                 show-faces="true"></fb:login-button>
    </div>
    <div>
      Without using JavaScript &amp; XFBML:
      <a href="<?php echo $loginUrl; ?>">
        <img src="http://static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif">
      </a>
    </div>
    <?php endif ?>

    <h3>Session</h3>
    <?php if ($me): ?>
    <pre><?php //print_r($session); ?></pre>
<?php 
$events  = json_decode(file_get_contents(
    'https://graph.facebook.com/me/events?access_token=' .
    $session['access_token']));
//print_r(events);
?>
<?php 
	foreach ($events as $event) {
    		print_r($event);
		echo "<br/>";	
	}
?>
https://graph.facebook.com/me/events?access_token=<?php $session['access_token']?>
	<?php echo $session['access_token']?>
    <h3>You</h3>
    <img src="https://graph.facebook.com/<?php echo $uid; ?>/picture">
<a href="https://graph.facebook.com/"<?php echo $uid; ?>/events"">events here</a>

    <?php //echo $me['name']; ?>

    <h3>Your User Object</h3>
    <pre><?php //print_r($me); ?></pre>
    <?php else: ?>
    <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

    <h3>Naitik</h3>
    <img src="https://graph.facebook.com/naitik/picture">
    <?php //echo $naitik['name']; ?>

  </body>
</html>