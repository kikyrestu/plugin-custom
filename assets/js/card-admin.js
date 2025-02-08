jQuery(document).ready(function($) {
    // Debug: Cek apakah script loaded
    console.log('Card admin script loaded');

    // Handle card duplication
    $(document).on('click', '.duplicate-card', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const cardId = button.data('id');
        const nonce = button.data('nonce');
        
        // Disable button
        button.addClass('updating-message');
        
        // Send AJAX request
        $.ajax({
            url: cardAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'duplicate_card',
                card_id: cardId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to edit page
                    window.location.href = response.data.redirect;
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error occurred while duplicating card');
            },
            complete: function() {
                button.removeClass('updating-message');
            }
        });
    });

    // Media uploader
    var mediaUploader;
    
    $('#upload_card_image_button').click(function(e) {
        e.preventDefault();

        // If the media uploader already exists, open it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Choose Card Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // When an image is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#card_image_id').val(attachment.id);
            
            // Update preview
            var previewHtml = '<img src="' + attachment.url + '" style="max-width: 200px; height: auto;">';
            $('.card-image-preview').html(previewHtml);
            
            // Update button text
            $('#upload_card_image_button').text('Change Image');
            
            // Show remove button
            if (!$('#remove_card_image_button').length) {
                $('#upload_card_image_button').after('<button type="button" class="button" id="remove_card_image_button">Remove Image</button>');
            }
        });

        // Open the media uploader
        mediaUploader.open();
    });

    // Remove image button
    $(document).on('click', '#remove_card_image_button', function(e) {
        e.preventDefault();
        
        // Clear image ID
        $('#card_image_id').val('');
        
        // Clear preview
        $('.card-image-preview').empty();
        
        // Update button text
        $('#upload_card_image_button').text('Upload Image');
        
        // Remove the remove button
        $(this).remove();
    });

    // Listen untuk event elementor delete
    elementor.channels.data.on('element:destroy', function(model) {
        if (model.attributes.widgetType === 'custom_card') {
            var cardId = model.container.$el.find('.custom-card').data('card-id');
            
            if (cardId) {
                // Kirim AJAX request untuk hapus card
                $.ajax({
                    url: cardAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'delete_card',
                        card_id: cardId,
                        nonce: cardAdmin.nonce
                    },
                    success: function(response) {
                        console.log('Card deleted:', response);
                    }
                });
            }
        }
    });

    // Handle save card button
    elementor.channels.editor.on('saveCard', function(view) {
        console.log('Save button clicked');
        
        // Dapatkan settings dari widget
        var settings = view.container.settings.attributes;
        console.log('Settings:', settings);

        // Kirim data ke server via AJAX
        $.ajax({
            url: cardAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'save_card_data',
                nonce: cardAdmin.nonce,
                card_id: settings.card_id,
                title: settings.card_title,
                subtitle: settings.card_subtitle,
                description: settings.card_description,
                show_button: settings.show_button,
                button_text: settings.button_text,
                button_url: settings.button_link ? settings.button_link.url : '',
                image_id: settings.card_image ? settings.card_image.id : ''
            },
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    // Update card_id di widget
                    view.container.settings.set('card_id', response.data.card_id);
                    
                    // Refresh widget
                    view.container.render();
                    
                    elementor.notifications.showToast({
                        message: 'Card saved successfully!',
                        type: 'success'
                    });
                } else {
                    elementor.notifications.showToast({
                        message: 'Failed to save card',
                        type: 'error'
                    });
                }
            }
        });
    });

    // Handle change:select_card event
    elementor.channels.editor.on('change:select_card', function(view) {
        var settings = view.container.settings.attributes;
        var cardId = settings.select_card;
        
        if (cardId) {
            // Get card data from server
            $.get(cardAdmin.ajax_url, {
                action: 'get_card_data',
                card_id: cardId,
                nonce: cardAdmin.nonce
            }, function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Update widget settings with ACF data
                    view.container.settings.set({
                        card_title: data.title,
                        card_subtitle: data.subtitle,
                        card_description: data.description,
                        card_image: data.card_image || {}
                    });
                    
                    // Refresh widget
                    view.container.render();
                }
            });
        }
    });
}); 