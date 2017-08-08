<?php

namespace FormBuilderBundle\Mail;

use \Pimcore\Mail;
use Symfony\Component\Templating\EngineInterface;

class FormBuilderMail extends Mail
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var array
     */
    private $ignoreFields = [];

    /**
     * @param EngineInterface $templating
     */
    public function setTemplateEngine(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param array $fields
     */
    public function setIgnoreFields($fields = [])
    {
        $this->ignoreFields = $fields;
    }

    /**
     * @param $data
     * @param $disableDefaultMailBody
     */
    public function setMailPlaceholders($data, $disableDefaultMailBody)
    {
        //allow access to all form placeholders
        foreach ($data as $label => $field) {
            //ignore fields!
            if (in_array($label, $this->ignoreFields) || empty($field['value'])) {
                continue;
            }

            $this->setParam($label, $this->getSingleRenderedValue($field['value']));
        }

        if ($disableDefaultMailBody === FALSE) {
            $this->setParam('body', $this->parseHtml($data));
        }
    }

    /**
     * @param array $data [label,value]
     *
     * @return string
     */
    private function parseHtml($data)
    {
        $renderData = [];

        foreach ($data as $label => $fieldData) {
            //ignore fields!
            if (in_array($label, $this->ignoreFields)) {
                continue;
            }

            //@todo: implement form translations.
            //$data = $this->getSingleRenderedValue($fieldData['value']);
            $data = $fieldData;

            if (empty($data)) {
                continue;
            }

            $renderData[] = ['label' => $label, 'value' => $fieldData];
        }

        $html = $this->templating->render('@FormBuilder/Form/Partials/defaultFormData.html.twig', ['data' => $renderData]);

        return $html;
    }

    /**
     * Transform placeholders into values.
     *
     * @param string $subject
     * @param array  $data
     *
     */
    public function parseSubject($subject = '', $data = [])
    {
        $realSubject = $subject;

        preg_match_all("/\%(.+?)\%/", $realSubject, $matches);

        if (isset($matches[1]) && count($matches[1]) > 0) {
            foreach ($matches[1] as $key => $inputValue) {
                foreach ($data as $formFieldName => $formFieldValue) {
                    if (empty($formFieldValue['value'])) {
                        continue;
                    }

                    if ($formFieldName == $inputValue) {
                        $realSubject = str_replace($matches[0][$key], $this->getSingleRenderedValue($formFieldValue['value'], ', '), $realSubject);
                    }
                }

                //replace with '' if not found.
                $realSubject = str_replace($matches[0][$key], '', $realSubject);
            }
        }

        $this->setSubject($realSubject);
    }

    /**
     * @param        $field
     * @param string $separator
     *
     * @return string
     */
    private function getSingleRenderedValue($field, $separator = '<br>')
    {
        $data = '';
        if (is_array($field)) {
            foreach ($field as $f) {
                $data .= $this->parseStringForOutput($f) . $separator;
            }
        } else {
            $data = $this->parseStringForOutput($field);
        }

        return $data;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function parseStringForOutput($string = '')
    {
        if (strstr($string, "\n")) {
            return nl2br($string);
        }

        return $string;
    }

}