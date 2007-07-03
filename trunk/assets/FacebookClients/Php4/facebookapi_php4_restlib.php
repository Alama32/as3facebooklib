<?
//
// +---------------------------------------------------------------------------+
// | Facebook Development Platform PHP4 client for API v1.0 by Kevin Fung      |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2007 Facebook, Inc.                                         | 
// | All rights reserved.                                                      |
// |                                                                           |
// | Redistribution and use in source and binary forms, with or without        |
// | modification, are permitted provided that the following conditions        |
// | are met:                                                                  |
// |                                                                           |
// | 1. Redistributions of source code must retain the above copyright         |
// |    notice, this list of conditions and the following disclaimer.          |
// | 2. Redistributions in binary form must reproduce the above copyright      |
// |    notice, this list of conditions and the following disclaimer in the    |
// |    documentation and/or other materials provided with the distribution.   |
// |                                                                           |
// | THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR      |
// | IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES |
// | OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.   |
// | IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT  |
// | NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY     |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT       |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF  |
// | THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.         |
// +---------------------------------------------------------------------------+
// | For help with this library, contact developers-help@facebook.com          |
// +---------------------------------------------------------------------------+
//

/**
 * REST-based API client.
 * @author ari
 * @author dave
 */
class FacebookRestClient {
  var $secret;
  var $session_key;
  var $api_key;
  var $server_addr;
  var $desktop;
  var $session_secret; // not used for server apps, for desktop apps this will get set in auth_getSession
  /**
   * Create the client.
   * @param string $session_key if you haven't gotten a session key yet, leave 
   *                            this as null and then set it later by just 
   *                            directly accessing the $session_key member 
   *                            variable.
   * @param bool   $desktop     set to true if you are a desktop client
   */
  function FacebookRestClient($server_addr, $api_key, $secret, $session_key=null, $desktop=false, $throw_errors=true) {
    $this->server_addr  = $server_addr;
    $this->secret       = $secret;
    $this->session_key  = $session_key;
    $this->api_key      = $api_key;
    $this->desktop      = $desktop;
    $this->throw_errors = $throw_errors;
    $this->last_call_success = true;
	$this->last_call_id = 0;
    $this->last_error = array();
    if ($GLOBALS['config']['debug']) {
      $this->cur_id = 0;
      ?>
<script type="text/javascript">
var types = ['params', 'xml', 'php', 'sxml'];
function toggleDisplay(id, type) {
  for each (var t in types) {
    if (t != type || document.getElementById(t + id).style.display == 'block') {
      document.getElementById(t + id).style.display = 'none';
    } else {
      document.getElementById(t + id).style.display = 'block';
    }
  }
  return false;
}
</script>
<?
    }
  }

  /**
   * Intended for use by desktop clients.  Call this function and store the
   * result, using it first to generate the appropriate login url and then to
   * retrieve the session information.
   * @return assoc array with 'token' => the auth_token string to be passed into login.php and auth_getSession.
   */
  function auth_createToken() {
    return $this->call_method('facebook.auth.createToken', array());
  }

  /**
   * Returns the session information available after current user logs in.
   * @param string $auth_token the token returned by auth_createToken or 
   *  passed back to your callback_url.
   * @return assoc array containing session_key, uid, and secret (desktop only)
   */
  function auth_getSession($auth_token) {
    if ($this->desktop) {
      $real_server_addr = $this->server_addr;
      $this->server_addr = str_replace('http://', 'https://', $real_server_addr);
    }
    $result = $this->call_method('facebook.auth.getSession', array('auth_token'=>$auth_token));
    $this->session_key = $result['auth_getSession_response']['session_key'];
    if ($this->desktop) {
      $this->session_secret = $result['secret'];
      $this->server_addr = $real_server_addr;
    }
    return $result;
  }

