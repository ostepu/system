<?php
/**
 * @file Exercise.php contains the Exercise class
 */
 
/**
 * the exercise structure
 *
 * @author Till Uhlig, Florian Lücke
 */
class Exercise extends Object implements JsonSerializable
{
    /**
     * @var string $id a string that identifies the exercise.
     */
    private $id = null;
    
    /**
     * the $id getter
     *
     * @return the value of $id
     */ 
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * the $id setter
     *
     * @param string $value the new value for $id
     */
    public function setId($value){
        $this->id = $value;
    }

    /**
     * @var string $courseId The id of the course this exercise belongs to.
     */
    private $courseId = null;
    
    /**
     * the $courseId getter
     *
     * @return the value of $courseId
     */ 
    public function getCourseId()
    {
        return $this->courseId;
    }
    
    /**
     * the $courseId setter
     *
     * @param string $value the new value for $courseId
     */
    public function setCourseId($value){
        $this->courseId = $value;
    }

    /**
     * @var string $sheetId The id of the sheet this exercise is on.
     */
    private $sheetId = null;
    
    /**
     * the $sheetId getter
     *
     * @return the value of $sheetId
     */ 
    public function getSheetId()
    {
        return $this->sheetId;
    }
    
    /**
     * the $sheetId setter
     *
     * @param string $value the new value for $sheetId
     */
    public function setSheetId($value){
        $this->sheetId = $value;
    }

    /**
     * @var int $maxPoints The maximum amount of points a student can reach in this exercise.
     */
    private $maxPoints = null;
    
