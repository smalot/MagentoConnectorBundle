define(
    ['jquery', 'backbone', 'underscore', 'oro/translator'],
    function ($, Backbone, _, __) {
        'use strict';
        var MappingItem = Backbone.Model.extend({
            defaults: {
                source: null,
                target: null
            }
        });

        var MappingCollection = Backbone.Collection.extend({
            model: MappingItem
        });

        var MappingItemView = Backbone.View.extend({
            tagName: 'tr',
            template: _.template(
                '<td>' +
                    '<input type="hidden" class="mapping-source" value="<%= mappingItem.source %>"/>' +
                '</td>' +
                '<td>' +
                    '<i class="icon-arrow-right"></i>' +
                '</td>' +
                '<td>' +
                    '<input type="hidden" class="mapping-target" value="<%= mappingItem.target %>"/>' +
                '</td>' +
                '<td>' +
                    '<a href="javascript:void(0);" class="btn remove-btn"><i class="icon-remove-sign"></i></a>' +
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
                this.$el.html(this.template({mappingItem: this.model.toJSON()}));

                this.$el.find('.mapping-source').select2({tags: this.sources, maximumSelectionSize: 1});
                this.$el.find('.mapping-target').select2({tags: this.targets, maximumSelectionSize: 1});

                return this;
            },
            updateSource: function(e) {
                this.model.set('source', e.currentTarget.value);
            },
            updateTarget: function(e) {
                this.model.set('target', e.currentTarget.value);
            },
            removeMappingItem: function(e) {
                console.log('cocou');
                this.model.destroy();
            }
        });

        var MappingView = Backbone.View.extend({
            tagName: 'table',
            className: 'table table-bordered mapping-table',
            template: _.template(
                '<thead><tr><td><%= __("pim_magento_connector.mapping.attribute.source") %></td><td></td><td><%= __("pim_magento_connector.mapping.attribute.target") %></td><td></td></tr></thead>' +
                '<tbody>' +
                '</tbody>' +
                '<tfoot><tr><td colspan="4"><a href="javascript:void(0);" class="btn add-btn"><i class="icon-plus"></i> <%= __("pim_magento_connector.mapping.attribute.add") %></a></td></tr></tfoot>'
            ),
            events: {
                'click a.add-btn': function() {
                    this.addMappingItem({});
                },
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

                _.each(this.collection.models, function(mappingItem) {
                    this.addMappingItem({mappingItem: mappingItem});
                }, this);

                if (!this.$target.data('rendered')) {
                    this.$target.after(this.$el)
                    this.$target.hide();
                }

                this.$target.data('rendered', true);

                return this;
            },
            save: function() {
                var values = {};
                _.each(this.collection.toJSON(), function(value) {
                    values[value.source] = value.target;
                });

                this.$target.html(JSON.stringify(values));

                if (this.collection.length === 0) {
                    this.addMappingItem();
                }
            },
            createMappingItem: function() {
                var mappingItem = new MappingItem({source: '', target: ''});
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
                if (!options.mappingItem) {
                    var mappingItem = this.createMappingItem();
                } else {
                    var mappingItem = options.mappingItem;
                }

                var mappingItemView = this.createMappingItemView(mappingItem);

                mappingItemView.render();

                this.$el.children('tbody').append(mappingItemView.$el);
            }
        });

        return function($element) {
            console.log($element.data('rendered'));
            if ($element.data('rendered') == true) {
                return;
            }

            if ($element.find('.mapping-field').length > 0) {
                $element = $element.find('.mapping-field');
            }

            var fieldValues = JSON.parse($element.html());

            var mappingCollection = [];
            for (var field in fieldValues) {
                if (fieldValues[field] != '') {
                    mappingCollection.push({source: field, target: fieldValues[field]});
                }
            }

            new MappingView({
                collection: new MappingCollection(mappingCollection),
                $target: $element,
                sources: $element.data('sources'),
                targets: $element.data('targets')
            });
        };
    }
);
