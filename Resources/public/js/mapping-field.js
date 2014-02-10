define(
    ['jquery', 'backbone', 'underscore', 'oro/translator', 'oro/form-validation'],
    function ($, Backbone, _, __, FormValidation) {
        'use strict';
        var MappingItem = Backbone.Model.extend({
            defaults: {
                source    : null,
                target    : null,
                deletable : true
            }
        });

        var MappingCollection = Backbone.Collection.extend({
            model: MappingItem
        });

        var MappingItemView = Backbone.View.extend({
            tagName: 'tr',
            template: _.template(
                '<td>' +
                    '<input type="text" class="mapping-source" required="required" value="<%= mappingItem.source %>"/>' +
                '</td>' +
                '<td>' +
                    '<i class="icon-arrow-right"></i>' +
                '</td>' +
                '<td>' +
                    '<input type="text" class="mapping-target" required="required" required="required" value="<%= mappingItem.target %>"/>' +
                '</td>' +
                '<td>' +
                    '<a href="javascript:void(0);" class="btn remove-btn <% if (!mappingItem.deletable) { %>disabled<% } %>">' +
                        '<i class="icon-remove-sign"></i>' +
                    '</a>' +
                '</td>'
            ),
            events: {
                'change input.mapping-source': 'updateSource',
                'change input.mapping-target': 'updateTarget',
                'click a.remove-btn':          'removeMappingItem'
            },
            sources: [],
            targets: [],
            initialize: function(options) {
                this.sources = options.sources;
                this.targets = options.targets;

                this.listenTo(this.model, "destroy", this.remove);

                this.render();
            },
            render: function() {
                this.$el.html(this.template({mappingItem: this.model.toJSON(), __: __}));


                return this;
            },
            updateSource: function(e) {
                this.model.set('source', e.currentTarget.value);
            },
            updateTarget: function(e) {
                this.model.set('target', e.currentTarget.value);
            },
            removeMappingItem: function(e) {
                if (this.model.attributes.deletable) {
                    this.model.destroy();
                }
            }
        });

        var MappingView = Backbone.View.extend({
            tagName: 'table',
            className: 'table table-bordered mapping-table',
            template: _.template(
                '<thead>' +
                    '<tr>' +
                        '<td><%= __("pim_magento_connector.mapping.attribute.source") %></td>' +
                        '<td></td>' +
                        '<td><%= __("pim_magento_connector.mapping.attribute.target") %></td>' +
                        '<td></td>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' +
                '</tbody>' +
                '<tfoot>' +
                    '<tr>' +
                        '<td colspan="4">' +
                            '<a href="javascript:void(0);" class="btn add-btn">' +
                                '<i class="icon-plus"></i><%= __("pim_magento_connector.mapping.attribute.add") %>' +
                            '</a>' +
                        '</td>' +
                    '</tr>' +
                '</tfoot>'
            ),
            events: {
                'click a.add-btn': 'addMappingItem',
            },
            $target: null,
            sources: [],
            targets: [],
            mappingItemViews: [],
            initialize: function(options) {
                this.$target = options.$target;
                this.sources = options.sources;
                this.targets = options.targets;

                this.listenTo(this.collection, "change add remove", this.save);
                this.render();
            },
            render: function() {
                this.$el.empty();
                this.$el.html(this.template({__: __}));

                if (!this.$target.data('rendered')) {
                    this.$target.after(this.$el)
                    this.$target.hide();
                }

                _.each(this.collection.models, function(mappingItem) {
                    this.addMappingItem({mappingItem: mappingItem});
                }, this);

                this.$target.data('rendered', true);

                return this;
            },
            save: function() {
                var values = {};
                _.each(this.collection.toJSON(), function(value) {
                    values[value.source] = {source:value.source, target: value.target};
                });

                this.$target.html(JSON.stringify(values));

                if (this.collection.length === 0) {
                    this.addMappingItem();
                }
            },
            createMappingItem: function() {
                var mappingItem = new MappingItem({source: '', target: '', deletable: true});
                this.collection.add(mappingItem);

                return mappingItem;
            },
            createMappingItemView: function(mappingItem) {
                var mappingItemView = new MappingItemView({
                    model: mappingItem,
                    sources: this.sources,
                    targets: this.targets
                });

                this.mappingItemViews.push(mappingItemView);

                return mappingItemView;
            },
            addMappingItem: function(options) {
                var options = options || {};

                if (!options.mappingItem) {
                    var mappingItem = this.createMappingItem();
                } else {
                    var mappingItem = options.mappingItem;
                }

                var mappingItemView = this.createMappingItemView(mappingItem);

                this.$el.children('tbody').append(mappingItemView.$el);

                mappingItemView.$el.find('.mapping-source').select2({tags: this.sources, maximumSelectionSize: 1});
                mappingItemView.$el.find('.mapping-target').select2({tags: this.targets, maximumSelectionSize: 1})
                    .enable(mappingItemView.model.get('deletable'));
            }
        });

        return function($element) {
            if ($element.data('rendered') == true) {
                return;
            }

            if ($element.find('.mapping-field').length > 0) {
                $element = $element.find('.mapping-field');
            }

            var fieldValues = JSON.parse($element.html());

            var mappingCollection = [];
            for (var field in fieldValues) {
                if (fieldValues[field]['target'] != '') {
                    mappingCollection.push(fieldValues[field]);
                }
            }

            new MappingView({
                collection: new MappingCollection(mappingCollection),
                $target: $element,
                sources: $element.data('sources'),
                targets: $element.data('targets')
            });

            $element.parents('form').on('submit', function() {
                var isValid = true;

                $('input.mapping-source, input.mapping-target').each(function() {
                    $(this).parent().children('.validation-faled').remove();

                    if ($(this).val() == '') {
                        FormValidation.addFieldErrors($(this), __('pim_magento_connector.mapping.attribute.not_blank'));
                        isValid = false;
                    }
                });

                return isValid;
            });
        };
    }
);
