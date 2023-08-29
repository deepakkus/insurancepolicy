<?php

Yii::app()->clientScript->registerScriptFile('/js/jquery.imgareaselect.min.js');
Yii::app()->clientScript->registerCssFile('/css/imgareaselect.css');
Yii::app()->clientScript->registerCssFile('/css/file/edit.css');

?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">

            <div class="figure-wrapper">
                <figure class="image-container target">
                    <div class="image-edit-container">

                        <!-- Toolbar -->
                        <div class="image-edit-toolbar">
                            <div class="image-edit-button-group">
                                <button type="button" class="image-edit-button image-edit-button-danger" data-toggle="tooltip" data-placement="bottom" title="Reset Image" id="reset">
                                    <svg class="image-edit-icon">
                                        <use xlink:href="#close"></use>
                                    </svg>
                                </button>
                            </div>
                            <div class="image-edit-button-group">
                                <button type="button" class="image-edit-button" data-toggle="tooltip" data-placement="bottom" title="Rotate Left" id="rotate-left">
                                    <svg class="image-edit-icon">
                                        <use xlink:href="#rotate-left-svg"></use>
                                    </svg>
                                </button>
                                <button type="button" class="image-edit-button" data-toggle="tooltip" data-placement="bottom"  title="Rotate Right" id="rotate-right">
                                    <svg class="image-edit-icon">
                                        <use xlink:href="#rotate-right-svg"></use>
                                    </svg>
                                </button>
                            </div>
                            <div class="image-edit-button-group">
                                <button type="button" class="image-edit-button image-edit-button-active" data-toggle="tooltip" data-placement="bottom"  title="Crop"  id="crop">
                                    <svg class="image-edit-icon">
                                        <use xlink:href="#crop-svg"></use>
                                    </svg>
                                </button>
                            </div>
                            <div class="image-edit-button-group" id="save-button">
                                <button type="button" class="image-edit-button image-edit-button-success" data-toggle="tooltip" data-placement="bottom"  title="Save Crop" id="save">
                                    <svg class="image-edit-icon">
                                        <use xlink:href="#save-svg"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Image Editor -->
                        <div style="width: 600px; height: 600px;">
                            <img id="image" src="<?php echo $imageEditTmpFilePath; ?>" alt="image-editor" title="image-editor" />
                        </div>
                    </div>
                    <figcaption class="image-meta">
                        <p>Image Name: <strong><?php echo $imageEditFileName; ?></strong></p>
                    </figcaption>
                </figure>
                <div>
                    <input class="submit marginTop20" type="button" value="Update Permanent Photo" />
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SVG templates -->
<div id="image-edit-icons" style="height: 0px; width: 0px; position: absolute; visibility: hidden;">
    <svg xmlns="http://www.w3.org/2000/svg">
        <symbol id="close" viewBox="0 0 24 24">
            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
            <path d="M0 0h24v24H0z" fill="none" />
        </symbol>
        <symbol id="rotate-left-svg" viewbox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"></path>
            <path d="M7.11 8.53L5.7 7.11C4.8 8.27 4.24 9.61 4.07 11h2.02c.14-.87.49-1.72 1.02-2.47zM6.09 13H4.07c.17 1.39.72 2.73 1.62 3.89l1.41-1.42c-.52-.75-.87-1.59-1.01-2.47zm1.01 5.32c1.16.9 2.51 1.44 3.9 1.61V17.9c-.87-.15-1.71-.49-2.46-1.03L7.1 18.32zM13 4.07V1L8.45 5.55 13 10V6.09c2.84.48 5 2.94 5 5.91s-2.16 5.43-5 5.91v2.02c3.95-.49 7-3.85 7-7.93s-3.05-7.44-7-7.93z"></path>
        </symbol>
        <symbol id="rotate-right-svg" viewbox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"></path>
            <path d="M15.55 5.55L11 1v3.07C7.06 4.56 4 7.92 4 12s3.05 7.44 7 7.93v-2.02c-2.84-.48-5-2.94-5-5.91s2.16-5.43 5-5.91V10l4.55-4.45zM19.93 11c-.17-1.39-.72-2.73-1.62-3.89l-1.42 1.42c.54.75.88 1.6 1.02 2.47h2.02zM13 17.9v2.02c1.39-.17 2.74-.71 3.9-1.61l-1.44-1.44c-.75.54-1.59.89-2.46 1.03zm3.89-2.42l1.42 1.41c.9-1.16 1.45-2.5 1.62-3.89h-2.02c-.14.87-.48 1.72-1.02 2.48z"></path>
        </symbol>
        <symbol id="crop-svg" viewbox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"></path>
            <path d="M17 15h2V7c0-1.1-.9-2-2-2H9v2h8v8zM7 17V1H5v4H1v2h4v10c0 1.1.9 2 2 2h10v4h2v-4h4v-2H7z"></path>
        </symbol>
        <symbol id="save-svg" viewbox="0 0 24 24">
            <path d="M0 0h24v24H0z" fill="none"></path>
            <path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"></path>
        </symbol>
    </svg>
