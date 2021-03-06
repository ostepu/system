<?php
/**
 * @file DBRedirect.php contains the DBRedirect class
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 *
 * @package OSTEPU (https://github.com/ostepu/system)
 * @since 0.5.0
 *
 * @author Till Uhlig <till.uhlig@student.uni-halle.de>
 * @date 2016
 *
 * @example DB/DBRedirect/RedirectSample.json
 */

include_once ( dirname(__FILE__) . '/../../Assistants/Model.php' );

/**
 * A class, to abstract the "DBRedirect" table from database
 */
class DBRedirect
{
    /**
     * REST actions
     *
     * This function contains the REST actions with the assignments to
     * the functions.
     *
     * @param Component $conf component data
     */
    private $_component = null;
    public function __construct( )
    {
        $component = new Model('redirect,course', dirname(__FILE__), $this, false, false, array('cloneable'=>true,
                                                                                         'addOptionsToParametersAsPostfix'=>true,
                                                                                         'addProfileToParametersAsPostfix'=>true));
        $this->_component=$component;
        $component->run();
    }

    /**
     * Edits a Redirect.
     */
    public function editRedirect( $callName, $input, $params = array() )
    {
        return $this->_component->callSqlTemplate('editRedirect',dirname(__FILE__).'/Sql/EditRedirect.sql',array_merge($params,array('in' => $input)),201,'Model::isCreated',array(new Redirect()),'Model::isProblem',array(new Redirect()));
    }

    /**
     * Deletes a Redirect.
     */
    public function deleteRedirect( $callName, $input, $params = array() )
    {
        return $this->_component->callSqlTemplate('deleteRedirect',dirname(__FILE__).'/Sql/DeleteRedirect.sql',$params,201,'Model::isCreated',array(new Redirect()),'Model::isProblem',array(new Redirect()));
    }

    /**
     * Adds an Redirect.
     */
    public function addRedirect( $callName, $input, $params = array() )
    { 
        $positive = function($input) {
            // sets the new auto-increment id
           $course = Course::ExtractCourse($input[count($input)-1]->getResponse(),true);

            // sets the new auto-increment id
            $obj = new Redirect( );                
            $obj->setId( $course->getId() . '_' . $input[count($input)-2]->getInsertId( ) );
            return array("status"=>201,"content"=>$obj);
        };
        return $this->_component->callSqlTemplate('addRedirect',dirname(__FILE__).'/Sql/AddRedirect.sql',array_merge($params,array( 'in' => $input)),201,$positive,array(),'Model::isProblem',array(new Redirect()),false);
    }

    public function get( $functionName, $linkName, $params=array(), $checkSession = true )
    {
        if (isset($params['redid'])){
            $params['courseid'] = Redirect::getCourseFromRedirectId($params['redid']);
            $params['redid'] = Redirect::getIdFromRedirectId($params['redid']);
        }
        
        $positive = function($input) {
            //$input = $input[count($input)-1];
            $result = Model::isEmpty();$result['content']=array();
            foreach ($input as $inp){
                if ( $inp->getNumRows( ) > 0 ){
                    // extract redirect data from db answer
                    $res = Redirect::ExtractRedirect( $inp->getResponse( ), false);
                    $result['content'] = array_merge($result['content'], (is_array($res) ? $res : array($res)));
                    $result['status'] = 200;
                }
            }
            return $result;
        };

        $params = DBJson::mysql_real_escape_string( $params );
        return $this->_component->call($linkName, $params, '', 200, $positive, array(), 'Model::isProblem', array(), 'Query');
    }

    public function getMatch($callName, $input, $params = array())
    {
        return $this->get($callName,$callName,$params);
    }

    /**
     * Removes the component from a given course
    */
    public function deleteCourse($callName, $input, $params = array())
    {
        return $this->_component->callSqlTemplate('deleteCourse',dirname(__FILE__).'/Sql/DeleteCourse.sql',$params,201,'Model::isCreated',array(new Course()),'Model::isProblem',array(new Course()),false);
    }

    /**
     * Adds the component to a course
     */
    public function addCourse($callName, $input, $params = array())
    {
        $positive = function($input, $course) {
            return array("status"=>201,"content"=>$course);
        };
        return $this->_component->callSqlTemplate('addCourse',dirname(__FILE__).'/Sql/AddCourse.sql',array_merge($params,array('object' => $input)),201,$positive,array('course'=>$input),'Model::isProblem',array(new Course()),false);
    }

    public function getApiProfiles( $callName, $input, $params = array() )
    {   
        $myName = $this->_component->_conf->getName();
        $profiles = array();
        $profiles['readonly'] = GateProfile::createGateProfile(null,'readonly');
        $profiles['readonly']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'GET /redirect/:path+',null));
        
        $profiles['general'] = GateProfile::createGateProfile(null,'general');
        $profiles['general']->setRules($profiles['readonly']->getRules());
        $profiles['general']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'DELETE /redirect/:path+',null));
        $profiles['general']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'PUT /redirect/:path+',null));
        $profiles['general']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'POST /redirect/:path+',null));
        $profiles['general']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'DELETE /course/:courseid',null));
        $profiles['general']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'POST /course',null));
        $profiles['general']->addRule(GateRule::createGateRule(null,'httpCall',$myName,'GET /link/exists/course/:courseid',null));
        
        $profiles['develop'] = GateProfile::createGateProfile(null,'develop');
        $profiles['develop']->setRules(array_merge($profiles['general']->getRules(), $this->_component->_com->apiRulesDevelop($myName)));

        ////$profiles['public'] = GateProfile::createGateProfile(null,'public');
        return Model::isOk(array_values($profiles));
    }
}