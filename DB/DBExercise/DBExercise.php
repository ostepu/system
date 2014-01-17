<?php
/**
 * @file DBExercise.php contains the DBExercise class
 */ 

require_once( 'Include/Slim/Slim.php' );
include_once( 'Include/Structures.php' );
include_once( 'Include/Request.php' );
include_once( 'Include/DBJson.php' );
include_once( 'Include/CConfig.php' );

\Slim\Slim::registerAutoloader();

// runs the CConfig
$com = new CConfig(DBExercise::getPrefix());

// runs the DBExercise
if (!$com->used())
    new DBExercise($com->loadConfig());  
    
/**
 * A class, to abstract the "Exercise" table from database
 *
 * @author Till Uhlig
 */
class DBExercise
{
    /**
     * @var Slim $_app the slim object
     */ 
    private $_app=null;
    
    /**
     * @var Component $_conf the component data object
     */ 
    private $_conf=null;
    
    /**
     * @var Link[] $query a list of links to a query component
     */ 
    private $query=array();
    
    /**
     * @var string $_prefix the prefixes, the class works with (comma separated)
     */
    private static $_prefix = "exercise";
    
    /**
     * the $_prefix getter
     *
     * @return the value of $_prefix
     */ 
    public static function getPrefix()
    {
        return DBExercise::$_prefix;
    }
    
    /**
     * the $_prefix setter
     *
     * @param string $value the new value for $_prefix
     */ 
    public static function setPrefix($value)
    {
        DBExercise::$_prefix = $value;
    }
    
    /**
     * the component constructor
     *
     * @param Component $conf component data
     */ 
    public function __construct($conf)
    {
        // initialize component
        $this->_conf = $conf;
        $this->query = array(CConfig::getLink($conf->getLinks(),"out"));
        
        // initialize slim
        $this->_app = new \Slim\Slim();
        $this->_app->response->headers->set('Content-Type', 'application/json');

        // PUT EditExercise
        $this->_app->put('/' . $this->getPrefix() . '(/exercise)/:eid(/)',
                        array($this,'editExercise'));
        
        // DELETE DeleteExercise
        $this->_app->delete('/' . $this->getPrefix() . '(/exercise)/:eid(/)',
                           array($this,'deleteExercise'));
        
        // POST SetExercise
        $this->_app->post('/' . $this->getPrefix() .'(/)',
                         array($this,'setExercise'));    
        
        // GET GetExercise
        $this->_app->get('/' . $this->getPrefix() . '(/exercise)/:eid(/)',
                        array($this,'getExercise'));
                        
        // GET GetSheetExercises
        $this->_app->get('/' . $this->getPrefix() . '/exercisesheet/:esid(/)',
                        array($this,'getSheetExercises'));
                        
        // GET GetCourseExercises
        $this->_app->get('/' . $this->getPrefix() . '/course/:courseid(/)',
                        array($this,'getCourseExercises'));
                        
        // GET GetAllExercises
        $this->_app->get('/' . $this->getPrefix() . '(/exercise)(/)',
                        array($this,'getAllExercises'));
        

                        
        // starts slim only if the right prefix was received
        if (strpos ($this->_app->request->getResourceUri(),'/' . 
                    $this->getPrefix()) === 0){
            // run Slim
            $this->_app->run();
        }
    }
    
    /**
     * PUT EditExercise
     *
     * @param int $eid a database exercise identifier
     */
    public function editExercise($eid)
    {
        Logger::Log("starts PUT EditExercise",LogLevel::DEBUG);
        
        // checks whether incoming data has the correct data type
        DBJson::checkInput($this->_app, 
                            ctype_digit($eid));
                            
        // decode the received exercise data, as an object
        $insert = Exercise::decodeExercise($this->_app->request->getBody());
        
        // always been an array
        if (!is_array($insert))
            $insert = array($insert);

        foreach ($insert as $in){
            // generates the update data for the object
            $data = $in->getInsertData();
            
            // starts a query, by using a given file
            $result = DBRequest::getRoutedSqlFile($this->query, 
                                            "Sql/EditExercise.sql", 
                                            array("eid" => $esid, "values" => $data));                   
           
            // checks the correctness of the query
            if ($result['status']>=200 && $result['status']<=299){
                $this->_app->response->setStatus(201);
                if (isset($result['headers']['Content-Type']))
                    $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
            } else{
                Logger::Log("PUT EditExercise failed",LogLevel::ERROR);
                $this->_app->response->setStatus(451);
                $this->_app->stop();
            }
        }
    }
    
