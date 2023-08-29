<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AHtml
 *
 * @author adam
 */
class AHtml extends CHtml{
    
	//does the samething as php function getallheaders. had to make this here tho cause that function doesnt exist in PHP versions < 5.4 and our dev server sucks and is using 5.3
	public static function getHttpRequestHeaders()
	{
		$headers = array();
		foreach($_SERVER as $key => $value) 
		{
			if (substr($key, 0, 5) <> 'HTTP_') {
				continue;
			}
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;	
		}
		return $headers;
	}
	
    /**
	 * Generates a check box list for a model attribute.
	 * The model attribute value is used as the selection.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * Note that a check box list allows multiple selection, like {@link listBox}.
	 * As a result, the corresponding POST value is an array. In case no selection
	 * is made, the corresponding POST value is an empty string.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the check box list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each checkbox input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each checkbox is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * check box input tag while "{label}" will be replaced by the corresponding check box label.</li>
	 * <li>separator: string, specifies the string that separates the generated check boxes.</li>
	 * <li>checkAll: string, specifies the label for the "check all" checkbox.
	 * If this option is specified, a 'check all' checkbox will be displayed. Clicking on
	 * this checkbox will cause all checkboxes checked or unchecked.</li>
	 * <li>checkAllLast: boolean, specifies whether the 'check all' checkbox should be
	 * displayed at the end of the checkbox list. If this option is not set (default)
	 * or is false, the 'check all' checkbox will be displayed at the beginning of
	 * the checkbox list.</li>
	 * <li>encode: boolean, specifies whether to encode HTML-encode tag attributes and values. Defaults to true.</li>
	 * </ul>
	 * Since 1.1.7, a special option named 'uncheckValue' is available. It can be used to set the value
	 * that will be returned when the checkbox is not checked. By default, this value is ''.
	 * Internally, a hidden field is rendered so when the checkbox is not checked, we can still
	 * obtain the value. If 'uncheckValue' is set to NULL, there will be no hidden field rendered.
	 * @return string the generated check box list
	 * @see checkBoxList
	 */
	public static function activeCheckBoxList($model,$attribute,$data,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		$selection=self::resolveValue($model,$attribute);
		if($model->hasErrors($attribute))
                    self::addErrorCss($htmlOptions);
		$name=$htmlOptions['name'];
		unset($htmlOptions['name']);
                
                $otherLabel = isset($htmlOptions['other']) ? $htmlOptions['other'] : '';

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
                    $uncheck=$htmlOptions['uncheckValue'];
                    unset($htmlOptions['uncheckValue']);
		}
		else
                    $uncheck='';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? self::hiddenField($name,$uncheck,$hiddenOptions) : '';
                
                $isOther = false;
                $other = null;

                foreach (explode(",", $selection) as $key => $value) {
                    
                    if(!in_array($value, $data) && !empty($value)){
                        $isOther = true;
                        $other = $value;
                    }
                }
                
                $html  = '<div id="'.$htmlOptions['id'].'" class="multiselect-fieldset-wrapper">';
                $html .= '<fieldset><legend><b>'.$model->getAttributeLabel($attribute).'</b></legend>';
		$html .= $hidden . self::checkBoxList($name,  explode(',', $selection),$data,$htmlOptions);
                
                
                if (isset($htmlOptions['other'])){
                    $id_other_check = get_class($model)."_".$attribute."_other_check";
                    $id_other_field = get_class($model)."_".$attribute."_other_field";

                    $htmlOptions['id'] = $id_other_check;
                    $html .= "<br>".self::checkBox($id_other_check, $other, $htmlOptions);
                    $html .= "&nbsp".$otherLabel;


                    $html .= "&nbsp&nbsp".self::textField(self::resolveName($model, $attribute)."[]", $other, array('id'=>$id_other_field, 'style'=>'display:'.($isOther ? 'inline' : 'none')));
                }
                $html .= '</fieldset></div>';
                
                $js=<<<EOD
$('#$id_other_check').click(function(){
    if($(this).attr("checked"))
        $('#$id_other_field').show().removeAttr('disabled');
    else 
        $('#$id_other_field').hide().prop('disabled', true);
});
EOD;
                $cs=Yii::app()->getClientScript();
                $cs->registerCoreScript('jquery');
                $cs->registerScript($id_other_check."_js",$js);
                
