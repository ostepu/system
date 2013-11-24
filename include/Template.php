<?php
include_once 'Helpers.php';

/**
* Template class.
*
* Applies templates to format data.
*/
class Template
{
    protected $templates;

    protected $content;

    /**
     * Apply a template to some data.
     *
     * @param array $template The template that should be applied.
     * @param mixed $data The data the template should be applied to.
     */
    protected function applyTemplate(array $template, $data)
    {   
        if (!is_array($data)) {
            // data is not an array so it can be converted to string by the
            // interpreter, we can simply return it
            return $data;
        }

        if (!isset($template['_template'])) {
            if (isset($template['_templatefile'])) {
                // check if a template file is specified
                $templateString = file_get_contents($template['_templatefile']);

                if ($templateString == false) {
                    // the template file could not be opened
                    die("[applyTemplate] file could not be opened: " .
                        $template['_templatefile']);
                }

            } else {
                // the template does not specify how to format the data
                // abort.
                die("[applyTemplate] The attribute '_template' is required!\n" .
                    "template: {$template}\ndata: {$data}");
            }
        } else {
            $templateString = $template['_template'];
        }

        /**
         * @todo find a way to determine which templates templateString depends
         * on and apply only those.
         */
        foreach ($data as $key => $value) {
            // check if the element has a template and apply it
            if (isset($this->templates[$key])) {

                $template = $this->templates[$key];

                if (is_array($value)) {
                    // the element is an array
                    if (!is_assoc($value)) {
                        // it is not associative, apply the template to each
                        // of its elements
                        $stringValue = $this->applyEach($template, $value);
                    } else {
                        // it is asociative, apply the template to it
                        $stringValue = $this->applyTemplate($template, $value);
                    }
                } else {
                    // the element is not an array, but has a template, replace
                    // its placeholder in its template 
                    $template2 = $this->templates[$key];
                    $stringValue = str_replace("%{$key}%",
                                               $value,
                                               $template2['_template']);
                }

                // remplace the placeholder by the formatted string
                $templateString = str_replace("%{$key}%",
                                              $stringValue,
                                              $templateString);
            } else {
                // the element does not have a template

                // remplace the placeholder by the elements string
                // representation
                $templateString = str_replace("%{$key}%",
                                              $value,
                                              $templateString);
            }
        }


        return $templateString;
    }


    /**
     * Apply a template to all elements of an array
     *
     * @param array $template The template that should be applied.
     * @param array $data An array of elements the template should be applied
     * to.
     */
    protected function applyEach(array $template, array $data)
    {   
        // an array of elements, formatted as string
        $strings = array();

        if (isset($template['_join'])) {
            // the template specifies how to join elements from this array
            $joinString = $template['_join'];
        } else {
            // the template does not specify how to join elements from this
            // array
            $joinString = "";
        }

        // apply the template to each element in the array
        foreach ($data as $key => $value) {
            // append the formatted element to others
            $strings[] = $this->applyTemplate($template, $value);
        }

        // join all the elements in a string
        return implode($joinString , $strings);
    }

    /**
     * Construct a new template.
     *
     * @param array $templates An associative array, that describes a template.
     */
    public function __construct(array $templates)
    {
        if (!isset($templates['_template'])) {
            // check if a template string is specified

            if (!isset($templates['_templatefile'])) {
                // check if a template file is specified

                die("[__construct] one of the attributes '_template' or"
                    . "'_templatefile' is required. templates: "
                    . print_r($templates, true));
            } 

        }

        $this->templates = $templates;
    }

    /**
     * Construct a new template.
     *
     * @param array $fileName The name of a file in which a template is stored
     */
    public function WithTemplateFile($fileName)
    {
        // get the contents of the file
        $fileContents = file_get_contents($fileName);

        if ($fileContents == false) {
            die("Could not open file: {$fileName}");
        }

        // parse the contents of the file as JSON object
        $template = json_decode($fileContents, true);

        if ($template == false || !is_array($template)) {
            die("Invalid JSON in file: {$fileName}");
        }

        return new Template($template);
    }

    /**
     * Bind content to the template.
     *
     * This replaces the placeholders in the template with the corresponding
     * values from the data array.
     *
     * @param array $content An associative array that contains the content for
     * the template.
     */
    public function bind(array $data)
    {
        $this->content = $this->applyTemplate($this->templates, $data);

        if (isset($this->templates['_import'])) {

            //this file requires some static file includes
            foreach ($this->templates['_import'] as $key => $fileName) {

                // load the content of the file called $fileName
                $importedText = file_get_contents($fileName);

                if ($importedText == false) {
                    // the file could not be opened
                    die("Could not open file: {$fileName}");
                }

                // replace all occurences of key with the contents of the file
                // with name $fileName
                $this->content = str_replace("%{$key}%",
                                             $importedText,
                                             $this->content);
            }
        }
    }

    /**
     * Show the template to the user.
     */
    public function show()
    {
        echo $this->content . "\n";
    }

    /**
     * Return the the template as a string.
     */
    public function __toString()
    {
        return $this->content . "\n";
    }
}
?>