    /**
     * DELETE DeleteExercise
     *
     * @param int $eid a database exercise identifier
     */
    public function deleteExercise($eid)
    {
        Logger::Log("starts DELETE DeleteExercise",LogLevel::DEBUG);
        
        // checks whether incoming data has the correct data type
        DBJson::checkInput($this->_app, 
                            ctype_digit($eid));
                            
        // starts a query, by using a given file
        $result = DBRequest::getRoutedSqlFile($this->query, 
                                        "Sql/DeleteExercise.sql", 
                                        array("eid" => $eid));    
                                        
        // checks the correctness of the query                          
        if ($result['status']>=200 && $result['status']<=299){
            $this->_app->response->setStatus($result['status']);
            if (isset($result['headers']['Content-Type']))
                $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
        } else{
            Logger::Log("DELETE DeleteExercise failed",LogLevel::ERROR);
            $this->_app->response->setStatus(409);
            $this->_app->stop();
        }
    }
    
    /**
     * POST SetExercise
     */
    public function setExercise()
    {
        Logger::Log("starts POST SetExercise",LogLevel::DEBUG);
        
        // decode the received exercise data, as an object
        $insert = Exercise::decodeExercise($this->_app->request->getBody());
        
        // always been an array
        if (!is_array($insert))
            $insert = array($insert);

        foreach ($insert as $in){
            // generates the insert data for the object
            $data = $in->getInsertData();
            
            // starts a query, by using a given file
            $result = DBRequest::getRoutedSqlFile($this->query, 
                                            "Sql/SetExercise.sql", 
                                            array("values" => $data));                   
           
            // checks the correctness of the query    
            if ($result['status']>=200 && $result['status']<=299){
                $queryResult = Query::decodeQuery($result['content']);
                
                // sets the new auto-increment id
                $obj = new Exercise();
                $obj->setId($queryResult->getInsertId());
            
                $this->_app->response->setBody(Exercise::encodeExercise($obj)); 
                $this->_app->response->setStatus(201);
                if (isset($result['headers']['Content-Type']))
                    $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
            } else{
                Logger::Log("POST SetExercise failed",LogLevel::ERROR);
                $this->_app->response->setStatus(451);
                $this->_app->stop();
            }
        }
    }
    
