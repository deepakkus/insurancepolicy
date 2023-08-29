<?php

/* @var $note EngShiftTicketNotes */

$form = $this->beginWidget('WDSActiveForm', array(
    'id' => 'shift-ticket-modal-form',
    'enableAjaxValidation' => true,
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    ),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'afterValidate' => new CJavaScriptExpression('function(form, data, hasError) {
            if (!hasError) {
                $.ajax({
                    "type": "POST",
                    "url": form.attr("action"),
                    "data": form.serialize(),
                    "success": function(data) {
                        var data = JSON.parse(data);
                        if (data.success === true) {
                            // Close jui dialog
                            $("#modal-container").dialog("close");
                            // Update form
                            $("#modal-container").find("#modal-content").html("");
                            // Reload Activities list
                            $.get("' . $this->createUrl('engShiftTicketNotes/notes', array('shift_ticket_id' => $note->eng_shift_ticket_id)) . '", function(data) {
                                $("#review-notes-container").html(data);
                                $(".alert-notes").fadeIn().delay(2000).fadeOut();
                            });
                        } else {
                            alert("Something went wrong!");
                            console.log("Shift ticket note saving error: " + data.error);
                        }
                    }
                });
                return false;
            }
            return false;
        }')

    )
));

echo $form->textAreaRow($note, 'notes', array(
    'rows' => 5,
    'maxlength' => '300',
    'style' => 'width: 300px;'
));

echo $form->generateFormJavscriptValidation();

$this->endWidget();
