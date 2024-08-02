define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return function (config) {
        var previousCart = {};

        function initJustuno() {
            if (window.ju4app.initialized) return;
            window.ju4app.initialized = true;
            window.ju4app('initFunc', function() {
                console.log('Justuno initialized');
                
                if (config.orderData) {
                    window.ju4app('order', config.orderData);
                }
                
                if (config.customJs) {
                    eval(config.customJs);
                }
            });
        }

        function waitForJu4app() {
            if (typeof window.ju4app === 'function') {
                initJustuno();
            } else {
                setTimeout(waitForJu4app, 500);
            }
        }

        waitForJu4app();

        function sendUpdatedItemData(item, qty) {
            if (window.ju4app) {
                window.ju4app('cartSync', {
                    items: [{
                        productID: item.product_id,
                        variationID: item.item_id,
                        sku: item.sku,
                        price: item.product_price_value * 100,
                        qty: qty,
                        name: item.name,
                        discount: (item.discount_amount || 0) * 100
                    }],
                    cart: {
                        currency: item.currency_code,
                        cartID: customerData.get('cart')().id
                    }
                });
            }
        }

        function detectCartChanges(newCart) {
            if (!newCart.items) return;

            newCart.items.forEach(function(item) {
                var prevItem = previousCart.items ? previousCart.items.find(i => i.item_id === item.item_id) : null;
                if (!prevItem || prevItem.qty !== item.qty) {
                    sendUpdatedItemData(item, item.qty);
                }
            });

            if (previousCart.items) {
                previousCart.items.forEach(function(prevItem) {
                    if (!newCart.items.some(i => i.item_id === prevItem.item_id)) {
                        sendUpdatedItemData(prevItem, 0);
                    }
                });
            }

            previousCart = JSON.parse(JSON.stringify(newCart));
        }

        customerData.get('cart').subscribe(function (updatedCart) {
            detectCartChanges(updatedCart);
        });

        var initialCart = customerData.get('cart')();
        if (initialCart && initialCart.items) {
            initialCart.items.forEach(function(item) {
                sendUpdatedItemData(item, item.qty);
            });
            previousCart = JSON.parse(JSON.stringify(initialCart));
        }
    };
});