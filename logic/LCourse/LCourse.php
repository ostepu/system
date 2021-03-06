<?php
/**
 * @file LCourse.php contains the LCourse class
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 *
 * @package OSTEPU (https://github.com/ostepu/system)
 * @since 0.1.1
 *
 * @author Till Uhlig <till.uhlig@student.uni-halle.de>
 * @date 2014-2016
 */

require_once dirname(__FILE__) . '/../../Assistants/vendor/Slim/Slim/Slim.php';
include_once dirname(__FILE__) . '/../../Assistants/Request.php';
include_once dirname(__FILE__) . '/../../Assistants/CConfig.php';
include_once ( dirname(__FILE__) . '/../../Assistants/Logger.php' );
include_once ( dirname(__FILE__) . '/../../Assistants/Structures.php' );

\Slim\Slim::registerAutoloader();

/**
 * A class, to handle requests to the LCourse-Component
 */
class LCourse
{
    /**
     * @var Slim $_app the slim object
     */
    private $app = null;

    /**
     * @var Component $_conf the component data object
     */
    private $_conf=null;

    /**
     * @var string $_prefix the prefix, the class works with
     */
    private static $_prefix = "course";

    /**
     * the $_prefix getter
     *
     * @return the value of $_prefix
     */
    public static function getPrefix()
    {
        return LCourse::$_prefix;
    }

    /**
     * the $_prefix setter
     *
     * @param string $value the new value for $_prefix
     */
    public static function setPrefix($value)
    {
        LCourse::$_prefix = $value;
    }

    /**
     * @var string $lURL the URL of the logic-controller
     */
    private $lURL = ""; // readed out from config below

    /**
     * @var Link[] $_out a list of links
     */
    private $_deleteCourse = array( );
    private $_postCourse = array( );

    /**
     * REST actions
     *
     * This function contains the REST actions with the assignments to
     * the functions.
     *
     * @param Component $conf component data
     */
    public function __construct()
    {
        // runs the CConfig
        $com = new CConfig( LCourse::getPrefix( ), dirname(__FILE__) );

        // runs the LCourse
        if ( $com->used( ) ) return;
            $conf = $com->loadConfig( );

        // initialize slim
        $this->app = new \Slim\Slim(array('debug' => true));
        $this->app->response->headers->set('Content-Type', 'application/json');

        // initialize component
        $this->_conf = $conf;
        $this->query = CConfig::getLink($conf->getLinks(),"controller");
        $this->_deleteCourse = CConfig::getLinks($conf->getLinks(),"deleteCourse");
        $this->_postCourse = CConfig::getLinks($conf->getLinks(),"postCourse");

        // initialize lURL
        $this->lURL = $this->query->getAddress();

        //POST AddCourse
        $this->app->post('/'.$this->getPrefix().'(/)', array($this, 'AddCourse'));

        //PUT EditCourse
        $this->app->put('/'.$this->getPrefix().'/course/:courseid(/)', array($this, 'editCourse'));

        //DELETE DeleteCourse
        $this->app->delete('/'.$this->getPrefix().'/course/:courseid(/)', array($this, 'deleteCourse'));

        //POST AddCourseMember
        $this->app->post('/'.$this->getPrefix().'/course/:courseid/user/:userid/status/:status(/)', array($this, 'addCourseMember'));

        //GET GetCourseMember
        $this->app->get('/'.$this->getPrefix().'/course/:courseid/user(/)', array($this, 'getCourseMember'));

        //GET GetCourses
        $this->app->get('/'.$this->getPrefix().'/user/:userid(/)', array($this, 'getCourses'));

        //run Slim
        $this->app->run();
    }

