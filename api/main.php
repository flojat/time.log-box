<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;       
require 'vendor/autoload.php';
include_once 'config/config.inc.php';

use Firebase\JWT\JWT;


$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();   
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
                                   $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}; 


$app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => ["/"],
    "passthrough" => ["/login"],
    //"secret" => getenv("JWT_SECRET"),
    "secret" => "devsecret",
    "attribute" => "jwt",
    "algorithm" => ["HS256", "HS384"]
    
]));


$app->post('/login', function (Request $request, Response $response) {

      $bodyData = $request->getParsedBody();
      $user = filter_var($bodyData['user'], FILTER_SANITIZE_STRING);
      $password = filter_var($bodyData['password'], FILTER_SANITIZE_STRING);
      $pwd = sha1 ($password);
      
      $stmt = $this->db->prepare("SELECT id, email, name, firstname
                                  FROM user
                                  WHERE user.email=:user
                                  AND user.password=:pwd ");
                                  
      $stmt->bindValue(':user', $user, PDO::PARAM_STR);
      $stmt->bindValue(':pwd', $pwd, PDO::PARAM_STR);
      $stmt->execute();
      
      if($row = $stmt->fetch()) { 
       
        $user_id = $row['id'];
        $user_email = $row['email'];
        $user_name = $row['name'];
        $user_firstname = $row['firstname'];
 
// prepare vars and generate jwt
        $now = new DateTime();
        $future = new DateTime("now +2 hours");
        $jti = substr(str_shuffle(str_repeat(implode('', array_merge(range('A','Z'),range('a','z'),range(0,9)) ),2)), 0, 16);
        //PHP7 coud be so easy ;-) //random_bytes(16) and encode in base 64 or base 62;
        
        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => $jti,
            "sub" => $user_firstname,
            "user_id" => $user_id,
            "logbox_mac" =>  "mobile-client",
            
        ];
        //$secret = getenv("JWT_SECRET");
        $secret = "devsecret";
        $token = JWT::encode($payload, $secret, "HS256");
        $data["status"] = "ok";
        $data["token"] = $token;
        
        return $response->withStatus(201)
          ->withHeader("Content-Type", "application/json; charset=utf-8")
          ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
   
   
    }else {
    
        return $response->withStatus(401)
        ->withHeader("Content-Type", "application/json; charset=utf-8");
    }
    
});



$app->get('/stats', function (Request $request, Response $response) {

/*OPTIONAL;

*/     
    $jsonData="";
    $jwt_token = (array)$request->getAttribute('jwt');
    $getParams = $request->getQueryParams();
      
    //FIXME: take values from JWT-Token
    $start_date = $getParams['start_date'];
    $end_date =  $getParams['end_date'];
    
    // not nice but quick 'n dirty to convert ISO_8601(json) to mysql datetime 
    // https://en.wikipedia.org/wiki/ISO_8601 )
    if($end_date !=""){
    $end_date = str_replace ( ".000Z" , "" , $end_date) ;
    $end_date = str_replace ( "T" , " " , $end_date);
    }
    if($start_date !=""){
    $start_date = str_replace ( ".000Z" , "" , $start_date) ;
    $start_date = str_replace ( "T" , " " , $start_date);
    }
    

    $dayOfWeek =  array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $user_id=7;
    
    //FIXME Insert dbquery to build this Array!
    //$arrayDataTable =  array('WEEKDAY','Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $projectsWithProgress  = [
    '9' => ["Allgem. log-box"],
    '10' => ["SLIM log-box"],
    '11' => ["Doku log-box"],
    '12' => ["webGUI log-box "],
    '13' => ["HW für log-box"],
    ];
    
    $jsonData .='[["WEEKDAY"';
    foreach ($projectsWithProgress as &$proj) {
      $jsonData .= ',"'.$proj[0].'"';
    }
    $jsonData .= ']';
    
    foreach ($dayOfWeek as &$day) {
        $jsonData.= ',["'.$day.'"'; 
        foreach ($projectsWithProgress as $projId => $projName) {
          $stmt = $this->db->prepare("SELECT sum(round((TIME_TO_SEC(TIMEDIFF(`stop`,`start`))/60/60),2)) h 
          FROM entries e
          WHERE `user_id`=:user_id
          AND DATE_FORMAT(`start`,'%a')=:day 
          AND e.project_id=:projId 
          AND `start` >= :start_date 
          AND `stop` <= ADDDATE(:end_date, +1)"); 
             
          $stmt->bindValue(':user_id', $jwt_token['user_id'], PDO::PARAM_STR);
          $stmt->bindValue(':day', $day, PDO::PARAM_STR);
          $stmt->bindValue(':projId', $projId, PDO::PARAM_INT);
          $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
          $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
          $stmt->execute();
   
          while($row = $stmt->fetch()) {
            $jsonData.= ",".floatval (($row['h'] != "") ? $row['h'] : 0 ); 
          }
          $stmt = null;
        }
        $jsonData.= "]"; 
    }
    $jsonData.= "]"; 
          
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});

