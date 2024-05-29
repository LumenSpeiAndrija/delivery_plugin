<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
//show services

?>
<table class="deliveryfrom_services wc_input_table widefat">
    <thead>
    <tr>
        <th width="20%"><?php esc_html_e('Service', 'deliveryfrom'); ?></th>
        <th><?php esc_html_e('Label (for admin)', 'deliveryfrom'); ?></th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th colspan="9">
            <button type="button" id="deliveryfrom_add_service" href="#"  class="button plus insert"><?php esc_html_e('Add service', 'deliveryfrom'); ?></button>
            <button type="button" id="deliveryfrom_delete_service" href="#" class="button minus"><?php esc_html_e('Remove selected services', 'deliveryfrom'); ?></button>
        </th>
    </tr>
    </tfoot>
    <tbody id="deliveryfrom_services">
    <?php if (!empty($methods)): ?>
        <?php foreach ($methods as $method): ?>
            <tr>
                <td>
                    <select name="deliveryfrom_services[<?php echo esc_attr($method['ID']); ?>]" data-attribute="deliveryfrom_service" autocomplete="off">
                        <?php foreach ($available_services as $service_id => $service_label): ?>
                            <option value="<?php echo esc_attr($service_id); ?>" <?php selected($service_id, $method['service'], true); ?>><?php echo esc_html($service_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="text" value="<?php echo esc_attr($method['name']); ?>" placeholder="*" name="deliveryfrom_services_labels[<?php echo esc_attr($method['ID']); ?>]" data-attribute="deliveryfrom_service" data-id="<?php echo esc_attr($method['ID']); ?>" class="ui-autocomplete-input" autocomplete="off">
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>