    /**
     * Adds a course.
     *
     * Called when this component receives an HTTP POST request to
     * /course(/).
     * The request body should contain a JSON object representing the course's
     * attributes.
     *
     * @author Till Uhlig
     * @date 2014
     */
    public function AddCourse()
    {
         Logger::Log(
                    'starts POST AddCourse',
                    LogLevel::DEBUG
                    );

        $header = $this->app->request->headers->all();
        $body = $this->app->request->getBody();

        $course = Course::decodeCourse($body);

        foreach ( $this->_postCourse as $_link ){
            $result = Request::routeRequest(
                                            'POST',
                                            '/course',
                                            $header,
                                            Course::encodeCourse($course),
                                            $_link,
                                            'course'
                                            );

            // checks the correctness of the query
            if ( $result['status'] >= 200 &&
                 $result['status'] <= 299 ){
                $queryResult = Course::decodeCourse( $result['content'] );
                if (is_array($queryResult)){$queryResult=$queryResult[0];}

                // sets the new auto-increment id
                $course->setId( $queryResult->getId( ) );

                $this->app->response->setStatus( 201 );
                if ( isset( $result['headers']['Content-Type'] ) )
                    $this->app->response->headers->set(
                                                        'Content-Type',
                                                        $result['headers']['Content-Type']
                                                        );

            } else {

                if ($course->getId()!==null){
                    //$this->deleteCourse($course->getId());
                }

                Logger::Log(
                            'POST AddCourse failed',
                            LogLevel::ERROR
                            );
                $this->app->response->setStatus( isset( $result['status'] ) ? $result['status'] : 409 );
                $this->app->response->setBody( Course::encodeCourse( $course ) );
                $this->app->stop( );
            }
        }

        $this->app->response->setBody( Course::encodeCourse( $course ) );
    }

    /**
     * Edits a course.
     *
     * Called when this component receives an HTTP PUT request to
     * /course/course/$courseid(/).
     * The request body should contain a JSON object representing the course's new
     * attributes.
     *
     * @param int $courseid The id of the course that is being updated.
     */
    public function editCourse($courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        $URL = $this->lURL.'/DB/course/course/'.$courseid;
        $answer = Request::custom('PUT', $URL, $header, $body);
        $this->app->response->setStatus($answer['status']);
    }

    /**
     * Deletes a course.
     *
     * Called when this component receives an HTTP DELETE request to
     * /course/course/$courseid(/).
     *
     * @param int $courseid The id of the course that is being deleted.
     *
     * @author Till Uhlig
     * @date 2014
     */
    public function deleteCourse($courseid){
        Logger::Log(
                    'starts DELETE DeleteCourse',
                    LogLevel::DEBUG
                    );
        $this->app->response->setStatus( 201 );

        $header = $this->app->request->headers->all();
        $courseid = DBJson::mysql_real_escape_string( $courseid );

        foreach ( $this->_deleteCourse as $_link ){
            $result = Request::routeRequest(
                                            'DELETE',
                                            '/course/course/'.$courseid,
                                            $header,
                                            '',
                                            $_link,
                                            'course'
                                            );

            // checks the correctness of the query
            if ( $result['status'] == 201){
                // ok
            } else {
                Logger::Log(
                            'DELETE DeleteCourse failed',
                            LogLevel::ERROR
                            );
                $this->app->response->setStatus( isset( $result['status'] ) ? $result['status'] : 409 );
                $this->app->stop( );
            }
        }
    }

    /**
     * Adds a user to a course.
     *
     * Called when this component receives an HTTP POST request to
     * /course/$courseid/user/$userid/status/$status(/).
     *
     * @param int $courseid The id of the course to which the user is being added.
     * @param int $userid The id of the user that is being added.
     * @param int $status The status this user should have in this course.
     */
    public function addCourseMember($courseid, $userid, $status)
    {
        $header = $this->app->request->headers->all();
        $body = array('id' => $userid,
                      'courses' => array(array('status' => $status,
                                               'course' => array('id' => $courseid)
                                               )
                                         )
                      );
        $body = json_encode($body);
        $URL = $this->lURL.'/DB/coursestatus';
        $answer = Request::custom('POST', $URL, $header, $body);
        $this->app->response->setStatus($answer['status']);
    }

    /**
     * Returns all users to a course.
     *
     * Called when this component receives an HTTP GET request to
     * /course/course/$courseid/user(/).
     *
     * @param int $courseid The id of the course whose users should be returned
     */
    public function getCourseMember($courseid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        $URL = $this->lURL.'/DB/user/course/'.$courseid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $this->app->response->setStatus($answer['status']);
        $this->app->response->setBody($answer['content']);
    }

    /**
     * Returns all courses a given user belongs to.
     *
     * Called when this component receives an HTTP GET request to
     * /course/user/$userid(/).
     *
     * @param int $userid The id of the user.
     */
    public function getCourses($userid)
    {
        $body = $this->app->request->getBody();
        $header = $this->app->request->headers->all();
        $URL = $this->lURL.'/DB/course/user/'.$userid;
        $answer = Request::custom('GET', $URL, $header, $body);
        $this->app->response->setStatus($answer['status']);
        $this->app->response->setBody($answer['content']);
    }
}