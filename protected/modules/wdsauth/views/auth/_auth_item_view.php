<div style="padding: 19px; margin-bottom: 20px; border: 1px solid #e3e3e3; border-radius: 4px;">
    <div class="row-fluid">
        <div class="span12">
            <?php

            echo '<h5>' . ucfirst($typeName) . ' Name: <span class="lead">' . $authItem->getName() . '</span></h5>';

            $this->widget('zii.widgets.CDetailView', array(
                'data' => $authItem,
                'htmlOptions' => array(
                    'class' => 'table'
                ),
                'itemTemplate' => '<tr><th>{label}</th><td>{value}</td></tr>',
                'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
                'attributes' => array(
                    'description',
                    'bizRule',
                    'data'
                )
            ));

            ?>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <p>Note: These are direct assignments, not recursive.</p>
            <?php

            echo '<b>' . ucfirst($typeName) . ' Children</b>';

            $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'authRoles',
                'dataProvider' => $childrenDataProvider,
                'summaryCssClass' => 'hidden',
                'emptyText' => 'No auth items',
                'enableSorting' => false,
                'columns' => array(
                    'name',
                    array(
                        'name' => 'type',
                        'value' => '$this->grid->controller->module->authManager->getAuthItemTypeName($data->type)'
                    )
                )
            ));

            echo '<b>' . ucfirst($typeName) . ' Parents</b>';

            $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'authRoles',
                'dataProvider' => $parentsDataProvider,
                'summaryCssClass' => 'hidden',
                'emptyText' => 'No auth items',
                'enableSorting' => false,
                'columns' => array(
                    'name',
                    array(
                        'name' => 'type',
                        'value' => '$this->grid->controller->module->authManager->getAuthItemTypeName($data->type)'
                    )
                )
            ));

            echo '<b>Users assigned to ' . $typeName . '</b>';

            $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'authRoles',
                'dataProvider' => $usersDataProvider,
                'summaryCssClass' => 'hidden',
                'emptyText' => 'No assigned users',
                'enableSorting' => false,
                'columns' => array(
                    'name',
                    'username'
                )
            ));

            ?>
        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <?php echo CHtml::button('Update', array('style' => 'background-color: #31b0d5; color: white;', 'id' => $typeName . '-update')); ?>
            <?php echo CHtml::button('Delete', array('style' => 'background-color: #d9534f; color: white;', 'id' => $typeName . '-delete')); ?>
        </div>
    </div>

    <script type="text/javascript">

        var $loading = $(".auth-item-view-loading");

        $('#<?php echo $typeName; ?>-update').on('click', function() {
            $loading.addClass("active");
            $.get('<?php echo $this->createUrl('auth/updateAuthItem'); ?>', {
                type: <?php echo $authItem->getType(); ?>,
                name: '<?php echo $authItem->getName(); ?>'
            }, function(html) {
                $loading.removeClass("active");
                $("#<?php echo $typeName; ?>-container").html(html)
            }).error(function(jqXHR) {
                $loading.removeClass("active");
                console.log(jqXHR.responseText);
            });
            return false;
        });

        $('#<?php echo $typeName; ?>-delete').on('click', function() {
            if (!confirm('Are you sure you want to delete this item?')) {
                return false;
            }

            var form = document.createElement('form');
            form.setAttribute('method', 'post');
            form.setAttribute('action', '<?php echo $this->createUrl('auth/deleteAuthItem'); ?>');

            var fieldName = document.createElement('input');
            fieldName.setAttribute('type', 'hidden');
            fieldName.setAttribute('name', 'name');
            fieldName.setAttribute('value', '<?php echo $authItem->getName(); ?>');

            var fieldType = document.createElement('input');
            fieldType.setAttribute('type', 'hidden');
            fieldType.setAttribute('name', 'type');
            fieldType.setAttribute('value', '<?php echo $authItem->getType(); ?>');

            form.appendChild(fieldName);
            form.appendChild(fieldType);
            document.body.appendChild(form);
            form.submit();

            return false;
        });

    </script>
</div>
