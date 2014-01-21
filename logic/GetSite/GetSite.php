<?php

require 'Slim/Slim.php';
include 'include/Request.php';
include_once( 'include/CConfig.php' );

\Slim\Slim::registerAutoloader();
/**
 * The GetSite class
 *
 * This class gives all informations needed to print a Site
 */
class LgetSite
{
    /**
     *Values needed for conversation with other components
     */
    private $_conf=null;

    private static $_prefix = "getSite";

    public static function getPrefix()
    {
        return LgetSite::$_prefix;
    }
    public static function setPrefix($value)
    {
        LgetSite::$_prefix = $value;
    }


    /**
     *Address of the Logic-Controller
     *dynamic set by CConf below
     */
    private $lURL = "http://localhost/uebungsplattform/Controller";

    public function __construct($conf)
    {
        /**
         *Initialise the Slim-Framework
         */
        $this->app = new \Slim\Slim();
        $this->app->response->headers->set('Content-Type', 'application/json');
        /**
         *Set the Logiccontroller-URL
         */
        $this->_conf = $conf;
        $this->query = array();
        
        $this->query = CConfig::getLink($conf->getLinks(),"controller");
        $this->lURL = $this->query->getAddress();


        //GET TutorAssignmentSiteInfo
        $this->app->get('/tutorassignment/course/:courseid/exercisesheet/:sheetid(/)', array($this, 'tutorAssignmentSiteInfo'));

        //GET StudentSiteInfo
        $this->app->get('/student/user/:userid/course/:courseid(/)', array($this, 'studentSiteInfo'));
        
        //run Slim
        $this->app->run();
    }

    public function tutorAssignmentSiteInfo($courseid, $sheetid){

        $response = array();
        $assignedSubmissionIDs = array();
        /**
         * Get Tutors
         */
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        $URL = $this->lURL.'/DB/coursestatus/course/'.$courseid.'/status/1'; //status = 1 => Tutor
        $answer = Request::custom('GET', $URL, $header, $body);
        $tutors = json_decode($answer['content'], true);
        foreach ($tutors AS $tutor){
            //benoetigte Attribute waehlen
            $newTutor = array();
            $newTutor['id'] = $tutor['id'];
            $newTutor['userName'] = $tutor['userName'];
            $newTutor['firstName'] = $tutor['firstName'];
            $newTutor['lastName'] = $tutor['lastName'];
            //im Rueckgabe-Array für jeden Tutor ein Marking (ohne Submissions) anlegen
            $tutorAssignment = array(
                    'tutor' => $newTutor,
                    'submissions' => array()
                    );
            array_push($response,$tutorAssignment);
        }
        /**
         * Get Markings
         */
        $URL = $this->lURL.'/DB/exercisesheet/'.$sheetid;
        $answer = Request::custom('GET', $URL, $header, $body);
        //fuer jedes Marking die zugeordnete Submision im Rueckgabearray dem passenden Tutor zuweisen
        foreach (json_decode($answer['content'], true) as $marking){
            foreach ($response as &$tutorAssignment){
                if ($marking['tutorId'] == $tutorAssignment['tutor']['id']){
                    array_push($tutorAssignment['submissions'], $marking['submission']);
                    //ID's aller bereits zugeordneten Submissions speicher
                    array_push($assignedSubmissionIDs, $marking['submission']['id']);
                    break;
                }
            }
        }

        /**
         * Get SelectedSubmissions
         */
        $URL = $this->lURL.'/DB/selectedsubmission/exercisesheet/'.$sheetid;
        $answer = Request::custom('GET', $URL, $header, $body);

        $virtualTutor = array(
                    'id' => null,
                    'userName' => "unassigned",
                    'firstName' => null,
                    'lastName' => null
                    );

        $unassignedSubmissions = array();


        $submissions = json_decode($answer['content'], true);
        foreach ($submissions as $submission){
            if (!in_array($submission['id'], $assignedSubmissionIDs)){
                array_push($unassignedSubmissions, $submission);
            }
        }
        $newTutorAssignment = array(
            'tutor' => $virtualTutor,
            'submissions' => $unassignedSubmissions
                    );
        array_push($response, $newTutorAssignment);

        $this->app->response->setBody(json_encode($response));
    }
    
    public function studentSideInfo($userid, $courseid){
        
        $response = array();
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        
        //get Exercisesheets

        $URL = $this->lURL.'/DB/exercisesheet/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $sheets = json_decode($answer['content'], true);
        foreach ($sheets as $sheet){
            $newSheet = array(
                        'id' => $sheet['id'],
                        'courseId'=> $sheet['courseId'],
                        'endDate'=> $sheet['endDate'],
                        'startDate'=> $sheet['startDate'],
                        'zipFile'=> $sheet['zipFile'],
                        'sampleSolution'=> $sheet['sampleSolution'],
                        'sheetFile'=> $sheet['sheetFile'],
                        'exercises'=> $sheet['exercises'],
                        'groupSize'=> $sheet['groupSize'],
                        'sheetName'=> $sheet['sheetName'],
                        'group'=> array()
                        );
            $response[] = $newShet;
        }
        //get UserGroups
        $URL = $this->lURL.'/DB/group/user/'.$usserid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $groups = json_decode($answer['content'], true);
        
        foreach ($groups as $group){
            foreach ($response as $sheet){
                if ($sheet['id'] == $group['shootId']){
                    $sheet['group'] = $group;
                    break;
                }
            }
        }
        $this->app->response->setBody(json_encode($response));
        
    }
}

/**
 * get new Config-Datas from DB
 */
$com = new CConfig(LgetSite::getPrefix());

/**
 * make a new instance of LgetSite-Class with the Config-Datas
 */
if (!$com->used())
    new LgetSite($com->loadConfig());
?>