$app->get('/stats/week', function (Request $request, Response $response) {

/*OPTIONAL;
    of_category_id Array of int(11)
    project_id int(11)
    search_text varchar(255)
    start_date date
    end_date date
    user_id int(11) (just if admin!)"	"get List of all Entries of that user within actual Week(7days)
    is used to show worked time this week"
*/
    
    $jwt_token = (array)$request->getAttribute('jwt');
    
    $jsonData="";
    
    $dayOfWeek =  array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $user_id=7;
    
    //FIXME Insert dbquery to build this Array!
    //$arrayDataTable =  array('WEEKDAY','Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $projectsWithProgress  = [
    '9' => ["Allgem. log-box"],
    '10' => ["SLIM log-box"],
    '11' => ["Doku log-box"],
    '12' => ["webGUI log-box "],
    '13' => ["HW für log-box"],
    ];
    
    $jsonData .='[["WEEKDAY"';
    foreach ($projectsWithProgress as &$proj) {
      $jsonData .= ',"'.$proj[0].'"';
    }
    $jsonData .= ']';
    
    foreach ($dayOfWeek as &$day) {
        $jsonData.= ',["'.$day.'"'; 
        foreach ($projectsWithProgress as $projId => $projName) {
           
            $sqlquery="SELECT sum(round((TIME_TO_SEC(TIMEDIFF(`stop`,`start`))/60/60),2)) h 
            FROM entries e WHERE YEARWEEK(`start`) = YEARWEEK(NOW()) 
            AND`user_id`=".$jwt_token['user_id']." 
            AND DATE_FORMAT(`start`,'%a')='$day' 
            AND e.project_id=".$projId;
            
            $stmt = $this->db->query($sqlquery);    
            while($row = $stmt->fetch()) {
              $jsonData.= ",".floatval (($row['h'] != "") ? $row['h'] : 0 ); 
            }
            $stmt = null;
        }
        $jsonData.= "]"; 
    }
    $jsonData.= "]"; 
          
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});

  

$app->get('/projects', function (Request $request, Response $response) {
    
    // List of Projects with: 
    // project_id(String), project_name(String), client_id(String), 
    // client_logo(img), client_name(String),  client_project_owner_id(String), 
    // client_project_owner_name(String), client_project_owner_tel(String),
    
    $jwt_token = (array)$request->getAttribute('jwt');
    
    $sqlquery="SELECT p.id project_id, p.name project_name, c.id client_id, 
                      c.logo client_logo, c.company client_name, 
                      c.id client_project_owner_id, concat(c.name,' ',c.firstname) 
                      client_project_owner_name, c.phone client_project_owner_tel
               FROM `projects` p,`clients` c, `projects_has_user` pu  
               WHERE p.client_id = c.id 
               AND p.isactive = TRUE
               AND p.id = pu.projects_id
               AND pu.user_id = ".$jwt_token['user_id'];     
    $stmt = $this->db->query($sqlquery); 
    
    $jsonData="[";
	  if($stmt->rowCount() >0){
        while($row = $stmt->fetch()) {
            $jsonData .= json_encode($row).",";    
        }
        $jsonData = substr ( $jsonData , 0, (strlen ($jsonData)-1) );
    }
	  $jsonData .= "]";  
  
    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});



