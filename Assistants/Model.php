<?php
require_once ( dirname(__FILE__) . '/Slim/Route.php' );
require_once ( dirname(__FILE__) . '/Slim/Router.php' );
require_once ( dirname(__FILE__) . '/Slim/Environment.php' );
include_once ( dirname(__FILE__) . '/Structures.php' );
include_once ( dirname(__FILE__) . '/Request.php' );
include_once ( dirname(__FILE__) . '/Logger.php' );
include_once ( dirname(__FILE__) . '/CConfig.php' );
include_once ( dirname(__FILE__) . '/DBRequest.php' );
include_once ( dirname(__FILE__) . '/DBJson.php' );

class Model
{
    
    /**
     * @var string $_path Der lokale Pfad des Moduls
     */
    private $_path=null;
    
    /**
     * @var string $_prefix Unterstützte Präfixe (veraltet)
     */
    private $_prefix=null;

    /**
     * @var Component $_conf the component data object
     */
    public $_conf = null;
    
    /**
     * @var string $_class Der Klassenname des Moduls
     */
    private $_class = null;
    
    /**
     * @var Component $_com Die Definition der Ausgänge
     */
    private $_com = null;
    
    /**
     * Der Konstruktor
     *
     * @param string Unterstützte Präfixe (veraltet)
     * @param string Der lokale Pfade des Moduls
     * @param string Der Klassenname des Moduls
     */
    public function __construct( $prefix, $path, $class )
    {
        $this->_path=$path;
        $this->_prefix=$prefix;
        $this->_class=$class;
    }