  /**
   * Returns events according to the filters specified.
   * @param int $uid Optional: User associated with events.  
   *   A null parameter will default to the session user.
   * @param array $eids Optional: Filter by these event ids.
   *   A null parameter will get all events for the user.
   * @param int $start_time Optional: Filter with this UTC as lower bound.  
   *   A null or zero parameter indicates no lower bound.
   * @param int $end_time Optional: Filter with this UTC as upper bound. 
   *   A null or zero parameter indicates no upper bound.
   * @param string $rsvp_status Optional: Only show events where the given uid
   *   has this rsvp status.  This only works if you have specified a value for
   *   $uid.  Values are as in events.getMembers.  Null indicates to ignore
   *   rsvp status when filtering.
   * @return array of events
   */
  function events_get($uid, $eids, $start_time, $end_time, $rsvp_status) {
    return $this->call_method('facebook.events.get',
        array(
        'uid' => $uid,
        'eids' => $eids,
        'start_time' => $start_time, 
        'end_time' => $end_time,
        'rsvp_status' => $rsvp_status));
  }

  /**
   * Returns membership list data associated with an event
   * @param int $eid : event id
   * @return assoc array of four membership lists, with keys 'attending',
   *  'unsure', 'declined', and 'not_replied'
   */
  function events_getMembers($eid) {
    return $this->call_method('facebook.events.getMembers',
      array('eid' => $eid));
  }
  
  /**
   * Makes an FQL query.  This is a generalized way of accessing all the data
   * in the API, as an alternative to most of the other method calls.  More
   * info at http://developers.facebook.com/documentation.php?v=1.0&doc=fql
   * @param string $query the query to evaluate
   * @return generalized array representing the results
   */
  function fql_query($query) {
    return $this->call_method('facebook.fql.query',
      array('query' => $query));
  }
  
  //new functions
  function feed_publishStoryToUser($title, $body, 
                                          $image_1=null, $image_1_link=null,
                                          $image_2=null, $image_2_link=null,
                                          $image_3=null, $image_3_link=null,
                                          $image_4=null, $image_4_link=null,
                                          $priority=1) {
    return $this->call_method('facebook.feed.publishStoryToUser',
      array('title' => $title,
            'body' => $body,
            'image_1' => $image_1,
            'image_1_link' => $image_1_link,
            'image_2' => $image_2,
            'image_2_link' => $image_2_link,
            'image_3' => $image_3,
            'image_3_link' => $image_3_link,
            'image_4' => $image_4,
            'image_4_link' => $image_4_link,
            'priority' => $priority));
  }
  
  function feed_publishActionOfUser($title, $body, 
                                           $image_1=null, $image_1_link=null,
                                           $image_2=null, $image_2_link=null,
                                           $image_3=null, $image_3_link=null,
                                           $image_4=null, $image_4_link=null,
                                           $priority=1) {
    return $this->call_method('facebook.feed.publishActionOfUser',
      array('title' => $title,
            'body' => $body,
            'image_1' => $image_1,
            'image_1_link' => $image_1_link,
            'image_2' => $image_2,
            'image_2_link' => $image_2_link,
            'image_3' => $image_3,
            'image_3_link' => $image_3_link,
            'image_4' => $image_4,
            'image_4_link' => $image_4_link,
            'priority' => $priority));
  }

  /**
   * Returns whether or not pairs of users are friends.
   * Note that the Facebook friend relationship is symmetric.
   * @param array $uids1: array of ids (id_1, id_2,...) of some length X
   * @param array $uids2: array of ids (id_A, id_B,...) of SAME length X
   * @return array of uid pairs with bool, true if pair are friends, e.g.
   *   array( 0 => array('uid1' => id_1, 'uid2' => id_A, 'are_friends' => 1),
   *          1 => array('uid1' => id_2, 'uid2' => id_B, 'are_friends' => 0) 
   *         ...)
   */
  function friends_areFriends($uids1, $uids2) {
    return $this->call_method('facebook.friends.areFriends',
        array('uids1'=>$uids1, 'uids2'=>$uids2));
  }
  
  /**
   * Returns the friends of the current session user.
   * @return array of friends
   */
  function friends_get() {
    return $this->call_method('facebook.friends.get', array());
  }
  
  /**
   * Returns the friends of the session user, who are also users
   * of the calling application.
   * @return array of friends
   */
  function friends_getAppUsers() {
    return $this->call_method('facebook.friends.getAppUsers', array());
  }

