define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Prostor_CumDiscount/summary/prostor-cumulative'
        },

        getPureValue: function () {
            var totals = quote.getTotals()();
            if (!totals || !totals.total_segments) {
                return 0;
            }

            var segment = totals.total_segments.find(function (s) {
                return s.code === 'prostor_cumulative';
            });

            return segment ? segment.value : 0;
        },

        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        isDisplayed: function () {
            return this.getPureValue() !== 0;
        }
    });
});