    /**
     * Führt das Modul entsprechend der Commands.json und Component.json Definitionen aus
     */
    public function run()
    {
        // runs the CConfig
        $com = new CConfig( $this->_prefix, $this->_path );

        // lädt die Konfiguration des Moduls
        if ( $com->used( ) ) return;
            $conf = $com->loadConfig( );
        $this->_conf=$conf;
        $this->_com=$com;
        $commands = $com->commands(array(),true,true);
        
        // multi Requests werden noch nicht unterstützt, das Model soll automatisch die Möglichkeit bieten,
        // mehrere Anfragen mit einmal zu beantworten
        ////$commands[] = array('name' => 'postMultiGetRequest','method' => 'POST', 'path' => '/multiGetRequest', 'inputType' => 'Link', 'outputType' => '');
        
        // Erstellt für jeden angebotenen Befehl einen Router
        // Ein Router stellt einen erlaubten Aufruf dar (mit Methode und URI), sodass geprüft werden kann,
        // welcher für die Beantwortung zuständig ist
        $router = new \Slim\Router();
        foreach ($commands as $command){
            $route = new \Slim\Route($command['path'],array($this->_class,(isset($command['callback']) ? $command['callback'] : $command['name'])),false);
            $route->via(strtoupper($command['method']));
            $route->setName($command['name']);
            $router->map($route);
            
            // wenn es ein GET Befehl ist, wird automatisch HEAD unterstützt
            if (strtoupper($command['method'])=='GET'){
                // erzeugt einen HEAD Router
                $route = new \Slim\Route($command['path'],array($this->_class,(isset($command['callback']) ? $command['callback'] : $command['name'])),false);
                $route->via('HEAD');
                $route->setName($command['name']);
                $router->map($route);
            }
        }

        // hier wird die eingehende URI erzeugt
        // Bsp.: /user/1
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = str_replace('?' . (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''), '', substr_replace($requestUri, '', 0, strlen((strpos($requestUri, $scriptName) !== false ? $scriptName : str_replace('\\', '', dirname($scriptName))))));
        
        // ermittelt den zuständigen Befehl
        $matches = $router->getMatchedRoutes(strtoupper($_SERVER['REQUEST_METHOD']), $path);

        if (count($matches)>0){
            // mindestens ein zutreffender Befehl wurde gefunden (nimm den Ersten)
            $matches = $matches[0];
            
            // suche den zugehörigen $commands Eintrag
            $selectedCommand=null;
            foreach ($commands as $command){
                if ($command['name'] === $matches->getName()){
                    $selectedCommand = $command;
                    break;
                }
            }
            
            // lies die eingehenden PHP Daten
            $rawInput = \Slim\Environment::getInstance()->offsetGet('slim.input');
            if (!$rawInput) {
                $rawInput = @file_get_contents('php://input');
            }

            // wir wollen wissen, ob die Eingabedaten als Liste rein kommen
            $arr = true;
            
            // wenn zu diesem Befehl ein inputType angegeben wurde, wird eine Type::decodeType() aufgerufen
            if (isset($selectedCommand['inputType']) && trim($selectedCommand['inputType'])!=''){
                $inputType = $selectedCommand['inputType'];
                $rawInput = call_user_func_array('\\'.$inputType.'::decode'.$inputType, array($rawInput));
                        
                if ( !is_array( $rawInput ) ){
                    // es ist keine Liste, also mach eine daraus (damit man sie einheitlich behandeln kann)
                    $rawInput = array( $rawInput );
                    $arr = false;
                }
            }

            // nun soll die zugehörige Funktion im Modul aufgerufen werden
            $params = $matches->getParams();
            if (isset($selectedCommand['inputType']) && trim($selectedCommand['inputType'])!='' && isset($rawInput)){
                // initialisiert die Ausgabe positiv
                $result=array("status"=>201,"content"=>array());
                
                // für jede Eingabe wird die Funktion ausgeführt
                foreach($rawInput as $input){
                    
                    // Aufruf der Modulfunktion
                    $res = call_user_func_array($matches->getCallable(), array($selectedCommand['name'],"input"=>$input,$params));
                    
                    // wenn es ein Ausgabeobjekt gibt, wird versucht dort einen Status zu setzen
                    if (is_callable(array($res['content'],'setStatus'))){
                        $res['content']->setStatus($res['status']);
                    }
                    
                    // setze Status und Ausgabe
                    $result["content"][] = $res['content'];
                    if (isset($res['status'])){
                        $result["status"] = $res['status'];
                    }
                }
            } else {
                // wenn keinen vorgegebenen Eingabetyp gibt, wird die Eingabe direkt an die Modulfunktion weitergegeben
                $result = call_user_func_array($matches->getCallable(), array($selectedCommand['name'],"input"=>$rawInput,$params));
            }
            
            if ($selectedCommand['method']=='HEAD'){
                // Bei einer HEAD Funktion (die eventuell im Modul als GET bearbeitet wird),
                // kann die Ausgabe verworfen werden
                $result['content'] = '';
            } elseif (isset($selectedCommand['outputType']) && trim($selectedCommand['outputType'])!='' && trim($selectedCommand['outputType'])!='binary'){
                // wenn ein Ausgabetyp angegeben ist, wird eine Typ::encodeTyp() ausgeführt                
                $outputType = $selectedCommand['outputType'];
                
                if (isset( $result['content']) ){
                    if ( !is_array( $result['content'] ) ){
                        $result['content'] = array( $result['content'] );
                    }
                    
                    if ( !$arr && count( $result['content'] ) == 1 ){
                        $result['content'] = $result['content'][0];
                    }

                    $result['content'] = call_user_func_array('\\'.$outputType.'::encode'.$outputType, array($result['content']));
                }
                header('Content-Type: application/json');                                            
            } elseif (isset($selectedCommand['outputType']) && trim($selectedCommand['outputType'])=='binary'){
                // wenn kein konkreter Ausgabetyp angegeben ist, soll nur die Umwandlung
                // nach json erfolgen
                if (isset( $result['content'])){
                    if (!is_string($result['content']))
                        $result['content'] = json_encode($result['content']);
                }
            } else {
                // selbst wenn nichts zutrifft, wird json kodiert
                if (isset( $result['content']) )
                    $result['content'] = json_encode($result['content']);
            }
        } else {
            // es wurde kein zutreffender Befehl gefunden, also gibt es eine leere Antwort
            $result=self::isEmpty();
        }

        // ab hier werden die Ergebnisse ausgegeben
        if (isset( $result['content'])  )
            echo $result['content'];  
                    
        if (isset( $result['status']) ){
            http_response_code($result['status']); 
        } else 
            http_response_code(200); 
    }
    
