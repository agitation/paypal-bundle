ag.ns("ag.paypal");

(function(){

// form

var
    paypalForm = function()
    {
        this.extend(this, ag.ui.tool.tpl("agit-payment-paypal-checkout", "tbody"));
        this.name = "PayPal";
    };

    paypalForm.prototype = Object.create(ag.order.CheckoutPaymentForm.prototype);

// payment module

var
    paypalPaymentModule = function(options) {
        ag.order.PaymentModule.call(this, options);
    };

    paypalPaymentModule.prototype = Object.create(ag.order.PaymentModule.prototype);

    paypalPaymentModule.prototype.getForm = function()
    {
        return new paypalForm();
    };

    ag.paypal.PaymentModule = paypalPaymentModule;

    ag.srv("plugins").register("ag.order.checkout.payment", "PP", paypalPaymentModule);
})();