</div>

<script type="text/javascript">

    $(function() {

        var PhotoEdit = {
            init: function() {

                // Declare variables
                this.image = $('#image');
                this.resetButton = $('#reset');
                this.rotateLeftButton = $('#rotate-left');
                this.rotateRightButton = $('#rotate-right');
                this.cropButton = $('#crop');
                this.saveButton = $('#save-button');
                this.submitButton = $('.submit');
                this.imageSelect = this.image.imgAreaSelect({ instance: true });

                // Setup
                this.imageSelect.setOptions({ disable: true, hide: true, show: false, maxWidth: 600, maxHeight: 600, handles: true });
                this.imageSelect.update();
                this.saveButton.hide();
                this.initListeners();
            },
            initListeners: function() {

                this.resetButton.click(function() {
                    this.ajaxUpdate({ reset: 1 });
                }.bind(this));

                this.rotateLeftButton.click(function() {
                    this.disableCrop()
                    this.ajaxUpdate({ rotate: -90 });
                }.bind(this));

                this.rotateRightButton.click(function() {
                    this.disableCrop()
                    this.ajaxUpdate({ rotate: 90 });
                }.bind(this));

                this.cropButton.click(function() {
                    var options = this.imageSelect.getOptions();
                    if (options.hide === true) {
                        this.cropButton.prop('title', 'Remove Crop').tooltip('destroy').tooltip('show');
                        var x2 = this.image.width() - 20,
                            y2 = this.image.height() - 20;
                        this.imageSelect.setOptions({ disable: false, hide: false, show: true });
                        this.imageSelect.setSelection(20, 20, x2, y2);
                        this.imageSelect.update();
                        this.saveButton.show();
                    } else {
                        this.cropButton.prop('title', 'Crop').tooltip('destroy').tooltip('show');
                        this.disableCrop()
                    }
                }.bind(this));

                this.saveButton.click(function() {
                    var selection = this.imageSelect.getSelection();
                    this.disableCrop();
                    this.ajaxUpdate({
                        crop: {
                            imageWidth: this.image.width(),
                            imageHeight: this.image.height(),
                            selectionWidth: selection.width,
                            selectionHeight: selection.height,
                            x: selection.x1,
                            y: selection.y1
                        }
                    });
                }.bind(this));

                this.submitButton.click(function() {
                    this.ajaxUpdate({ save: 1 });
                }.bind(this));
            },
            disableCrop: function() {
                this.imageSelect.setOptions({ disable: true, hide: true, show: false });
                this.imageSelect.update();
                this.saveButton.hide();
            },
            ajaxUpdate: function(postObject) {
                var options = {
                    image: postObject,
                    imageEditFileName: <?php echo json_encode($imageEditFileName); ?>,
                    imageEditTmpFilePath: <?php echo json_encode($imageEditTmpFilePath); ?>,
                    imageEditId: <?php echo json_encode($imageEditId); ?>,
                    imageEditFilePath: <?php echo json_encode($imageEditFilePath); ?>
                };

                $.post('<?php echo $this->createUrl($this->route); ?>', options, function(data) {
                    if (data.hasOwnProperty('error') && data.error === false) {
                        if (postObject.hasOwnProperty('reset')) {
                            window.location.reload();
                        } else if (postObject.hasOwnProperty('save')) {
                            window.location.href = '<?php echo $returnUrl; ?>';
                        } else {
                            var src = this.image.prop('src');
                            if (src.indexOf('?reloadID=') !== -1) {
                                src = src.substr(0, src.indexOf('?reloadID='));
                            }
                            this.image.prop('src', src + '?reloadID=' + new Date().getTime().toString());
                        }
                    } else {
                        console.log('Something went wrong!');
                    }
                }.bind(this), 'json').error(function(error) {
                    console.log('Error: ' + error.responseText);
                });
            }
        };

        PhotoEdit.init();
    });

</script>
