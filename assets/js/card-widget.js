(function($) {
    'use strict';

    var CardWidget = elementorModules.frontend.handlers.Base.extend({
        getDefaultSettings: function() {
            return {
                selectors: {
                    cardSelect: '.elementor-control-select_card select',
                    cardWrapper: '.custom-card',
                    buttonWrapper: '.elementor-button-wrapper',
                    card: '.elementor-card'
                }
            };
        },

        getDefaultElements: function() {
            var selectors = this.getSettings('selectors');
            return {
                $cardSelect: this.$element.find(selectors.cardSelect),
                $cardWrapper: this.$element.find(selectors.cardWrapper),
                $buttonWrapper: this.$element.find(selectors.buttonWrapper),
                $card: this.$element.find(selectors.card)
            };
        },

        bindEvents: function() {
            var self = this;
            
            // Handle card selection change
            this.elements.$cardSelect.on('change', this.onCardSelectChange.bind(this));
            
            // Handle direction and alignment changes in editor
            if (window.elementor) {
                elementor.channels.editor.on('change', function(controlView) {
                    if (controlView.model.get('element') === self.$element.data('id')) {
                        var changedProperty = controlView.model.get('name');
                        
                        if (changedProperty === 'button_direction') {
                            var direction = controlView.container.settings.get('button_direction');
                            self.updateButtonDirection(direction);
                        }
                        
                        if (changedProperty === 'button_alignment') {
                            var alignment = controlView.container.settings.get('button_alignment');
                            self.updateButtonAlignment(alignment);
                        }

                        // Handle hover effect changes
                        if (changedProperty === 'enable_hover_effects' || 
                            changedProperty === 'hover_animation_type' ||
                            changedProperty === 'hover_animation_intensity') {
                            self.updateHoverEffects();
                        }
                    }
                });
            }
        },

        updateButtonDirection: function(direction) {
            console.log('Updating direction to:', direction);
            
            var $buttonWrapper = this.$element.find(this.getSettings('selectors').buttonWrapper);
            if ($buttonWrapper.length) {
                $buttonWrapper.removeClass('horizontal vertical').addClass(direction);
                
                // Re-apply current alignment after direction change
                var alignment = this.getElementSettings('button_alignment');
                if (alignment) {
                    this.updateButtonAlignment(alignment);
                }
            }
        },

        updateButtonAlignment: function(alignment) {
            console.log('Updating alignment to:', alignment);
            
            var $buttonWrapper = this.$element.find(this.getSettings('selectors').buttonWrapper);
            if ($buttonWrapper.length) {
                // Remove all possible alignment classes
                $buttonWrapper.removeClass('start center end space-between stretch');
                // Add new alignment class
                $buttonWrapper.addClass(alignment);
            }
        },

        updateHoverEffects: function() {
            var settings = this.getElementSettings(),
                $card = this.elements.$card;

            // Remove existing hover effects
            $card.removeAttr('data-hover');
            $card.css('--hover-intensity', '');

            // Apply new hover effects if enabled
            if (settings.enable_hover_effects === 'yes' && settings.hover_animation_type !== 'none') {
                $card.attr('data-hover', settings.hover_animation_type);
                if (settings.hover_animation_intensity) {
                    $card.css('--hover-intensity', settings.hover_animation_intensity.size);
                }
            }
        },

        onInit: function() {
            elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);
            
            // Set initial direction and alignment
            var settings = this.getElementSettings();
            if (settings.button_direction) {
                this.updateButtonDirection(settings.button_direction);
            }
            if (settings.button_alignment) {
                this.updateButtonAlignment(settings.button_alignment);
            }
            // Initialize hover effects
            this.updateHoverEffects();
        },

        onElementChange: function(propertyName) {
            if (propertyName === 'button_direction') {
                var direction = this.getElementSettings('button_direction');
                this.updateButtonDirection(direction);
            }
            if (propertyName === 'button_alignment') {
                var alignment = this.getElementSettings('button_alignment');
                this.updateButtonAlignment(alignment);
            }
        },

        onCardSelectChange: function(event) {
            var selectedCard = $(event.currentTarget).val();
            this.elements.$cardWrapper.addClass('card-loading');
            
            elementorFrontend.config.ajaxurl = elementorProFrontend.config.ajaxurl;
            
            elementor.ajax.addRequest('update_card_widget', {
                unique_id: this.getUniqueID(),
                data: {
                    selected_card: selectedCard
                },
                success: function(data) {
                    this.elements.$cardWrapper.html(data.html);
                    
                    // Re-apply current direction and alignment after content update
                    var settings = this.getElementSettings();
                    if (settings.button_direction) {
                        this.updateButtonDirection(settings.button_direction);
                    }
                    if (settings.button_alignment) {
                        this.updateButtonAlignment(settings.button_alignment);
                    }
                }.bind(this),
                complete: function() {
                    this.elements.$cardWrapper.removeClass('card-loading');
                }.bind(this)
            });
        }
    });

    jQuery(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/custom_card.default', function($scope) {
            var $card = $scope.find('.custom-card');
            var cardId = $card.data('card-id');
            
            if (cardId) {
                jQuery.ajax({
                    url: cardWidgetData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'get_card_data',
                        nonce: cardWidgetData.nonce,
                        card_id: cardId
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            $card.html(response.data);
                        }
                    }
                });
            }
        });
    });

})(jQuery); 