$app->get('/openentry', function (Request $request, Response $response) {
   
  /*
  * needs logbox_mac varchar(255), user_id int(11) from JWT-Token
  * check for a open entry and return project_id and start datetimestamp if found
  * used to mark project-logbutton green an set open-timestamp
  */
      $jwt_token = (array)$request->getAttribute('jwt');
      $sqlquery="SELECT `project_id`,`start` FROM `entries` WHERE `logbox_mac`='".$jwt_token['logbox_mac']."' AND `user_id`=".$jwt_token['user_id']." AND stop IS NULL";
      $stmt = $this->db->query($sqlquery); 
  	  $jsonData="[";
  	  if($stmt->rowCount() >0){
          while($row = $stmt->fetch()) {
              $jsonData .= json_encode($row).",";    
          }
          $jsonData = substr ( $jsonData , 0, (strlen ($jsonData)-1) );
      }
  	  $jsonData .= "]";  
      $response->getBody()->write($jsonData);   
      return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});





$app->post('/project/entry', function (Request $request, Response $response) {
   
//Insert new entry for Project by ID with actual Timestamp
//has to close a previous open Entry of that user with actual Timestamp
//is used to open or reopen new entry in same/other project (stop-watch-like)	
// and stop logging time by sending "go_to_standby":1

//200, single entry: id int(11), logbox_mac varchar(255), logbox_ip varchar(255), 
//project_id int(11), user_id int(11), start datetime, stop datetime, 
//category int(11), notes varchar(255)  


      $jsonData="";
      $jwt_token = (array)$request->getAttribute('jwt');
      $bodyData = $request->getParsedBody();
      
      $project_id = (int)filter_var($bodyData['project_id'], FILTER_SANITIZE_STRING);
      $go_to_standby = filter_var($bodyData['go_to_standby'], FILTER_SANITIZE_STRING);
      $notes = $this->db->quote( filter_var($bodyData['message'], FILTER_SANITIZE_STRING) );
      
      $logbox_ip = "unset";
      if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !=""){
          $logbox_ip = $_SERVER['REMOTE_ADDR'];
      }
      
//IF entry has no STOP timestamp set it, bevore open new one
      $sqlquery="SELECT `id`,`logbox_mac`,`project_id`,`user_id`,`stop` FROM `entries` WHERE `logbox_mac`='".$jwt_token['logbox_mac']."' AND `user_id`=".$jwt_token['user_id']." AND stop IS NULL";
      logall($sqlquery);
      $stmt = $this->db->query($sqlquery); 
      while($row = $stmt->fetch()) { 
        $updatestmt = $this->db->query("UPDATE `entries` set `stop` = NOW(), `notes` = $notes WHERE `id`=".$row['id']);
      }

//then insert a new entry with just the START timestamp, if not have to go to standby   
      if($go_to_standby == 0){
        $sqlquery="INSERT INTO `entries` (`logbox_mac`,`logbox_ip`,`project_id`,`user_id`,`start`) values('".$jwt_token['logbox_mac']."', '$logbox_ip', $project_id, ".$jwt_token['user_id'].", NOW())";
        $stmt = $this->db->query($sqlquery); 
      }
      
//get the actual Project to return
      $sqlquery="SELECT p.id project_id, p.name project_name, c.id client_id, 
                        c.logo client_logo, c.company client_name, 
                        c.id client_project_owner_id, concat(c.name,' ',c.firstname) 
                        client_project_owner_name, c.phone client_project_owner_tel
               FROM `projects` p,`clients` c  
               WHERE p.id = $project_id AND p.client_id = c.id"; 
      $stmt = $this->db->query($sqlquery); 
      while($row = $stmt->fetch()) {
        $jsonData .= json_encode($row);    
      }

    $response->getBody()->write($jsonData);   
    return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
});


function logall($valuesToSave, $logPost = false){

  $valuesToSave = date("Y-m-j H:i:s").", ".$valuesToSave.", ";
  $logFileName = "main.php.log";

  if($logPost){
      foreach($_POST as $key => $value){
            $valuesToSave .= ",$value($key)";
      }
      
      foreach($_GET as $key => $value){
            $valuesToSave .= ",$value($key)";
      }
  }
  
  $valuesToSave .= ",{$_SERVER['REMOTE_ADDR']}";
//log request with filename
  if ($logFileName != ""){
    if(!($datei = fopen("logs/".$logFileName,"a"))){
      //echo("geht nicht");
    }else{
     fwrite($datei,"$valuesToSave\n");
     fclose($datei);
    }
  }

}




$app->run();