  /**
   * Returns groups according to the filters specified.
   * @param int $uid Optional: User associated with groups.  
   *  A null parameter will default to the session user.
   * @param array $gids Optional: group ids to query.
   *   A null parameter will get all groups for the user.
   * @return array of groups
   */
  function groups_get($uid, $gids) {
    return $this->call_method('facebook.groups.get',
        array(
        'uid' => $uid,
        'gids' => $gids));
  }

  /**
   * Returns the membership list of a group
   * @param int $gid : Group id
   * @return assoc array of four membership lists, with keys 
   *  'members', 'admins', 'officers', and 'not_replied'
   */
  function groups_getMembers($gid) {
    return $this->call_method('facebook.groups.getMembers',
      array('gid' => $gid));
  }

  /**
   * Returns the outstanding notifications for the session user.
   * @return assoc array of 
   *  notification count objects for 'messages', 'pokes' and 'shares', 
   *  a uid list of 'friend_requests', a gid list of 'group_invites',
   *  and an eid list of 'event_invites'
   */
  function notifications_get() {
    return $this->call_method('facebook.notifications.get', array());
  }
  
  /**
   * Sends an email notification to the specified user.
   * @return string url which you should send the logged in user to to finalize the message.
   */
  function notifications_send($to_ids, $markup, $no_email) {
    return $this->call_method('facebook.notifications.send',
                              array('to_ids' => $to_ids, 'markup' => $markup, 'no_email' => $no_email));
  }
  
  /**
   * Sends a request to the specified user (e.g. "you have 1 event invitation")
   * @param array $to_ids   user ids to receive the request (must be friends with sender, capped at 10)
   * @param string $type    type of request, e.g. "event" (as in "You have an event invitation.")
   * @param string $content fbml content of the request.  really stripped down fbml - just
   *                        text/names/links.  also, use the special tag <fb:req-choice url="" label="" />
   *                        to specify the buttons to be included.
   * @param string $image   url of an image to show beside the request
   * @param bool   $invite  whether to call it an "invitation" or a "request"
   * @return string url which you should send the logged in user to to finalize the message.
   */
  function notifications_sendRequest($to_ids, $type, $content, $image, $invite) {
    return $this->call_method('facebook.notifications.sendRequest',
                              array('to_ids' => $to_ids, 'type' => $type, 'content' => $content,
                                    'image' => $image, 'invite' => $invite));
  }

  /**
   * Returns photos according to the filters specified.
   * @param int $subj_id Optional: Filter by uid of user tagged in the photos.
   * @param int $aid Optional: Filter by an album, as returned by 
   *  photos_getAlbums.
   * @param array $pids Optional: Restrict to a list of pids 
   * Note that at least one of these parameters needs to be specified, or an 
   * error is returned.
   * @return array of photo objects.
   */
  function photos_get($subj_id, $aid, $pids) {
    return $this->call_method('facebook.photos.get', 
      array('subj_id' => $subj_id, 'aid' => $aid, 'pids' => $pids));
  }

  /**
   * Returns the albums created by the given user.
   * @param int $uid Optional: the uid of the user whose albums you want.
   *   A null value will return the albums of the session user.
   * @param array $aids Optional: a list of aids to restrict the query.
   * Note that at least one of the (uid, aids) parameters must be specified.
   * @returns an array of album objects.
   */
  function photos_getAlbums($uid, $aids) {
    return $this->call_method('facebook.photos.getAlbums', 
      array('uid' => $uid,
            'aids' => $aids));
  }

  /**
   * Returns the tags on all photos specified.
   * @param string $pids : a list of pids to query
   * @return array of photo tag objects, with include pid, subject uid,
   *  and two floating-point numbers (xcoord, ycoord) for tag pixel location
   */
  function photos_getTags($pids) {
    return $this->call_method('facebook.photos.getTags', 
      array('pids' => $pids));
  }
  
