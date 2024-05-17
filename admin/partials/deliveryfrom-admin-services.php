<?php

//show services

?>
<table class="deliveryfrom_services wc_input_table widefat">
    <thead>
        <tr>
            <th width="20%"><?php _e('Service', 'deliveryfrom'); ?></th>
            <th><?php _e('Label (for admin)', 'deliveryfrom'); ?></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th colspan="9">
                <button type="button" id="deliveryfrom_add_service" href="#"  class="button plus insert"><?php _e('Add service', 'deliveryfrom'); ?></button>
                <button type="button" id="deliveryfrom_delete_service" href="#" class="button minus"><?php _e('Remove selected services', 'deliveryfrom'); ?></button>
            </th>
        </tr>
    </tfoot>
    <tbody id="deliveryfrom_services">
    <?php if(sizeof($methods) > 0): ?>
        <?php foreach($methods as $method): ?>
        <tr>
            <td>
                <select name="deliveryfrom_services[<?php esc_attr_e($method['ID']); ?>]" data-attribute="deliveryfrom_service" autocomplete="off">
                    <?php foreach($available_services as $service_id => $service_label): ?>
                        <option value="<?php echo esc_attr($service_id); ?>" <?php selected($service_id, $method['service'], true); ?>><?php echo esc_html($service_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <input type="text" value="<?php esc_attr_e($method['name']); ?>" placeholder="*" name="deliveryfrom_services_labels[<?php esc_attr_e($method['ID']); ?>]" data-attribute="deliveryfrom_service" data-id="<?php esc_attr_e($method['ID']); ?>" class="ui-autocomplete-input" autocomplete="off">
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
    
</table>