    /**
     * Führt eine Anfrage über $linkName aus
     *
     * @param string $linkName Der Name des Ausgangs
     * @param mixed[] $params Die Ersetzungen für die Platzhalter des Befehls (Bsp.: array('uid'=>2,'cid'=>1)
     * @param string body Der Inhalt der Anfrage für POST und PUT
     * @param int $positiveStatus Der Status, welcher als erfolgreiche Antwort gesehen wird (Bsp.: 200)
     * @param callable $positiveMethod Im positiven Fall wird diese Methode aufgerufen
     * @param mixed[] $positiveParams Die Werte, welche an die positive Funktion übergeben werden
     * @param callable $negativeMethod Im negativen Fall wird diese Methode aufgerufen
     * @param mixed[] $negativeParams Die Werte, welche an die negative Funktion übergeben werden
     * @param string returnType Ein optionaler Rückgabetyp (es können Structures angegeben werden, sodass automatisch Typ::encodeType() ausgelöst wird)
     * @return mixed Das Ergebnis der aufgerufenen Resultatfunktion
     */
    public function call($linkName, $params, $body, $positiveStatus, callable $positiveMethod, $positiveParams, callable $negativeMethod, $negativeParams, $returnType=null)
    {
        $link=CConfig::getLink($this->_conf->getLinks( ),$linkName);
        $instructions = $this->_com->instruction(array(),true)['links'];
        
        // ermittle den zutreffenden Ausgang
        $selectedInstruction=null;
        foreach($instructions as $instruction){
            if ($instruction['name']==$linkName){
                $selectedInstruction=$instruction;
                break;
            }
        }
        
        $order = $selectedInstruction['links'][0]['path'];
        
        // ersetzt die Platzer im Ausgang mit den eingegeben Parametern
        foreach ($params as $key=>$param)
            $order = str_replace( ':'.$key, $param, $order);

        // führe nun den Aufruf aus
        $result = Request::routeRequest( 
                                        $selectedInstruction['links'][0]['method'],
                                        $order,
                                        array(),
                                        $body,
                                        $link,
                                        $link->getPrefix()
                                        );    
                      
        if ( $result['status'] == $positiveStatus ){
            // die Antwort war so, wie wir sie erwartet haben
            if ($returnType!==null){
                // wenn ein erwarteter Rückgabetyp angegeben wurde, wird eine Typ::decodeType() ausgeführt
                $result['content'] = call_user_func_array('\\'.$returnType.'::decode'.$returnType, array($result['content']));
                if ( !is_array( $result['content'] ) )
                    $result['content'] = array( $result['content'] );
            }
            
            // rufe nun die positive Methode auf
            return call_user_func_array($positiveMethod, array_merge(array("input"=>$result['content']),$positiveParams));
        }
        
        // ansonsten rufen wir die negative Methode auf
        return call_user_func_array($negativeMethod, $negativeParams);
    }
    
    /**
     * Sendet den Inhalt von $file an $linkName und behandelt die Antwort
     *
     * @param string $linkName Der Name des Ausgangs
     * @param string $file Der Pfad des SQL Templates
     * @param mixed[] $params Die Variablen, welche im Template verwendet werden können (Bsp.: array('time'=>12)
     * @param int $positiveStatus Der Status, welcher als erfolgreiche Antwort gesehen wird (Bsp.: 200)
     * @param callable $positiveMethod Im positiven Fall wird diese Methode aufgerufen
     * @param mixed[] $positiveParams Die Werte, welche an die positive Funktion übergeben werden
     * @param callable $negativeMethod Im negativen Fall wird diese Methode aufgerufen
     * @param mixed[] $negativeParams Die Werte, welche an die negative Funktion übergeben werden
     * @param bool $checkSession Ob die Sessiondaten in der Datenbank geprüft werden sollen
     * @return mixed Das Ergebnis der aufgerufenen Resultatfunktion
     */
    public function callSqlTemplate($linkName, $file, $params, $positiveStatus, callable $positiveMethod, $positiveParams, callable $negativeMethod, $negativeParams, $checkSession=true)
    {
        $link=CConfig::getLink($this->_conf->getLinks( ),$linkName);
        
        // führe nun den Aufruf mit der SQL $file aus
        $result = DBRequest::getRoutedSqlFile( 
                                              $link,
                                              $file,
                                              $params,
                                              $checkSession
                                              );

        if ( $result['status'] == $positiveStatus){
            // die Antwort war so, wie wir sie erwartet haben
            $queryResult = Query::decodeQuery( $result['content'] );
            if (!is_array($queryResult)) $queryResult = array($queryResult);
            
            // rufe nun die positive Methode auf
            return call_user_func_array($positiveMethod, array_merge(array("input"=>$queryResult),$positiveParams));
        }
        
        // ansonsten rufen wir die negative Methode auf
        return call_user_func_array($negativeMethod, $negativeParams);
    }
    