                return $html;
	}
        
        /**
	 * Generates a check box list.
	 * A check box list allows multiple selection, like {@link listBox}.
	 * As a result, the corresponding POST value is an array.
	 * @param string $name name of the check box list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param mixed $select selection of the check boxes. This can be either a string
	 * for single selection or an array for multiple selections.
	 * @param array $data value-label pairs used to generate the check box list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each checkbox input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each checkbox is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * check box input tag while "{label}" be replaced by the corresponding check box label.</li>
	 * <li>separator: string, specifies the string that separates the generated check boxes.</li>
	 * <li>checkAll: string, specifies the label for the "check all" checkbox.
	 * If this option is specified, a 'check all' checkbox will be displayed. Clicking on
	 * this checkbox will cause all checkboxes checked or unchecked.</li>
	 * <li>checkAllLast: boolean, specifies whether the 'check all' checkbox should be
	 * displayed at the end of the checkbox list. If this option is not set (default)
	 * or is false, the 'check all' checkbox will be displayed at the beginning of
	 * the checkbox list.</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list.</li>
	 * <li>container: string, specifies the checkboxes enclosing tag. Defaults to 'span'.
	 * If the value is an empty string, no enclosing tag will be generated</li>
	 * </ul>
	 * @return string the generated check box list
	 */
	public static function checkBoxList($name,$select,$data,$htmlOptions=array())
	{
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
		$container=isset($htmlOptions['container'])?$htmlOptions['container']:'span';
		$other=isset($htmlOptions['other'])?$htmlOptions['other']:'Other';
                 
                
		unset($htmlOptions['template'],$htmlOptions['separator'],$htmlOptions['container']);

		if(substr($name,-2)!=='[]')
			$name.='[]';

		if(isset($htmlOptions['checkAll']))
		{
			$checkAllLabel=$htmlOptions['checkAll'];
			$checkAllLast=isset($htmlOptions['checkAllLast']) && $htmlOptions['checkAllLast'];
		}
		unset($htmlOptions['checkAll'],$htmlOptions['checkAllLast']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		$items=array();
		$baseID=self::getIdByName($name);
		$id=0;
		$checkAll=true;

		foreach($data as $value=>$label)
		{
			$checked=!is_array($select) && !strcmp($value,$select) || is_array($select) && in_array($value,$select);
			$checkAll=$checkAll && $checked;
			$htmlOptions['value']=$value;
			$htmlOptions['id']=$baseID.'_'.$id++;
			$option=self::checkBox($name,$checked,$htmlOptions);
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}

		if(isset($checkAllLabel))
		{
			$htmlOptions['value']=1;
			$htmlOptions['id']=$id=$baseID.'_all';
			$option=self::checkBox($id,$checkAll,$htmlOptions);
			$item= strtr($template,array('{input}'=>$option,'{label}'=>$label));
			if($checkAllLast)
				$items[]=$item;
			else
				array_unshift($items,$item);
			$name=strtr($name,array('['=>'\\[',']'=>'\\]'));
                        
                        $id = $htmlOptions['id'];
                        
			$js=<<<EOD
$('#$id').click(function() {
	$("input[name='$name']").prop('checked', this.checked);
});
$("input[name='$name']").click(function() {
	$('#$id').prop('checked', !$("input[name='$name']:not(:checked)").length);
});
$('#$id').prop('checked', !$("input[name='$name']:not(:checked)").length);
EOD;
			$cs=Yii::app()->getClientScript();
			$cs->registerCoreScript('jquery');
			$cs->registerScript($id,$js);
		}
                

		if(empty($container))
			return implode($separator,$items);
		else
			return self::tag($container,array('id'=>$baseID),implode($separator,$items));
	}
        
        
        /**
	 * Generates a radio button list for a model attribute.
	 * The model attribute value is used as the selection.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the radio button list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each radio button input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each radio button is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
	 * <li>separator: string, specifies the string that separates the generated radio buttons. Defaults to new line (<br/>).</li>
	 * <li>encode: boolean, specifies whether to encode HTML-encode tag attributes and values. Defaults to true.</li>
	 * </ul>
	 * Since version 1.1.7, a special option named 'uncheckValue' is available that can be used to specify the value
	 * returned when the radio button is not checked. By default, this value is ''. Internally, a hidden field is
	 * rendered so that when the radio button is not checked, we can still obtain the posted uncheck value.
	 * If 'uncheckValue' is set as NULL, the hidden field will not be rendered.
	 * @return string the generated radio button list
	 * @see radioButtonList
	 */
	public static function activeRadioButtonList($model,$attribute,$data,$htmlOptions=array())
	{
		self::resolveNameID($model,$attribute,$htmlOptions);
		$selection=self::resolveValue($model,$attribute);
		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		$name=$htmlOptions['name'];
		unset($htmlOptions['name']);
                
                $otherLabel = isset($htmlOptions['other']) ? $htmlOptions['other'] : '';

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
                    $uncheck=$htmlOptions['uncheckValue'];
                    unset($htmlOptions['uncheckValue']);
		}
		else
                    $uncheck='';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>self::ID_PREFIX.$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? self::hiddenField($name,$uncheck,$hiddenOptions) : '';
                
                $isOther = false;

                if(!in_array($selection, $data) && !empty($data)){
                    $isOther = true;
                    $other = $selection;
                }
                
                
                $html  = '<div id="'.$htmlOptions['id'].'" class="singleselect-fieldset-wrapper">';
                $html .= '<fieldset><legend><b>'.$model->getAttributeLabel($attribute).'</b></legend>';
		$html .= $hidden . self::radioButtonList($name,$selection,$data,$htmlOptions);
                
                if (isset($htmlOptions['other'])){
                    $id_other_radio = get_class($model)."_".$attribute."_other_radio";
                    $id_other_field = get_class($model)."_".$attribute."_other_field";

                    $htmlOptions['id'] = $id_other_radio;
                    $html .= "<br>".self::radioButton($name, $other, $htmlOptions);
                    $html .= "&nbsp".$otherLabel;
                    $htmlOptions['id'] = $id_other_radio;
                    $html .= "&nbsp&nbsp".self::textField(self::resolveName($model, $attribute), $other, array('id'=>$id_other_field, 'style'=>'display:'.$isOther?'inline':'none'));
                }
                $html .= '</fieldset></div>';
                
                
                $js=<<<EOD
$("[name='$name']").click(function(){
    if($("[name='$name']:checked").attr("id") == '$id_other_radio')
        $('#$id_other_field').show().removeAttr('disabled');
    else 
        $('#$id_other_field').hide().prop('disabled', true);
});
EOD;
                $cs=Yii::app()->getClientScript();
                $cs->registerCoreScript('jquery');
                $cs->registerScript($id_other_radio."_js",$js);
                
                return $html;
	}
        
        /**
	 * Generates a radio button list.
	 * A radio button list is like a {@link checkBoxList check box list}, except that
	 * it only allows single selection.
	 * @param string $name name of the radio button list. You can use this name to retrieve
	 * the selected value(s) once the form is submitted.
	 * @param string $select selection of the radio buttons.
	 * @param array $data value-label pairs used to generate the radio button list.
	 * Note, the values will be automatically HTML-encoded, while the labels will not.
	 * @param array $htmlOptions addtional HTML options. The options will be applied to
	 * each radio button input. The following special options are recognized:
	 * <ul>
	 * <li>template: string, specifies how each radio button is rendered. Defaults
	 * to "{input} {label}", where "{input}" will be replaced by the generated
	 * radio button input tag while "{label}" will be replaced by the corresponding radio button label.</li>
	 * <li>separator: string, specifies the string that separates the generated radio buttons. Defaults to new line (<br/>).</li>
	 * <li>labelOptions: array, specifies the additional HTML attributes to be rendered
	 * for every label tag in the list.</li>
	 * <li>container: string, specifies the radio buttons enclosing tag. Defaults to 'span'.
	 * If the value is an empty string, no enclosing tag will be generated</li>
	 * </ul>
	 * @return string the generated radio button list
	 */
	public static function radioButtonList($name,$select,$data,$htmlOptions=array())
	{
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
		$container=isset($htmlOptions['container'])?$htmlOptions['container']:'span';
		unset($htmlOptions['template'],$htmlOptions['separator'],$htmlOptions['container']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		$items=array();
		$baseID=self::getIdByName($name);
		$id=0;
		foreach($data as $value=>$label)
		{
			$checked=!strcmp($value,$select);
			$htmlOptions['value']=$value;
			$htmlOptions['id']=$baseID.'_'.$id++;
			$option=self::radioButton($name,$checked,$htmlOptions);
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}
		if(empty($container))
			return implode($separator,$items);
		else
			return self::tag($container,array('id'=>$baseID),implode($separator,$items));
	}
}

?>