    /**
     * GET GetExercise
     *
     * @param int $eid a database exercise identifier
     */
    public function getExercise($eid)
    {        
        Logger::Log("starts GET GetExercise",LogLevel::DEBUG);
        
        // checks whether incoming data has the correct data type
        DBJson::checkInput($this->_app, 
                            ctype_digit($eid));
                            
        // starts a query, by using a given file
        $result = DBRequest::getRoutedSqlFile($this->query, 
                                        "Sql/GetExercise.sql", 
                                        array("eid" => $eid));        
        
        // checks the correctness of the query
        if ($result['status']>=200 && $result['status']<=299){
            $query = Query::decodeQuery($result['content']);

            $data = $query->getResponse();
            
            
            // generates an assoc array of an exercise by using a defined 
            // list of its attributes
            $exercise = DBJson::getObjectsByAttributes($data, 
                                    Exercise::getDBPrimaryKey(), 
                                    Exercise::getDBConvert());           
            
            
            // generates an assoc array of files by using a defined 
            // list of its attributes
            $attachments = DBJson::getObjectsByAttributes($data, 
                                        File::getDBPrimaryKey(), 
                                        File::getDBConvert());
            
            
            // generates an assoc array of submissions by using a defined 
            // list of its attributes
            $submissions = DBJson::getObjectsByAttributes($data,
                                    Submission::getDBPrimaryKey(), 
                                    Submission::getDBConvert(), 
                                    '2');
                                    
            // sets the selectedForGroup attribute
            foreach ($submissions as &$submission){
                if (isset($submission['selectedForGroup']) || $submission['selectedForGroup']==null){
                    if (!isset($submission['id'])){
                        $submission['selectedForGroup'] = (string) 0;
                    } elseif ($submission['id'] == $submission['selectedForGroup']) {
                        $submission['selectedForGroup'] = (string) 1;
                    } else
                        $submission['selectedForGroup'] = (string) 0;
                }
                else
                    $submission['selectedForGroup'] = (string) 0;
            }       
            
            // concatenates the exercise and the associated attachments
            $res = DBJson::concatObjectListResult($data, $exercise,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_attachments'] ,$attachments,File::getDBPrimaryKey());  
            
            // concatenates the exercise and the associated submissions
            $res = DBJson::concatResultObjectLists($data, $res,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_submissions'] ,$submissions,Submission::getDBPrimaryKey(), '2');
            
            // only one object as result
            if (count($res)>0)
                $res = $res[0];
                
            $this->_app->response->setBody(Exercise::encodeExercise($res));
            $this->_app->response->setStatus($result['status']);
            if (isset($result['headers']['Content-Type']))
                $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
        } else{
            Logger::Log("GET GetExercise failed",LogLevel::ERROR);
            $this->_app->response->setStatus(409);
            $this->_app->response->setBody(Exercise::encodeExerciseSheet(new Exercise()));
            $this->_app->stop();
        }
    }

    /**
     * GET GetAllExercises
     */
    public function getAllExercises()
    {       
        Logger::Log("starts GET GetAllExercises",LogLevel::DEBUG);
        
        // starts a query, by using a given file
        $result = DBRequest::getRoutedSqlFile($this->query, 
                                        "Sql/GetAllExercises.sql", 
                                        array());        

        // checks the correctness of the query                           
        if ($result['status']>=200 && $result['status']<=299){
            $query = Query::decodeQuery($result['content']);

            $data = $query->getResponse();
            
            
            // generates an assoc array of exercises by using a defined 
            // list of its attributes
            $exercises = DBJson::getObjectsByAttributes($data, 
                                    Exercise::getDBPrimaryKey(), 
                                    Exercise::getDBConvert());           
            
            
            // generates an assoc array of files by using a defined 
            // list of its attributes
            $attachments = DBJson::getObjectsByAttributes($data, 
                                        File::getDBPrimaryKey(), 
                                        File::getDBConvert());
            
            
            // generates an assoc array of submissions by using a defined 
            // list of its attributes
            $submissions = DBJson::getObjectsByAttributes($data, 
                                    Submission::getDBPrimaryKey(), 
                                    Submission::getDBConvert(), 
                                    '2');
            
            // sets the selectedForGroup attribute
            foreach ($submissions as &$submission){
                if (isset($submission['selectedForGroup']) 
                    || $submission['selectedForGroup']==null){
                    
                    if (!isset($submission['id'])){
                        $submission['selectedForGroup'] = (string) 0;
                    } elseif ($submission['id'] == $submission['selectedForGroup']) {
                        $submission['selectedForGroup'] = (string) 1;
                    } else
                        $submission['selectedForGroup'] = (string) 0;
                }
                else
                    $submission['selectedForGroup'] = (string) 0;
            }       
            
            // concatenates the exercise and the associated attachments
            $res = DBJson::concatObjectListResult($data, $exercises,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_attachments'] ,$attachments,File::getDBPrimaryKey());  
            
            // concatenates the exercise and the associated submissions
            $res = DBJson::concatResultObjectLists($data, $res,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_submissions'] ,$submissions,Submission::getDBPrimaryKey(), '2');
                
            $this->_app->response->setBody(Exercise::encodeExercise($res));
            $this->_app->response->setStatus($result['status']);
            if (isset($result['headers']['Content-Type']))
                $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
        } else{
            Logger::Log("GET GetAllExercises failed",LogLevel::ERROR);
            $this->_app->response->setStatus(409);
            $this->_app->response->setBody(Exercise::encodeExerciseSheet(new Exercise()));
            $this->_app->stop();
        }
    }
    
    /**
     * GET GetSheetExercises
     *
     * @param int $esid a database exercise sheet identifier
     */
    public function getSheetExercises($esid)
    {     
        Logger::Log("starts GET GetSheetExercises",LogLevel::DEBUG);
        
        // checks whether incoming data has the correct data type
        DBJson::checkInput($this->_app, 
                            ctype_digit($esid));
                            
        // starts a query, by using a given file
        $result = DBRequest::getRoutedSqlFile($this->query, 
                                        "Sql/GetSheetExercises.sql", 
                                        array("esid" => $esid));        

        // checks the correctness of the query                              
        if ($result['status']>=200 && $result['status']<=299){
            $query = Query::decodeQuery($result['content']);

            $data = $query->getResponse();
            
            // generates an assoc array of exercises by using a defined 
            // list of its attributes
            $exercises = DBJson::getObjectsByAttributes($data, Exercise::getDBPrimaryKey(), Exercise::getDBConvert());           
            
            // generates an assoc array of files by using a defined 
            // list of its attributes
            $attachments = DBJson::getObjectsByAttributes($data, File::getDBPrimaryKey(), File::getDBConvert());
            
            // generates an assoc array of submissions by using a defined 
            // list of its attributes
            $submissions = DBJson::getObjectsByAttributes($data, Submission::getDBPrimaryKey(), Submission::getDBConvert(), '2');
             
            // sets the selectedForGroup attribute
            foreach ($submissions as &$submission){
                if (isset($submission['selectedForGroup']) || $submission['selectedForGroup']==null){
                    if (!isset($submission['id'])){
                        $submission['selectedForGroup'] = (string) 0;
                    } elseif ($submission['id'] == $submission['selectedForGroup']) {
                        $submission['selectedForGroup'] = (string) 1;
                    } else
                        $submission['selectedForGroup'] = (string) 0;
                }
                else
                    $submission['selectedForGroup'] = (string) 0;
            }      
            
            // concatenates the exercise and the associated attachments
            $res = DBJson::concatObjectListResult($data, $exercises,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_attachments'] ,$attachments,File::getDBPrimaryKey());  
            
            // concatenates the exercise and the associated submissions
            $res = DBJson::concatResultObjectLists($data, $res,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_submissions'] ,$submissions,Submission::getDBPrimaryKey(), '2');
                
            $this->_app->response->setBody(Exercise::encodeExercise($res));
            $this->_app->response->setStatus($result['status']);
            if (isset($result['headers']['Content-Type']))
                $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
        } else{
            Logger::Log("GET GetSheetExercises failed",LogLevel::ERROR);
            $this->_app->response->setStatus(409);
            $this->_app->response->setBody(Exercise::encodeExercise(new Exercise()));
            $this->_app->stop();
        }
    }
    
    /**
     * GET GetCourseExercises
     *
     * @param int $courseid a database Course identifier
     */
    public function getCourseExercises($courseid)
    {     
        Logger::Log("starts GET GetCourseExercises",LogLevel::DEBUG);

        // checks whether incoming data has the correct data type
        DBJson::checkInput($this->_app, 
                            ctype_digit($courseid));
                            
        // starts a query, by using a given file
        $result = DBRequest::getRoutedSqlFile($this->query, 
                                        "Sql/GetCourseExercises.sql", 
                                        array("courseid" => $courseid));        

        // checks the correctness of the query                              
        if ($result['status']>=200 && $result['status']<=299){
            $query = Query::decodeQuery($result['content']);

            $data = $query->getResponse();
            
            // generates an assoc array of exercises by using a defined 
            // list of its attributes
            $exercises = DBJson::getObjectsByAttributes($data, Exercise::getDBPrimaryKey(), Exercise::getDBConvert());           
            
            // generates an assoc array of files by using a defined 
            // list of its attributes
            $attachments = DBJson::getObjectsByAttributes($data, File::getDBPrimaryKey(), File::getDBConvert());
            
            // generates an assoc array of submissions by using a defined 
            // list of its attributes
            $submissions = DBJson::getObjectsByAttributes($data, Submission::getDBPrimaryKey(), Submission::getDBConvert(), '2');
             
            // sets the selectedForGroup attribute
            foreach ($submissions as &$submission){
                if (isset($submission['selectedForGroup']) || $submission['selectedForGroup']==null){
                    if (!isset($submission['id'])){
                        $submission['selectedForGroup'] = (string) 0;
                    } elseif ($submission['id'] == $submission['selectedForGroup']) {
                        $submission['selectedForGroup'] = (string) 1;
                    } else
                        $submission['selectedForGroup'] = (string) 0;
                }
                else
                    $submission['selectedForGroup'] = (string) 0;
            }      
            
            // concatenates the exercise and the associated attachments
            $res = DBJson::concatObjectListResult($data, $exercises,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_attachments'] ,$attachments,File::getDBPrimaryKey());  
            
            // concatenates the exercise and the associated submissions
            $res = DBJson::concatResultObjectLists($data, $res,Exercise::getDBPrimaryKey(),Exercise::getDBConvert()['E_submissions'] ,$submissions,Submission::getDBPrimaryKey(), '2');
                
            $this->_app->response->setBody(Exercise::encodeExercise($res));
            $this->_app->response->setStatus($result['status']);
            if (isset($result['headers']['Content-Type']))
                $this->_app->response->headers->set('Content-Type', $result['headers']['Content-Type']);
                
        } else{
            Logger::Log("GET GetCourseExercises failed",LogLevel::ERROR);
            $this->_app->response->setStatus(409);
            $this->_app->response->setBody(Exercise::encodeExercise(new Exercise()));
            $this->_app->stop();
        }
    }
}
?>