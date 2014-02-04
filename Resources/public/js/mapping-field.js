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
                    '<input type="text" class="mapping-source" value="<%= mappingItem.source %>"/>' +
                '</td>' +
                '<td>' +
                    '<i class="icon-arrow-right"></i>' +
                '</td>' +
                '<td>' +
                    '<input type="text" class="mapping-target" value="<%= mappingItem.target %>"/>' +
                '</td>' +
                '<td>' +
                    '<a href="javascript:void(0);" class="btn remove-btn"><i class="icon-remove-sign"></i></a>' +
                '</td>'
            ),
            events: {
                'input input.mapping-source': 'updateSource',
                'input input.mapping-target': 'updateTarget',
                'click a.remove-btn':         'removeMappingItem'
            },
            initialize: function(options) {
                Backbone.View.prototype.initialize.apply(this, arguments);

                this.listenTo(this.model, "destroy", this.remove);
            },
            render: function() {
                this.$el.html(this.template({mappingItem: this.model.toJSON()}));

                return this;
            },
            updateSource: function(e) {
                this.model.set('source', e.currentTarget.value);
            },
            updateTarget: function(e) {
                this.model.set('target', e.currentTarget.value);
            },
            removeMappingItem: function(e) {
                this.model.destroy();
            }
        });

        var MappingView = Backbone.View.extend({
            tagName: 'table',
            className: 'table table-bordered mapping-table',
            template: _.template(
                '<thead><tr><td><%= __("pim_magento_connector.mapping.attribute.source") %></td><td></td><td><%= __("pim_magento_connector.mapping.attribute.target") %></td><td></td></tr></thead>' +
                '<tbody></tbody>' +
                '<tfoot><tr><td colspan="4"><a href="javascript:void(0);" class="btn add-btn"><i class="icon-plus"></i> <%= __("pim_magento_connector.mapping.attribute.add") %></a></td></tr></tfoot>'
            ),
            events: {
                'click a.add-btn': 'addMappingItem'
            },
            $target: null,
            mappingItemViews: [],
            initialize: function(options) {
                this.$target = options.$target;

                _.each(this.collection.models, function(mappingItem) {
                    this.mappingItemViews.push(new MappingItemView({model: mappingItem}))
                }, this);

                Backbone.View.prototype.initialize.apply(this, arguments);
                this.listenTo(this.collection, "change add remove", this.save);
                this.render();
            },
            render: function() {
                this.$el.html(this.template({__: __}));
                this.$el.children('tbody').empty();

                _.each(this.mappingItemViews, function(mappingItemView) {
                    this.$el.children('tbody').append(mappingItemView.render().$el);
                }, this);

                this.$el.insertAfter(this.$target);
            },
            save: function() {
                var values = {};
                _.each(this.collection.toJSON(), function(value) {
                    values[value.source] = value.target;
                });

                this.$target.val(JSON.stringify(values));
            },
            addMappingItem: function() {
                var newMappingItem = new MappingItem({source: '', target:''});
                this.collection.add(newMappingItem);

                var mappingItemView = new MappingItemView({model: newMappingItem});

                this.mappingItemViews.push(mappingItemView);

                this.$el.children('tbody').append(mappingItemView.render().$el);
            }
        });

        return function($element) {
            var fieldValues = JSON.parse($element.val());

            fieldValues = _.map(fieldValues, function(target, source) {
                if (target != '') {
                    return {source: source, target: target};
                }
            });

            new MappingView({collection: new MappingCollection(fieldValues), $target: $element});
        };
    }
);
