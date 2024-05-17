<tr>
    <td>
        <select name="deliveryfrom_services[{{newid}}]" data-attribute="deliveryfrom_service" autocomplete="off">
            <?php foreach($available_services as $service_id => $service_label): ?>
                <option value="<?php echo esc_attr($service_id); ?>"><?php echo esc_html($service_label); ?></option>
            <?php endforeach; ?>
        </select>
    </td>

    <td>
        <input type="text" value="" placeholder="*" name="deliveryfrom_services_labels[{{newid}}]" data-attribute="deliveryfrom_service" class="ui-autocomplete-input" autocomplete="off">
    </td>
</tr>