  /*
  //Adds a tag with the given information to a photo.
  //Don't know if this works yet, waiting for facebook's client update to see
  function photos_addTag($pid, $tag_uid, $tag_text, $x, $y) {
    return $this->call_method('facebook.photos.addTag', 
      array('pid' => $pid,
	  		'tag_uid' => $tag_uid,
			'tag_text' => $tag_text,
			'x' => $x,
			'y' => $y));
  }
  
  //Creates and returns a new album owned by the current session user.
  //Don't know if this works yet, waiting for facebook's client update to see
  function photos_createAlbum($name, $location, $description) {
    return $this->call_method('facebook.photos.createAlbum', 
      array('name' => $name,
	  		'location' => $location,
			'description' => $description));
  }
  
  //Uploads a photo owned by the current session user and returns the new photo.
  //Don't know if this works yet, waiting for facebook's client update to see
  function photos_upload($aid, $caption, $data) {
    return $this->call_method('facebook.photos.upload', 
      array('aid' => $aid,
	  		'caption' => $caption,
			'data' => $data));
  }
  */
  
  /**
   * For earlier users of Platform beta only.  Decodes ids returned in beta 
   * version of service to real Facebook ids (event, user, etc.) in version 1.
   * @param array $ids ids returned from beta service
   * @return array of (old_id, new_id) pairs, for all valid old ids.
   */
  function update_decodeIDs($ids)
  {
    return $this->call_method('facebook.update.decodeIDs', array('ids' => $ids));
  }

  /**
   * Returns the requested info fields for the requested set of users
   * @param array $uids an array of user ids 
   * @param array $fields an array of strings describing the info fields desired
   * @return array of users
   */
  function users_getInfo($uids, $fields) {
    return $this->call_method('facebook.users.getInfo', array('uids' => $uids, 'fields' => $fields));
  }

  /**
   * Returns the user corresponding to the current session object.
   * @return integer uid
   */
  function users_getLoggedInUser(){
    return $this->call_method('facebook.users.getLoggedInUser', array());
  }
  
  /** 
   * Returns whether or not the user corresponding to the current session object has the app installed 
   * @return boolean 
   */
  function users_isAppAdded(){
    return $this->call_method('facebook.users.isAppAdded', array());
  }
  
  /**
   * Sets the FBML for the profile of the user attached to this session
   * @param   string   $markup     The FBML that describes the profile presence of this app for the user 
   * @return  array    A list of strings describing any compile errors for the submitted FBML
   */
  function profile_setFBML($markup, $uid = null) {
    return $this->call_method('facebook.profile.setFBML', array('markup' => $markup, 'uid' => $uid));
  }

  function fbml_refreshImgSrc($url) {
    return $this->call_method('facebook.fbml.refreshImgSrc', array('url' => $url));
  }

  function fbml_refreshRefUrl($url) {
    return $this->call_method('facebook.fbml.refreshRefUrl', array('url' => $url));
  }
  
  

  /* UTILITY FUNCTIONS */

  function call_method($method, $params) {
    $this->last_call_success = true;
    $this->last_error = array();

    $xml = $this->post_request($method, $params);
	$sxml = $this->xml2array($xml);
	$result = $sxml;
	
    if ($GLOBALS['config']['debug']) {
      // output the raw xml and its corresponding php object, for debugging:
	  print '<a href=sample_client.php>RELOAD THIS PAGE HERE</a>';
      print '<div style="margin: 10px 30px; padding: 5px; border: 2px solid black; background: gray; color: white; font-size: 12px; font-weight: bold;">';
      $this->cur_id++;
      print $this->cur_id . ': Called ' . $method . ', show ' .
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'params\');">Params</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'xml\');">XML</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'sxml\');">SXML</a> | '.
            '<a href=# onclick="return toggleDisplay(' . $this->cur_id . ', \'php\');">PHP</a>';
      print '<pre id="params'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($params, true).'</pre>';
      print '<pre id="xml'.$this->cur_id.'" style="display: none; overflow: auto;">'.htmlspecialchars($xml).'</pre>';
      print '<pre id="php'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($result, true).'</pre>';
      print '<pre id="sxml'.$this->cur_id.'" style="display: none; overflow: auto;">'.print_r($sxml, true).'</pre>';
      print '</div>';
    }
    if (is_array($result) && isset($result['error_code'])) {
      $this->last_call_success = false;
      $this->last_error = $result;
    }
    return $result;
  }