    /**
     * Sendet $sql an $linkName und behandelt die Antwort
     *
     * @param string $linkName Der Name des Ausgangs
     * @param string $sql Der zu verwendende SQL Inhalt
     * @param int $positiveStatus Der Status, welcher als erfolgreiche Antwort gesehen wird (Bsp.: 200)
     * @param callable $positiveMethod Im positiven Fall wird diese Methode aufgerufen
     * @param mixed[] $positiveParams Die Werte, welche an die positive Funktion übergeben werden
     * @param callable $negativeMethod Im negativen Fall wird diese Methode aufgerufen
     * @param mixed[] $negativeParams Die Werte, welche an die negative Funktion übergeben werden
     * @param bool $checkSession Ob die Sessiondaten in der Datenbank geprüft werden sollen
     * @return mixed Das Ergebnis der aufgerufenen Resultatfunktion
     */
    public function callSql($linkName, $sql, $positiveStatus, callable $positiveMethod, $positiveParams, callable $negativeMethod, $negativeParams, $checkSession=true)
    {
        $link=CConfig::getLink($this->_conf->getLinks( ),$linkName);
        // starts a query, by using given sql statements/statement
        $result = DBRequest::getRoutedSql( 
                                              $link,
                                              $sql,
                                              $checkSession
                                              );

        // checks the correctness of the query
        if ( $result['status'] == $positiveStatus){
            // die Antwort war so, wie wir sie erwartet haben
            $queryResult = Query::decodeQuery( $result['content'] );
            if (!is_array($queryResult)) $queryResult = array($queryResult);
            
            // rufe nun die positive Methode auf
            return call_user_func_array($positiveMethod, array_merge(array("input"=>$queryResult),$positiveParams));
        }
        
        // ansonsten rufen wir die negative Methode auf
        return call_user_func_array($negativeMethod, $negativeParams);
    }
    
    /**
     * Liefert eine Rückgabe
     *
     * @param int $status Der Status
     * @param string $content Der optionale Inhalt
     * @return array('status'=>..,'content'=>..) Die Antwort
     */
    public static function createAnswer($status=200, $content=''){
        return array("status"=>$status,"content"=>$content);
    }
    
    /**
     * Liefert eine Rückgabe (ein Problem ist aufgetreten)
     *
     * @param string $content Der optionale Inhalt
     * @return array('status'=>..,'content'=>..) Die Antwort
     */
    public static function isProblem($content=null){
        return self::createAnswer(409,$content);
    }
    
    /**
     * Liefert eine Rückgabe (wurde erstellt)
     *
     * @param string $content Der optionale Inhalt
     * @return array('status'=>..,'content'=>..) Die Antwort
     */
    public static function isCreated($content=null){
        return self::createAnswer(201,$content);
    }
    
    /**
     * Liefert eine Rückgabe (Anfrage war erfolgreich)
     *
     * @param int $status Der Status
     * @param string $content Der optionale Inhalt
     * @return array('status'=>..,'content'=>..) Die Antwort
     */
    public static function isOk($content=null){
        return self::createAnswer(200,$content);
    }
    
    /**
     * Liefert eine Rückgabe (Ressource wurde nicht gefunden)
     *
     * @param int $status Der Status
     * @param string $content Der optionale Inhalt
     * @return array('status'=>..,'content'=>..) Die Antwort
     */
    public static function isEmpty($content=null){
        return self::createAnswer(404,$content);
    }

    /**
     * Liefert eine Rückgabe (ein Problem ist aufgetreten)
     *
     * @param int $status Der Status
     * @param string $content Der optionale Inhalt
     * @return array('status'=>..,'content'=>..) Die Antwort
     */
    public static function isRejected($content=null){
        return self::createAnswer(401,$content);
    }
}