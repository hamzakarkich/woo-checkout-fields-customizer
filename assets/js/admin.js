jQuery(document).ready(function($) {
    var fieldCounter = $('.wc-checkout-fields-customizer-field').length;

    // Make the table sortable
    $('#wc-checkout-fields-customizer-table tbody').sortable({
        items: 'tr',
        cursor: 'move',
        axis: 'y',
        handle: 'td',
        scrollSensitivity: 40,
        helper: function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        },
        start: function(event, ui) {
            ui.item.css('background-color', '#f6f6f6');
        },
        stop: function(event, ui) {
            ui.item.removeAttr('style');
            updateFieldPriorities();
        }
    });

    // Add new field
    $('#add-new-field').on('click', function() {
        fieldCounter++;
        var newFieldHtml = `
            <tr class="wc-checkout-fields-customizer-field">
                <td>
                    <input type="checkbox" name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][enabled]" checked>
                </td>
                <td>
                    <select name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][type]" class="field-type">
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="select">Select</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][label]" value="Custom Field ${fieldCounter}">
                </td>
                <td>
                    <input type="text" name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][placeholder]" value="Enter value">
                </td>
                <td>
                    <input type="checkbox" name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][required]">
                </td>
                <td>
                    <input type="number" name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][priority]" value="${fieldCounter * 10}" min="0" step="1">
                </td>
                <td>
                    <input type="text" name="wc_checkout_fields_customizer[custom_field_${fieldCounter}][options]" class="field-options" style="display:none;" placeholder="Option 1, Option 2, Option 3">
                </td>
                <td>
                    <button type="button" class="button remove-field">Remove</button>
                </td>
            </tr>
        `;
        $('#wc-checkout-fields-customizer-table tbody').append(newFieldHtml);
        updateFieldPriorities();
    });

    // Remove field
    $('#wc-checkout-fields-customizer-table').on('click', '.remove-field', function() {
        $(this).closest('tr').remove();
        updateFieldPriorities();
    });

    // Show/hide options field based on field type
    $('#wc-checkout-fields-customizer-table').on('change', '.field-type', function() {
        var $optionsField = $(this).closest('tr').find('.field-options');
        if ($(this).val() === 'select' || $(this).val() === 'radio') {
            $optionsField.show();
        } else {
            $optionsField.hide();
        }
    });

    function updateFieldPriorities() {
        $('#wc-checkout-fields-customizer-table tbody tr').each(function(index) {
            $(this).find('input[name$="[priority]"]').val((index + 1) * 10);
        });
    }
});