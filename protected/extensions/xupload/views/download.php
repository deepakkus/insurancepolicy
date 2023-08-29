<!-- The template to display files available for download -->
<?php $old_filename_id = uniqid('file-rename-old-filename');
      $new_filename_id = uniqid('file-rename-new-filename');
?>


<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">
                {% if (file.thumbnail_url) { %} 
<!--                 <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}">-->
                    <img class="photo" src="{%=file.thumbnail_url%}"><!--</a>-->
                {% } %}
            </td>
            
            <td class="name">
                <div class="filename-rename">
                    <div id="<?php uniqid('gallery-image-rename') ?>">
                        <input class="upload-gallery-new-filename" id="<?php echo $old_filename_id ?>" type="text" value="{%=file.name.substr(0, file.name.lastIndexOf('.'))%}" name="new_filename">
                        <br>
                        <input class="upload-gallery-old-filename" id="<?php echo $new_filename_id ?>" type="hidden" value="{%=file.name%}" name="old_filename">
                        <input type="hidden" value="{%=file.assessment_id%}" name="assessment_id">
                        <input class="upload-gallery-submit-button" style="display:none" type="button" value="Rename">
                        <input class="upload-gallery-cancel-button" style="display:none" type="button" value="Cancel">
                    </div>
                </div>
<!--                <a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">{%=file.name%}</a><br><br>-->
                <span>{%=o.formatFileSize(file.size)%}</span>
            </td>

        {% } %}
        <td></td>
        <td class="delete">
            <button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
                <i class="icon-trash icon-white"></i>
                <span>{%=locale.fileupload.destroy%}</span>
            </button>
        </td>
    </tr>

{% } %}
</script>