    /**
     * the $maxPoints getter
     *
     * @return the value of $maxPoints
     */ 
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }
    
    /**
     * the $maxPoints setter
     *
     * @param int $value the new value for $maxPoints
     */
    public function setMaxPoints($value){
        $this->maxPoints = $value;
    }

    /**
     * @var int $type The type of points this exercise yields.
     */
    private $type = null;
    
    /**
     * the $type getter
     *
     * @return the value of $type
     */ 
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * the $int setter
     *
     * @param string $value the new value for $type
     */
    public function setType($value){
        $this->type = $value;
    }
    
    /**
     * @var int $link The type of points this exercise yields.
     */
    private $link = null;
    
    /**
     * the $link getter
     *
     * @return the value of $link
     */ 
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * the $link setter
     *
     * @param int $value the new value for $link
     */
    public function setLink($value){
        $this->link = $value;
    }

    /**
     * @var Submission[] $submissiona the submissions for this exercise
     */
    private $submissions = array();
    
    /**
     * the $submissions getter
     *
     * @return the value of $submissions
     */ 
    public function getSubmissions()
    {
        return $submissions;
    }
    
    /**
     * the $submissions setter
     *
     * @param Submission[] $value the new value for $submissions
     */
    public function setSubmissions($value){
        $submissions = $value;
    }
    
    /**
     * @var File[] $attachements a set of attachments that belong to this sheet
     */
    private $attachments = array();
    
    /**
     * the $attachments getter
     *
     * @return the value of $attachments
     */ 
    public function getAttachments()
    {
        return $this->attachments;
    }
    
    /**
     * the $attachments setter
     *
     * @param File[] $value the new value for $attachments
     */
    public function setAttachments($value){
        $this->attachments = $value;
    }
    
    /**
     * @var bool $bonus bonus=true means bonus points
     */
    private $bonus = null;
    
    /**
     * the $bonus getter
     *
     * @return the value of $bonus
     */ 
    public function getBonus()
    {
        return $this->bonus;
    }
    
    /**
     * the $bonus setter
     *
     * @param Bool $value the new value for $bonus
     */
    public function setBonus($value){
        $this->bonus = $value;
    }
    
    /**
     * Creates an Exercise object, for database post(insert) and put(update).
     * Not needed attributes can be set to null.
     *
     * @param string $exerciseId The id of the exercise.
     * @param string $courseId The id of the course. (do not use!)
     * @param string $sheetId The id of the exercise sheet.
     * @param string $maxPoints the max points
     * @param string $type the id of the exercise type
     * @param string $link the id of the exercise, this exercise belongs to
     * @param string $bonus the bonus flag
     *
     * @return an exercise object
     */
    public static function createExercise($exerciseId,$courseId,$sheetId,
                                    $maxPoints,$type,$link,$bonus)
    {
        return new Exercise(array('id' => $exerciseId,
        'courseId' => $courseId,
        'sheetId' => $sheetId, 
        'maxPoints' => $maxPoints, 
        'type' => $type, 
        'link' => $link, 
        'bonus' => $bonus));
    }
    
    /**
     * returns an mapping array to convert between database and structure
     *
     * @return the mapping array
     */
    public static function getDbConvert()
    {
        return array(
           'E_id' => 'id',
           'C_id' => 'courseId',
           'ES_id' => 'sheetId',
           'E_maxPoints' => 'maxPoints',
           'ET_id' => 'type',
           'E_id_link' => 'link',
           'E_submissions' => 'submissions',
           'E_bonus' => 'bonus',
           'E_attachments' => 'attachments'
        );
    }
    
     /**
     * converts an object to insert/update data
     *
     * @return a comma separated string e.g. "a=1,b=2"
     */
    public function getInsertData()
    {
        $values = "";
        
        if ($this->id != null) $this->addInsertData($values, 'E_id', DBJson::mysql_real_escape_string($this->id));
        if ($this->sheetId != null) $this->addInsertData($values, 'ES_id', DBJson::mysql_real_escape_string($this->sheetId));
        if ($this->maxPoints != null) $this->addInsertData($values, 'E_maxPoints', DBJson::mysql_real_escape_string($this->maxPoints));
        if ($this->type != null) $this->addInsertData($values, 'ET_id', DBJson::mysql_real_escape_string($this->type));
        if ($this->link != null) $this->addInsertData($values, 'E_id_link', DBJson::mysql_real_escape_string($this->link));
        if ($this->bonus != null) $this->addInsertData($values, 'E_bonus', DBJson::mysql_real_escape_string($this->bonus));
        
        if ($values != ""){
            $values=substr($values,1);
        }
        return $values;
    }
    
    /**
     * returns a sting/string[] of the database primary key/keys
     * 
     * @return the primary key/keys
     */
    public static function getDbPrimaryKey()
    {
        return 'E_id';
    }
    
    /**
     * the constructor
     * 
     * @param $data an assoc array with the object informations
     */
    public function __construct($data=array()) 
    {
        foreach ($data AS $key => $value) {
             if (isset($key)){
                if ($key == 'submissions'){
                    $this->{$key} = Submission::decodeSubmission($value, false);
                }elseif ($key == 'attachments') {
                    $this->{$key} = File::decodeFile($value, false);
                } else
                $this->{$key} = $value;
            }
        }
    }
    
    /**
     * encodes an object to json
     * 
     * @param $data the object
     *
     * @return the json encoded object
     */
    public static function encodeExercise($data)
    {
        return json_encode($data);
    }
    
    /**
     * decodes $data to an object
     * 
     * @param string $data json encoded data (decode=true) 
     * or json decoded data (decode=false)
     * @param bool $decode specifies whether the data must be decoded
     *
     * @return the object
     */
    public static function decodeExercise($data, $decode=true)
    {
        if ($decode && $data==null) 
            $data = "{}";
    
        if ($decode)
            $data = json_decode($data);
        if (is_array($data)){
            $result = array();
            foreach ($data AS $key => $value) {
                array_push($result, new Exercise($value));
            }
            return $result;   
        } else
            return new Exercise($data);
    }
    
    /**
     * the json serialize function
     *
     * @return an array to serialize the object
     */
    public function jsonSerialize()
    {
        $list = array();
        if ($this->id!==null) $list['id'] = $this->id;
        if ($this->courseId!==null) $list['courseId'] = $this->courseId;
        if ($this->sheetId!==null) $list['sheetId'] = $this->sheetId;
        if ($this->maxPoints!==null) $list['maxPoints'] = $this->maxPoints;
        if ($this->type!==null) $list['type'] = $this->type;
        if ($this->link!==null) $list['link'] = $this->link;
        if ($this->submissions!==array() && $this->submissions!==null) $list['submissions'] = $this->submissions;
        if ($this->bonus!==null) $list['bonus'] = $this->bonus;
        if ($this->attachments!==array() && $this->attachments!==null) $list['attachments'] = $this->attachments;
        return $list;
    }
}
?>