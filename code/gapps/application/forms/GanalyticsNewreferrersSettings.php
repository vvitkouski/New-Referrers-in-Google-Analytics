<?php

class Application_Form_GanalyticsNewreferrersSettings extends Zend_Form
{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setAttrib('id', 'ga-newreferrers-settings');
 
        $this->addElement('text', 'min_traffic', array(
            'label'      => 'Minimum Traffic:',
            'required'   => true,
            'value'      => '10',
            'min'        => '0',
            'class'      => 'required digits',
            'filters'    => array('StringTrim'),
            'validators' => array()
        ));

        $this->addElement('text', 'download_period', array(
            'label'      => 'Download Period (days):',
            'required'   => true,
            'value'      => '30',
            'min'        => '1',
            'class'      => 'required digits',
            'filters'    => array('StringTrim'),
            'validators' => array()
        ));

        $this->addElement('text', 'compare_period', array(
            'label'      => 'Compare Period (days):',
            'required'   => true,
            'value'      => '180',
            'min'        => '1',
            'class'      => 'required digits',
            'filters'    => array('StringTrim'),
            'validators' => array()
        ));
 
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Create Report',
        ));
    }


}