  function post_request($method, $params) {
    $params['method'] = $method;
    $params['session_key'] = $this->session_key;
    $params['api_key'] = $this->api_key;
    $params['call_id'] = microtime_float();
	if ($params['call_id'] <= $this->last_call_id) {
	  $params['call_id'] = $this->last_call_id + 0.001;
	}
    if (!isset($params['v'])) {
      $params['v'] = '1.0';
    }
    $post_params = array();
	
    foreach ($params as $key => $val) {
      if (is_array($val)) $val = implode(',', $val);
      $post_params[] = $key.'='.urlencode($val);
    }
    if ($this->desktop && $method != 'facebook.auth.getSession' && $method != 'facebook.auth.createToken') {
      $secret = $this->session_secret;
    } else {
      $secret = $this->secret;
    }
    $post_params[] = 'sig='.api_generate_sig($params, $secret);
    $post_string = implode('&', $post_params);

    // Use CURL if installed
    if (function_exists('curl_init'))
    {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->server_addr);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook API PHP4 Client 1.0 (curl) ' . phpversion());
      $result = curl_exec($ch);
      curl_close($ch);
    } else {

      // non-curl based version...
      // Using fopen with ssl transport requires OpenSSL to be installed.
      if ((strncmp($this->server_addr, 'https', 5) == 0)
        && function_exists('openssl_open'))
      {
        $protocol = 'https';
      } else {
        // switch back to http
        $protocol = 'http';
        $this->server_addr = str_replace('https://', 'http://', $this->server_addr);
      }

      $context =
        array($protocol =>
              array('method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n".
                                'User-Agent: Facebook API PHP4 Client 1.0 (non-curl) '.phpversion()."\r\n".
                                'Content-length: ' . strlen($post_string),
                    'content' => $post_string));
      $contextid=stream_context_create($context);
      $sock=fopen($this->server_addr, 'r', false, $contextid);
      if ($sock) {
        $result='';
        while (!feof($sock))
          $result.=fgets($sock, 4096);

        fclose($sock);
      }
    }
    return $result;
  }
  
  //xml2array script from http://www.bin-co.com/php/scripts/xml2array/
  //you still need xml_parser_create function to parse xml in PHP4
  function xml2array($contents) {
    if(!$contents) return array();
    if(!function_exists('xml_parser_create')) {
        return array();
    }
    $parser = xml_parser_create();
    xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
    xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
    xml_parse_into_struct( $parser, $contents, $xml_values );
    xml_parser_free( $parser );
    if(!$xml_values) return;
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();
    $current = &$xml_array;
    foreach($xml_values as $data) {
        unset($attributes,$value);
        extract($data);
        $result = '';
        if(isset($value)) {
            $result = $value;
        }
        if($type == "open") {
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                $current = &$current[$tag];
            } else {
                if(isset($current[$tag][0])) {
                    array_push($current[$tag], $result);
                } else {
                    $current[$tag] = array($current[$tag],$result);
                }
                $last = count($current[$tag]) - 1;
                $current = &$current[$tag][$last];
            }
        } elseif($type == "complete") {
            if(!isset($current[$tag])) {
                $current[$tag] = $result;
            } else {
                if((is_array($current[$tag]) and $get_attributes == 0)
                        or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
                    array_push($current[$tag],$result);
                } else {
                    $current[$tag] = array($current[$tag],$result);
                }
            }
        } elseif($type == 'close') {
            $current = &$parent[$level-1];
        }
    }
    return($xml_array);
}

}

// Supporting methods and values------
/**
 * Generate a signature for the API call.  Should be copied into the client
 * library and also used on the server to validate signatures.
 *
 * @author ari
 */
function api_generate_sig($params_array, $secret) {
  $str = '';
  ksort($params_array);
  foreach ($params_array as $k=>$v) {
    if ($k != 'sig')
      $str .= "$k=$v";
  }
  $str .= $secret;

  return md5($str);
}

//PHP4 replacement for microtime(true)
function microtime_float() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

/**
 * Error codes and descriptions for the Facebook API.
 */

define("API_EC_SUCCESS", 0);

/*
 * GENERAL ERRORS
 */
