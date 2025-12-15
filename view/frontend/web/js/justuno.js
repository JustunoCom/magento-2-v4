define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return function (config) {
        var currencyCode = config.currencyCode;
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
                        sku: item.product_sku,
                        price: item.product_price_value * 100,
                        qty: qty,
                        name: item.product_name,
                        currency: currencyCode,
                        discount: (item.discount_amount || 0) * 100
                    }],
                    cart: {
                        currency: currencyCode,
                        cartID: customerData.get('cart')().id
                    }
                });
            }
        }

        function sendCartData(cartItems) {
            if (!window.ju4app || !cartItems || !cartItems.length) return;

            const cartData = {
			    items: cartItems.map(item => {
			        // Extract the actual price including tax from the formatted string
			        const priceInclTax = parseFloat(
			            item.product_price.match(/\$[\d,]+\.?\d*/)[0].replace(/[$,]/g, '')
			        );
			        
			        // Calculate discount as difference between original price and current price (incl tax)
			        const discountAmount = item.product_original_price_value - priceInclTax;
			        
			        return {
			            productID: item.product_id,
			            variationID: item.item_id,
			            sku: item.product_sku,
			            price: Math.round(priceInclTax * 100), // Price in cents (incl tax)
			            qty: item.qty,
			            name: item.product_name,
			            currency: currencyCode,
			            discount: Math.round(discountAmount * 100) // Discount in cents
			        };
			    }),
			    cart: {
			        currency: currencyCode,
			        cartID: customerData.get('cart')().id,
			        subTotal: Math.round(parseFloat(
			            customerData.get('cart')().subtotal_incl_tax
			                .match(/\$[\d,]+\.?\d*/)[0]
			                .replace(/[$,]/g, '')
			        ) * 100), // Subtotal in cents (incl tax)
			        totalPrice: Math.round(parseFloat(
			            customerData.get('cart')().subtotal_incl_tax
			                .match(/\$[\d,]+\.?\d*/)[0]
			                .replace(/[$,]/g, '')
			        ) * 100) // Total in cents (incl tax) - same as subtotal for now
			    }
			};

            window.ju4app('cartSync', cartData);
        }

        function detectCartChanges(newCart) {
            if (!newCart.items) return;
            sendCartData(newCart.items);
        }

        customerData.get('cart').subscribe(function (updatedCart) {
            detectCartChanges(updatedCart);
        });

        var initialCart = customerData.get('cart')();
        if (initialCart && initialCart.items) {
            sendCartData(initialCart.items);
        }
    };
});
