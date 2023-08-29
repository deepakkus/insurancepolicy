<?php

Yii::import('ext.bootstrap.widgets.TbActiveForm');

/**
 * Subclass of CActiveForm
 */
class WDSActiveForm extends TbActiveForm
{
    /**
     * Generates javascript form validation.
     * Intended for use when form is inserted asynchronously into the DOM
     *      (JS won't be registered in view because view was already loaded)
     *
     * This function should be called at the end of the form, before WDSActiveForm::end()
     * Ex:
     *
     *      .... form content ....
     *
     *      <?php echo $form->generateFormJavscriptValidation(); ?>
     *
     *
     * @return string
     */
    public function generateFormJavscriptValidation()
    {
		$options = $this->clientOptions;
		if (isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
			$options['validationUrl'] = CHtml::normalizeUrl($this->clientOptions['validationUrl']);

		$options['attributes'] = array_values($this->attributes);

		if ($this->summaryID !== null)
			$options['summaryID'] = $this->summaryID;

		if ($this->focus !== null)
			$options['focus'] = $this->focus;

		if (!empty(CHtml::$errorCss))
			$options['errorCss'] = CHtml::$errorCss;

		$options = CJavaScript::encode($options);
		$id = $this->getId();
        $script = CHtml::script("jQuery('#$id').yiiactiveform($options);");
        return $script;
    }
}