define("API_EC_UNKNOWN", 1);
define("API_EC_SERVICE", 2);
define("API_EC_METHOD", 3);
define("API_EC_TOO_MANY_CALLS", 4);
define("API_EC_BAD_IP", 5);

/*
 * PARAMETER ERRORS
 */
define("API_EC_PARAM", 100);
define("API_EC_PARAM_API_KEY", 101);
define("API_EC_PARAM_SESSION_KEY", 102);
define("API_EC_PARAM_CALL_ID", 103);
define("API_EC_PARAM_SIGNATURE", 104);
define("API_EC_PARAM_USER_ID", 110);
define("API_EC_PARAM_USER_FIELD", 111);
define("API_EC_PARAM_SOCIAL_FIELD", 112);
define("API_EC_PARAM_ALBUM_ID", 120);

/*
 * USER PERMISSIONS ERRORS
 */
define("API_EC_PERMISSION", 200);
define("API_EC_PERMISSION_USER", 210);
define("API_EC_PERMISSION_ALBUM", 220);
define("API_EC_PERMISSION_PHOTO", 221);

define("FQL_EC_PARSER", 601);
define("FQL_EC_UNKNOWN_FIELD", 602);
define("FQL_EC_UNKNOWN_TABLE", 603);
define("FQL_EC_NOT_INDEXABLE", 604);


/*
 * DATA EDIT ERRORS
 */
define("API_EC_EDIT", 300);
define("API_EC_EDIT_USER_DATA", 310);
define("API_EC_EDIT_PHOTO", 320);


$api_error_descriptions = array(
    'API_EC_SUCCESS'           => 'Success',
    'API_EC_UNKNOWN'           => 'An unknown error occurred',
    'API_EC_SERVICE'           => 'Service temporarily unavailable',
    'API_EC_METHOD'            => 'Unknown method',
    'API_EC_TOO_MANY_CALLS'    => 'Application request limit reached',
    'API_EC_BAD_IP'            => 'Unauthorized source IP address',
    'API_EC_PARAM'             => 'Invalid parameter',
    'API_EC_PARAM_API_KEY'     => 'Invalid API key',
    'API_EC_PARAM_SESSION_KEY' => 'Session key invalid or no longer valid',
    'API_EC_PARAM_CALL_ID'     => 'Call_id must be greater than previous',
    'API_EC_PARAM_SIGNATURE'   => 'Incorrect signature',
    'API_EC_PARAM_USER_ID'     => 'Invalid user id',
    'API_EC_PARAM_USER_FIELD'  => 'Invalid user info field',
    'API_EC_PARAM_SOCIAL_FIELD' => 'Invalid user field',
    'API_EC_PARAM_ALBUM_ID'    => 'Invalid album id',
    'API_EC_PERMISSION'        => 'Permissions error',
    'API_EC_PERMISSION_USER'   => 'User not visible',
    'API_EC_PERMISSION_ALBUM'  => 'Album not visible',
    'API_EC_PERMISSION_PHOTO'  => 'Photo not visible',
    'API_EC_EDIT'              => 'Edit failure',
    'API_EC_EDIT_USER_DATA'    => 'User data edit failure',
    'API_EC_EDIT_PHOTO'        => 'Photo edit failure',
	'FQL_EC_PARSER'			   => 'FQL: Parser Error',
	'FQL_EC_UNKNOWN_FIELD'        => 'FQL: Unknown Field',
	'FQL_EC_UNKNOWN_TABLE'        => 'FQL: Unknown Table',
	'FQL_EC_NOT_INDEXABLE'        => 'FQL: Statement not indexable'
    );
	  

$profile_field_array = array(
    "about_me",
    "activities",
    "affiliations",
    "birthday",
    "books",
    "current_location",
    "education_history",
    "first_name",
    "hometown_location",
    "hs_info",
    "interests",
    "last_name",
    "meeting_for",
    "meeting_sex",
    "movies",
    "music",
    "name",
    "notes_count",
    "pic",
    "pic_big",
    "pic_small",
    "political",
    "profile_update_time",
    "quotes",
    "relationship_status",
    "religion",
    "sex", 
    "significant_other_id",
    "status",
    "timezone",
    "tv",
    "wall_count", 
    "work_history");
?>