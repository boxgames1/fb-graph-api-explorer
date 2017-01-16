<?php 

App::uses('Component', 'Controller');
App::import('Vendor', 'Facebook', array('file' => 'facebook-php-sdk-v4-4.0/autoload.php')); //Load FB SDK
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

class FacebookComponent extends Component {
     
	private $appid = "YOUR_APP_ID";
	private $appsecret ="YOUR_APP_SECRET";

	private $access_token = "YOUR_ACCESS_TOKEN";
	      
    //Get Fans Count of a Fan Page
    public function getFans($id){
        //Construct a Facebook URL
        $json_url ='https://graph.facebook.com/'.$id.'?access_token='.$this->appid.'|'.$this->appsecret.'&fields=fan_count';
        $json = file_get_contents($json_url);
        $json_output = json_decode($json);
        if(isset($json_output->fan_count) && $json_output->fan_count){
          return $likes = $json_output->fan_count;
        }else{
          return 0;
        }
	  }
    
    
    //Get the count of posts. In this case we get them from the last 28 days.
    public function getPostsCount($id){
        $posts=array();
        while(true){
          if (count($posts)==0) $until=strtotime("now");
          else {
            $until = $posts[count($posts)-1]->created_time;
            $until = strtotime($until)-1;
          }
          $json_url ='https://graph.facebook.com/'.$id.'/posts?access_token='.$this->appid.'|'.$this->appsecret.'&fields=created_time&limit=100&since='.strtotime('-28days',strtotime("now"))."&until=".$until;
          $json = file_get_contents($json_url);
          $json_output = json_decode($json); 
          if(isset($json_output->data) && $json_output->data && count($json_output->data)>0){
            if(count($posts)==0) $posts=$json_output->data;
            else {
              array_merge($posts, $json_output->data);
            }
          }
          else break;
        }
        return count($posts);
	  }

     //Get an array of insights
     public function getPageInsights($id){
        FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
        $sessionFacebook = FacebookSession::newAppSession();

        $request = new FacebookRequest(
              $sessionFacebook,
              'GET',
              '/'.$id.'/insights/page_impressions,page_consumptions,page_impressions_organic,page_impressions_viral,page_engaged_users,page_stories_by_story_type?period=days_28&fields=values&since='.strtotime("now").'&until='.strtotime("now") , array("access_token" => $this->access_token));
        $response = $request->execute();

        $post = $response->getGraphObject()->asArray();		
        $json_output = $post;
        $data = array();
        if(isset($json_output["data"]) && $json_output["data"]){
          $data["reach"]=$json_output["data"][0]->values[0]->value;
          $data["clicks"]=$json_output["data"][1]->values[0]->value;
          $data["organic"]=$json_output["data"][2]->values[0]->value;
          $data["viral"]=$json_output["data"][3]->values[0]->value;
          $data["interactions"]=$json_output["data"][4]->values[0]->value;
        }
        return $data;
      }
	
  //Get likes count
	public function getTotalLikes($id){
		
		FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
		$sessionFacebook = FacebookSession::newAppSession();
  		
		$request = new FacebookRequest(
				  $sessionFacebook,
				  'GET',
				  '/'.$id.'/likes?summary=true'
				);
		$response = $request->execute();
		
		$likes = $response->getGraphObject()->asArray();		
		$total = $likes["summary"]->total_count;
		return $total;
  		
	}


  //Get comments count
	public function getTotalComments($id){
		
		FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
		$sessionFacebook = FacebookSession::newAppSession();
  		
		$request = new FacebookRequest(
				  $sessionFacebook,
				  'GET',
				  '/'.$id.'/comments?summary=true'
				);
		$response = $request->execute();
		
		$comments = $response->getGraphObject()->asArray();		
		$total = $comments["summary"]->total_count;
		return $total;
  		
	}



  //Get reactions count
	public function getTotalReactions($id){
		
		FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
		$sessionFacebook = FacebookSession::newAppSession();
  		
		$request = new FacebookRequest(
				  $sessionFacebook,
				  'GET',
				  '/'.$id.'/reactions?summary=true'
				);
		$response = $request->execute();
		
		$reactions = $response->getGraphObject()->asArray();						
		$total = $reactions["summary"]->total_count;
		return $total;
  		
	}

  //Get last posts. 5 by default
	public function getLastContentsByChannel($id,$qty=5){
		
		FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
		$sessionFacebook = FacebookSession::newAppSession();
  		
		$request = new FacebookRequest(
				  $sessionFacebook,
				  'GET',
				  '/'.$id.'/feed?limit='.$qty , array("access_token" => $this->access_token));
		$response = $request->execute();

		$post = $response->getGraphObject()->asArray();						

		return $post["data"];
  		
	}

  //Get Scheduled posts
	public function getScheduledContentsByChannel($id){
		
		FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
		$sessionFacebook = FacebookSession::newAppSession();
  		
		$request = new FacebookRequest(
				  $sessionFacebook,
				  'GET',
				  '/'.$id.'/promotable_posts?is_published=false' , array("access_token" => $this->access_token));
		$response = $request->execute();

		$post = $response->getGraphObject()->asArray();						

		return $post["data"];
  		
	}


  //Get ID of page by his screen name.
	public function getIdApiPage($alias){
		FacebookSession::setDefaultApplication( $this->appid, $this->appsecret );
		$sessionFacebook = FacebookSession::newAppSession();
  		
		$request = new FacebookRequest(
				  $sessionFacebook,
				  'GET',
				  '/'.$alias , array("access_token" => $this->access_token));
		$response = $request->execute();

		$data = $response->getGraphObject()->asArray();						

		if(isset($data["id"])&&!empty($data["id"]))
			return $data["id"];
		else return null;
	}




}

?>
